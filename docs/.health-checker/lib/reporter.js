/**
 * @author    Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license   https://magebit.com/code-license
 */
'use strict';

const { isError } = require('./finding');

const INDENT = 32;
const RULE = '-'.repeat(72);
const RUN_COMMAND = 'docs/.health-checker/run';

const shellQuote = (value) => `'${value.replace(/'/g, "'\\''")}'`;
const sortedUnique = (items) => [...new Set(items)].sort();

function messageFor(finding) {
  if (finding.code === 'review.stale') {
    return [
      finding.message,
      'Fix: check the doc against the change again: edit it, or confirm it with the review lines below.',
      'Use the write-project-docs skill, or follow docs/MAINTAINING.md.',
    ].join('\n');
  }

  if (finding.code !== 'doc.untouched') return finding.message;

  return [
    finding.message,
    'Fix: update the doc so it holds after this change, or confirm it still holds with the review',
    'lines below. Use the write-project-docs skill, or follow docs/MAINTAINING.md.',
    'Uncommitted changes count too: stash the ones this commit leaves out.',
  ].join('\n');
}

function reviewTrailers(docs) {
  return sortedUnique(docs)
    .map((doc) => `--trailer ${shellQuote(`Docs-Reviewed: ${doc}`)}`)
    .join(' ');
}

const MAX_SUBJECT = 72;

/**
 * One empty commit recording the reviews, which the hook and the PR gate read. It skips the hooks: a hook run
 * through `docker compose exec` cannot see the temporary index `--only` commits from, so it would judge the
 * staged code instead.
 */
function reviewCommand(docs, ticketKey) {
  const list = sortedUnique(docs);
  // Commit-msg hooks that require a scope accept a lowercase area name when there is no ticket.
  const type = `docs(${ticketKey === null ? 'project-docs' : ticketKey})`;
  // A subject naming the docs reads in the log; a generic one gets copied unread.
  const names = list.map((doc) => doc.split('/').pop());
  const listed = names.slice(0, 3).join(', ');
  let subject =
    names.length > 3
      ? `Confirm ${listed} and ${names.length - 3} more docs still match the code`
      : `Confirm ${listed} ${names.length === 1 ? 'still matches' : 'still match'} the code`;
  // Long doc names would break a subject length limit; the trailers still name every doc.
  if (`${type}: ${subject}`.length > MAX_SUBJECT) {
    subject = names.length > 1 ? `Confirm ${names.length} docs still match the code` : 'Confirm the doc still matches the code';
  }

  let command = `git commit --allow-empty --only --no-verify -m ${shellQuote(`${type}: ${subject}`)}`;
  command += ` -m ${shellQuote('Keep the Docs-Reviewed lines when you squash this branch.')}`;
  for (const doc of list) command += ` --trailer ${shellQuote(`Docs-Reviewed: ${doc}`)}`;
  return command;
}

// Errors first within each file, so the actionable lines lead.
function groupByFile(findings) {
  const grouped = new Map();
  for (const finding of findings) {
    if (!grouped.has(finding.file)) grouped.set(finding.file, []);
    grouped.get(finding.file).push(finding);
  }

  return [...grouped.keys()]
    .sort((a, b) => (a < b ? -1 : a > b ? 1 : 0))
    .map((file) => [file, grouped.get(file).sort((a, b) => Number(isError(b)) - Number(isError(a)))]);
}

// Returns an empty string when there is nothing to say.
function render(findings, { ticketKey = null } = {}) {
  if (findings.length === 0) return '';

  const errors = findings.filter(isError);
  const warnings = findings.filter((finding) => !isError(finding));

  const lines = ['', 'Documentation health', RULE];
  for (const [file, fileFindings] of groupByFile(findings)) {
    lines.push(file);
    for (const finding of fileFindings) {
      const [first, ...rest] = messageFor(finding).split('\n');
      lines.push(`  ${(isError(finding) ? 'ERROR' : 'warn').padEnd(8)} ${finding.code.padEnd(20)} ${first}`);
      for (const line of rest) lines.push(' '.repeat(INDENT) + line);
    }
  }

  lines.push(RULE, `${errors.length} error(s), ${warnings.length} warning(s)`);
  if (errors.length > 0) lines.push(`Errors block the commit. Fix them, then run: ${RUN_COMMAND}`);
  if (warnings.length > 0) lines.push('Warnings need judgement. They never block.');

  const untouched = findings.filter((finding) => ['doc.untouched', 'review.stale'].includes(finding.code)).map((finding) => finding.file);
  if (untouched.length > 0) {
    lines.push(
      '',
      'Add these review lines only once the doc has been checked against the change and',
      'still holds. The commit-msg hook reads them from the commit message, and the',
      'PR gate from the branch commits.',
      `  Add to the commit you are making: ${reviewTrailers(untouched)}`,
      'Code already committed? Commit the review after it. --only leaves staged code',
      'out; the commit is empty, so --no-verify only skips hooks with nothing to check:',
      `  ${reviewCommand(untouched, ticketKey)}`
    );
  }

  lines.push('');
  return lines.join('\n');
}

module.exports = { render };
