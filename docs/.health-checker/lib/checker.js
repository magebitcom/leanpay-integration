/**
 * @author    Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license   https://magebit.com/code-license
 */
'use strict';

const fs = require('fs');
const path = require('path');
const { error, warning } = require('./finding');
const { isIgnored, CONFIG_PATH, CHECKER_DIRECTORY } = require('./config');
const { globToRegExp } = require('./glob');
const { coveredPaths, pathCovers, get } = require('./document');

const VALID_STATUSES = ['draft', 'stable', 'deprecated'];

const REVIEW_LINE = /^\s*(?:[*-]\s+)?Docs-Reviewed:[ \t]*(.*)$/gim;

const unique = (items) => [...new Set(items)];
const byBytes = (a, b) => (a < b ? -1 : a > b ? 1 : 0);

function withParentFolders(files) {
  const known = new Set();
  for (const file of files) {
    // A folder already known has all its parents known too.
    for (let current = file; current !== '.' && current !== '' && !known.has(current); current = path.posix.dirname(current)) {
      known.add(current);
    }
  }
  return known;
}

function normalisePath(value) {
  const parts = [];
  for (const part of value.split('/')) {
    if (part === '' || part === '.') continue;
    if (part === '..') {
      if (parts.length === 0) return null;
      parts.pop();
    } else {
      parts.push(part);
    }
  }
  return parts.join('/');
}

function summarise(paths) {
  const list = unique(paths);
  if (list.length <= 3) return list.join(', ');
  return `${list.slice(0, 3).join(', ')} and ${list.length - 3} more`;
}

// Remove fenced blocks and inline code so documented examples are not mistaken for real links.
function stripCode(markdown) {
  return markdown.replace(/^```[\s\S]*?^```/gm, '').replace(/`[^`\n]*`/g, '');
}

// Dates are strings; a value that is not a real calendar date, such as month 13, is rejected.
function parseDate(value) {
  if (typeof value !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
  const [year, month, day] = value.split('-').map(Number);
  const date = new Date(Date.UTC(year, month - 1, day));
  const valid = date.getUTCFullYear() === year && date.getUTCMonth() === month - 1 && date.getUTCDate() === day;
  return valid ? value : null;
}

function reviewLines(message, commit, index) {
  const reviews = [];
  for (const match of message.matchAll(REVIEW_LINE)) {
    const paths = match[1]
      .split(',')
      // `./docs/x.md`, `/docs/x.md` and `docs/x.md.` all mean docs/x.md.
      .map((entry) => entry.trim().replace(/^(?:\.\/|\/)+/, '').replace(/[ \t.,;:]+$/, ''))
      .filter((entry) => entry !== '');
    if (paths.length === 0) reviews.push({ commit, index, path: null });
    for (const reviewed of paths) reviews.push({ commit, index, path: reviewed });
  }
  return reviews;
}

function check({ repoRoot, config, documents, git, message, base, today, knownPaths }) {
  const known = withParentFolders(knownPaths);

  const pathExists = (candidate) => {
    const trimmed = candidate.replace(/^\/+|\/+$/g, '');
    return trimmed !== '' && fs.existsSync(path.join(repoRoot, trimmed)) && known.has(trimmed);
  };

  let changedFiles = null;
  let reviews = [];
  let commits = [];
  let workingTreeFiles = [];

  // A merge brings changes judged on their own branch, so it gets only the snapshot checks.
  if (!git.isMerging()) {
    // From the merge base, so commits the base branch gained after the split are not counted.
    const mergeBase = git.mergeBase(base);
    if (mergeBase === null) {
      throw new Error(`No merge base with ${base}. A shallow clone needs full history (fetch-depth: 0).`);
    }

    changedFiles = unique([...git.changedSince(mergeBase), ...git.untrackedPaths()]);
    commits = git.commitsSince(mergeBase);
    // Uncommitted and untracked changes are newer than any commit.
    workingTreeFiles = unique([...git.changedSince('HEAD'), ...git.untrackedPaths()]);
    commits.forEach((commit, index) => reviews.push(...reviewLines(commit.message, commit.commit, index)));
    if (message !== null) reviews.push(...reviewLines(message, '', commits.length));
  }

  const checkFrontmatter = (document) => {
    if (document.frontmatter === null) {
      return [
        document.parseError === null
          ? error('frontmatter.missing', document.path, 'No OKF frontmatter block. Add one delimited by --- lines, with at least a `type`.')
          : error('frontmatter.unparseable', document.path, `Frontmatter does not parse as YAML: ${document.parseError}`),
      ];
    }
    const type = get(document, 'type');
    if (typeof type !== 'string' || type.trim() === '') {
      return [error('type.missing', document.path, 'Frontmatter is missing a non-empty `type` (the one field OKF requires).')];
    }
    return [];
  };

  const checkLifecycle = (document) => {
    const findings = [];
    // Absent status means stable, per OKF.
    const status = get(document, 'status');
    if (status !== null) {
      if (typeof status !== 'string' || !VALID_STATUSES.includes(status)) {
        findings.push(error('status.invalid', document.path, `\`status\` must be one of: ${VALID_STATUSES.join(', ')}.`));
      } else if (status === 'draft') {
        findings.push(warning('doc.draft', document.path, 'Marked `status: draft` - still incomplete.'));
      } else if (status === 'deprecated') {
        findings.push(warning('doc.deprecated', document.path, 'Marked `status: deprecated` - delete it once nothing links here.'));
      }
    }

    const staleAfter = get(document, 'stale_after');
    if (staleAfter !== null) {
      const date = parseDate(staleAfter);
      if (date === null) {
        findings.push(error('date.invalid', document.path, '`stale_after` must be a real date, e.g. stale_after: "2027-03-01".'));
      } else if (today >= date) {
        findings.push(warning('doc.stale', document.path, `Stale since ${date}. Check it against what it describes, then move \`stale_after\`: use the write-project-docs skill, or follow docs/MAINTAINING.md.`));
      }
    }
    return findings;
  };

  // A document claiming a path that no longer exists is describing something gone.
  const checkCoveredPathsExist = (document) =>
    coveredPaths(document)
      .filter((covered) => !pathExists(covered))
      .map((covered) =>
        error(
          'covers.missing',
          document.path,
          `Declares \`${covered}\`, which is not in the repository. Was it renamed, removed or gitignored?`
        )
      );

  const checkLinks = (document) => {
    const body = stripCode(document.body);
    const directory = path.posix.dirname(document.path);
    const findings = [];
    for (const link of unique([...body.matchAll(/\[[^\]]*\]\(([^)\s]+)\)/g)].map((match) => match[1]))) {
      if (/^(https?:|mailto:|tel:|#)/i.test(link)) continue;

      // Drop the anchor; the file must exist, the heading is not checked.
      const hash = link.indexOf('#');
      const target = hash === -1 ? link : link.slice(0, hash);
      if (target === '') continue;

      const resolved = normalisePath(`${directory}/${target}`);
      if (resolved === null || !pathExists(resolved)) {
        findings.push(error('link.broken', document.path, `Link target is not in the repository: ${link}`));
      }
    }
    return findings;
  };

  // Every path a `require` glob expands to must be claimed by some doc.
  const checkRequired = () => {
    const claimed = documents.flatMap(coveredPaths);
    const findings = [];
    const candidates = [];
    for (const pattern of config.require) {
      const regex = globToRegExp(pattern.replace(/^\/+/, ''));
      const matches = [...known].filter((candidate) => {
        if (!regex.test(candidate)) return false;
        // As in shell globbing, a wildcard skips dot segments; without `**` both have the same segment count.
        const patternSegments = pattern.replace(/^\/+/, '').split('/');
        if (candidate.split('/').some((segment, i) => segment.startsWith('.') && !(patternSegments[i] || '').startsWith('.'))) return false;
        // A git-ignored local folder is not in CI's checkout, so nothing requires a doc for it.
        return pathExists(candidate) && !isIgnored(config, candidate) && !pathCovers(CHECKER_DIRECTORY, candidate);
      });

      // A warning, not an error, so a fresh project with an empty app/code is not blocked.
      if (matches.length === 0) {
        findings.push(warning('require.unmatched', CONFIG_PATH, `\`${pattern}\` matches nothing. Remove it from \`require\`, or fix the path.`));
      }
      candidates.push(...matches);
    }

    for (const candidate of unique(candidates).sort(byBytes)) {
      if (claimed.some((covered) => pathCovers(covered, candidate))) continue;
      findings.push(error('covers.unclaimed', CONFIG_PATH, `\`${candidate}\` is claimed by no doc. Add it to the \`covers\` of the doc that describes it, or write a doc for it.`));
    }
    return findings;
  };

  // A review line that names no doc confirms nothing, so it is reported rather than ignored.
  const checkReviews = () => {
    const documentPaths = new Set(documents.map((document) => document.path));
    return reviews
      .filter((review) => review.path === null || !documentPaths.has(review.path))
      .map((review) =>
        warning(
          'review.unknown',
          review.commit === '' ? 'the commit message' : `commit ${review.commit.slice(0, 7)}`,
          review.path === null
            ? 'A `Docs-Reviewed:` line names no doc, so it confirms nothing.'
            : `\`Docs-Reviewed: ${review.path}\` names no doc under docs/, so it confirms nothing.`
        )
      );
  };

  // A doc in the change set counts as updated, and so does one a `Docs-Reviewed:` line on the branch names.
  const checkChangedCodeHasDocs = () => {
    if (changedFiles === null || changedFiles.length === 0) return [];

    const changed = new Set(changedFiles);
    const reviewed = new Set(reviews.filter((review) => review.path !== null).map((review) => review.path));
    const relevant = changedFiles.filter((file) => !isIgnored(config, file));
    const findings = [];

    for (const document of documents) {
      if (changed.has(document.path) || reviewed.has(document.path)) continue;

      const touched = [];
      for (const covered of coveredPaths(document)) {
        for (const file of relevant) {
          if (pathCovers(covered, file)) touched.push(file);
        }
      }
      if (touched.length === 0) continue;

      findings.push(
        error('doc.untouched', document.path, `Covers ${touched.length} changed file(s) but was not updated:\n${summarise(touched)}`)
      );
    }
    return findings;
  };

  /**
   * Positions are indexes in the branch's commits, oldest first; the working tree is newest. A doc edit and a review
   * line confirm the changes in their own commit and in its ancestors, so a change on a sibling branch is never
   * confirmed by a review it does not lead to. In a branch without merges that is every earlier commit.
   */
  const checkReviewOrder = () => {
    if (changedFiles === null || changedFiles.length === 0) return [];

    const workingTree = commits.length;
    const changesOf = new Map();
    const addChange = (file, position) => {
      if (!changesOf.has(file)) changesOf.set(file, []);
      changesOf.get(file).push(position);
    };
    commits.forEach((commit, position) => commit.files.forEach((file) => addChange(file, position)));
    workingTreeFiles.forEach((file) => addChange(file, workingTree));

    const positionOf = new Map(commits.map((commit, position) => [commit.commit, position]));
    const ancestry = new Map();
    // The positions a confirmation at `position` covers: itself and every ancestor in the branch.
    const ancestorsOf = (position) => {
      if (position === workingTree) return null;
      if (!ancestry.has(position)) {
        const seen = new Set([position]);
        const queue = [position];
        while (queue.length > 0) {
          for (const parent of commits[queue.pop()].parents) {
            const at = positionOf.get(parent);
            if (at !== undefined && !seen.has(at)) {
              seen.add(at);
              queue.push(at);
            }
          }
        }
        ancestry.set(position, seen);
      }
      return ancestry.get(position);
    };

    const confirmationsOf = (document) => {
      const positions = new Set(changesOf.get(document.path) || []);
      for (const review of reviews) if (review.path === document.path) positions.add(review.index);
      return [...positions];
    };

    // Only the branch's net change set counts: a file changed and changed back is no change.
    const changed = changedFiles.filter((file) => changesOf.has(file) && !isIgnored(config, file));
    const label = (position) => (position === workingTree ? 'uncommitted changes' : commits[position].commit.slice(0, 7));

    const findings = [];
    for (const document of documents) {
      const confirmations = confirmationsOf(document);
      if (confirmations.length === 0) continue;
      const covered = confirmations.map(ancestorsOf);
      const isConfirmed = (position) => covered.some((set) => set === null || set.has(position));

      const later = new Map();
      for (const file of changed) {
        if (file === document.path || !coveredPaths(document).some((claim) => pathCovers(claim, file))) continue;
        const unconfirmed = changesOf.get(file).filter((position) => !isConfirmed(position));
        if (unconfirmed.length > 0) later.set(file, Math.max(...unconfirmed));
      }
      if (later.size === 0) continue;

      findings.push(
        error(
          'review.stale',
          document.path,
          `Confirmed in ${label(Math.max(...confirmations))}, then changed again in ${label(Math.max(...later.values()))}:\n${summarise([...later.keys()])}`
        )
      );
    }
    return findings;
  };

  const findings = [];
  for (const document of documents) {
    const frontmatter = checkFrontmatter(document);
    if (frontmatter.length > 0) {
      // Without usable frontmatter nothing else about the doc can be judged.
      findings.push(...frontmatter);
      continue;
    }
    findings.push(...checkLifecycle(document), ...checkCoveredPathsExist(document), ...checkLinks(document));
  }

  return [
    ...findings,
    ...checkRequired(),
    ...checkReviews(),
    ...checkChangedCodeHasDocs(),
    ...checkReviewOrder(),
  ];
}

module.exports = { check, reviewLines };
