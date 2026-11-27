/**
 * @author    Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license   https://magebit.com/code-license
 */
'use strict';

const { execFileSync } = require('child_process');

const nonEmpty = (items) => items.filter((item) => item !== '');

function createGit(repoRoot) {
  // Null when git fails: a missing ref or merge state is an answer, not a crash.
  const tryRun = (args) => {
    try {
      return execFileSync('git', ['-C', repoRoot, ...args], {
        encoding: 'utf8',
        maxBuffer: 1 << 28,
        stdio: ['ignore', 'pipe', 'pipe'],
      });
    } catch {
      return null;
    }
  };

  const run = (args) => {
    const output = tryRun(args);
    if (output === null) throw new Error(`could not read the change set: git ${args.join(' ')} failed.`);
    return output;
  };

  const nulList = (args) => nonEmpty(run(args).split('\0'));

  return {
    currentBranch: () => (tryRun(['rev-parse', '--abbrev-ref', 'HEAD']) || '').trim(),

    originHead: () => (tryRun(['symbolic-ref', '--quiet', '--short', 'refs/remotes/origin/HEAD']) || '').trim() || null,

    refExists: (ref) => tryRun(['rev-parse', '--verify', '--quiet', `${ref}^{commit}`]) !== null,

    isMerging: () => tryRun(['rev-parse', '--quiet', '--verify', 'MERGE_HEAD']) !== null,

    // A count git cannot give ranks last, so a broken ref is never picked as the closest base.
    commitsAhead: (ref) => {
      const output = tryRun(['rev-list', '--count', `${ref}..HEAD`]);
      return output === null ? Infinity : Number(output.trim());
    },

    mergeBase: (base) => (tryRun(['merge-base', base, 'HEAD']) || '').trim() || null,

    commitMessagePath: () => (tryRun(['rev-parse', '--path-format=absolute', '--git-path', 'COMMIT_EDITMSG']) || '').trim(),

    // What CI checks out: tracked files, plus new ones that are not ignored.
    trackedPaths: () => nulList(['ls-files', '--cached', '--others', '--exclude-standard', '-z']),

    untrackedPaths: () => nulList(['ls-files', '--others', '--exclude-standard', '-z']),

    // Working tree against a commit; --no-renames keeps a moved file's old path.
    changedSince: (commit) => nulList(['diff', '--no-renames', '--name-only', '-z', commit]),

    /**
     * The commits since the merge base, oldest first, each with its message, its parents inside that range and the
     * files it changed.
     * A merge commit lists only the files its resolution changed: those that differ both from every parent
     * and from the merge git would have made on its own. Without a git that can tell (2.36 or newer), a
     * merge lists no files, so a merge never raises a finding it cannot prove.
     */
    commitsSince: (mergeBase) => {
      const range = `${mergeBase}..HEAD`;
      const fileLog = (args) => [
        '-c', 'core.quotePath=false', 'log', '--topo-order', '--reverse', '--no-renames', '--name-only',
        ...args, '--format=%x01%H', range,
      ];
      const byCommit = (output) => {
        const map = new Map();
        for (const chunk of output.split('\x01')) {
          if (chunk.trim() === '') continue;
          const lines = chunk.trim().split('\n');
          map.set(lines.shift(), nonEmpty(lines));
        }
        return map;
      };

      const messageFields = run(['log', '-z', '--format=%H%x00%B', range]).split('\0');
      const messages = new Map();
      for (let index = 0; index + 1 < messageFields.length; index += 2) messages.set(messageFields[index], messageFields[index + 1]);

      const files = byCommit(run(fileLog([])));
      const merges = nonEmpty(run(['rev-list', '--merges', range]).split('\n'));
      if (merges.length > 0) {
        const combined = tryRun(fileLog(['--merges', '--diff-merges=dense-combined']));
        const remerged = tryRun(fileLog(['--merges', '--diff-merges=remerge']));
        const fromParents = combined === null ? new Map() : byCommit(combined);
        const fromRemerge = remerged === null ? new Map() : byCommit(remerged);
        for (const merge of merges) {
          const resolved = new Set(fromRemerge.get(merge) || []);
          files.set(merge, (fromParents.get(merge) || []).filter((file) => resolved.has(file)));
        }
      }

      const parents = new Map();
      for (const line of nonEmpty(run(['rev-list', '--parents', range]).split('\n'))) {
        const [commit, ...rest] = line.trim().split(' ');
        parents.set(commit, rest);
      }

      return [...files.entries()].map(([commit, changed]) => ({
        commit,
        files: changed,
        message: messages.get(commit) || '',
        parents: (parents.get(commit) || []).filter((parent) => files.has(parent)),
      }));
    },
  };
}

module.exports = { createGit };
