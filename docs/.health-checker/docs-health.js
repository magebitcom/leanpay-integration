#!/usr/bin/env node
/**
 * Documentation health check, the one entry point every caller uses (hook, run shim, PR gate).
 *
 *   node docs/.health-checker/docs-health.js                       whole branch against the base
 *   node docs/.health-checker/docs-health.js <message-file>        commit-msg hook
 *   node docs/.health-checker/docs-health.js --base=<ref>          PR gate, or another base
 *   node docs/.health-checker/docs-health.js --repo=<path>         a repository other than the one holding this folder
 *
 * The change set is the branch: everything since it left the base, committed or not, plus untracked files.
 * The base is, in order: --base, `base` in docs/.health-checker/health-checker.yaml, then origin/HEAD. A `base` list
 * names the long-lived branches, and the closest one is the base.
 * During a merge only the snapshot checks run. Exit codes: 0 no errors, 1 an error, 2 could not run.
 *
 * @author    Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license   https://magebit.com/code-license
 */
'use strict';

const fs = require('fs');
const path = require('path');
const { loadConfig, CONFIG_PATH } = require('./lib/config');
const { loadDocuments } = require('./lib/document');
const { createGit } = require('./lib/git');
const { check } = require('./lib/checker');
const { render } = require('./lib/reporter');
const { isError } = require('./lib/finding');

const USAGE = 'The options are --base=<ref> and --repo=<path>.';

function parseArguments(argv) {
  const parsed = { base: null, repo: null, messageFile: null };
  for (const argument of argv) {
    if (argument.startsWith('--base=')) {
      parsed.base = argument.slice('--base='.length);
      if (parsed.base === '') throw new Error('--base needs a ref, e.g. --base=origin/master.');
    } else if (argument.startsWith('--repo=')) {
      parsed.repo = argument.slice('--repo='.length);
    } else if (argument.startsWith('-')) {
      throw new Error(`Unknown argument: ${argument}. ${USAGE}`);
    } else if (parsed.messageFile !== null) {
      throw new Error('Only one commit message file: the path git passes to the commit-msg hook.');
    } else {
      parsed.messageFile = argument;
    }
  }
  return parsed;
}

function isFile(file) {
  try {
    return fs.statSync(file).isFile();
  } catch (error) {
    return false;
  }
}

// The commit-msg hook passes the message being committed, so a review line in it counts as this commit's.
function readMessage(messageFile, repoRoot, git) {
  if (messageFile === null) return null;

  let file = path.resolve(repoRoot, messageFile);
  // In a linked worktree .git is a file; git keeps the message in the worktree's own git dir.
  if (!fs.existsSync(file) && path.basename(messageFile) === 'COMMIT_EDITMSG') file = git.commitMessagePath();
  if (file === '' || !isFile(file)) {
    throw new Error(`No commit message file at ${messageFile}. Pass the path the commit-msg hook gets.`);
  }
  return fs.readFileSync(file, 'utf8');
}

// The review commit the doc.untouched fix prints is scoped to the branch's Jira key: feature/MB-1234-x -> MB-1234.
function ticketKeyOf(git) {
  let branch = git.currentBranch();
  // The PR gate checks out a detached HEAD; GitHub names the PR's branch in GITHUB_HEAD_REF.
  if (branch === 'HEAD' && (process.env.GITHUB_HEAD_REF || '') !== '') branch = process.env.GITHUB_HEAD_REF;
  const match = /(?:^|\/)([A-Z][A-Z0-9]+-[0-9]+)/.exec(branch);
  return match ? match[1] : null;
}

function localDate() {
  const now = new Date();
  const pad = (value) => String(value).padStart(2, '0');
  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
}

// A branch cut from staging and holding a merged or cherry-picked feature is judged by its own changes, not by
// everything staging holds that the default branch does not. The closest listed branch leaves the fewest commits on
// this one; a tie goes to the first listed. Refs not fetched yet are skipped.
function chooseBase(args, config, git) {
  if (args.base !== null) return { base: args.base, among: [] };
  if (config.bases === null) {
    // origin/HEAD is recorded at clone time, and git fetch never updates it.
    const head = git.originHead();
    if (head === null) {
      throw new Error(
        'No base branch: origin/HEAD is not set. Point it at the branch feature branches start from ' +
          `(git remote set-head origin <branch>), or set \`base\` in ${CONFIG_PATH}.`
      );
    }
    return { base: head, among: [] };
  }
  if (config.bases.length === 1) return { base: config.bases[0], among: [] };

  const found = config.bases.filter((ref) => git.refExists(ref));
  if (found.length === 0) {
    throw new Error(`None of the bases in ${CONFIG_PATH} is found (${config.bases.join(', ')}). Run: git fetch`);
  }
  let base = found[0];
  let fewest = git.commitsAhead(base);
  for (const ref of found.slice(1)) {
    const ahead = git.commitsAhead(ref);
    if (ahead < fewest) {
      base = ref;
      fewest = ahead;
    }
  }
  return { base, among: found };
}

function main(argv) {
  const args = parseArguments(argv);
  const repoRoot = path.resolve(args.repo !== null ? args.repo : path.join(__dirname, '..', '..'));
  const git = createGit(repoRoot);

  const config = loadConfig(repoRoot);
  process.stdout.write(`docs-health ${config.version}\n`);

  const message = readMessage(args.messageFile, repoRoot, git);

  const { base, among } = chooseBase(args, config, git);
  if (!git.refExists(base)) throw new Error(`Base ref ${base} not found. Run: git fetch`);
  process.stdout.write(`Base: ${base}${among.length > 1 ? `, the closest of ${among.join(', ')}` : ''}\n`);

  const trackedPaths = git.trackedPaths();
  const documents = loadDocuments(repoRoot, config, new Set(trackedPaths));
  const findings = check({
    repoRoot,
    config,
    documents,
    git,
    message,
    base,
    today: localDate(),
    knownPaths: trackedPaths,
  });

  const report = render(findings, { ticketKey: ticketKeyOf(git) });
  if (report === '') {
    process.stdout.write('Documentation health: no problems found.\n');
    return 0;
  }

  process.stdout.write(report);
  return findings.some(isError) ? 1 : 0;
}

try {
  process.exitCode = main(process.argv.slice(2));
} catch (error) {
  process.stderr.write(`docs-health could not run: ${error.message}\n`);
  process.exitCode = 2;
}
