/**
 * @author    Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license   https://magebit.com/code-license
 */
'use strict';

const escapeRegExp = (char) => char.replace(/[.*+?^${}()|[\]\\/]/g, '\\$&');

// `**` crosses directories, `*` and `?` stay within one path segment.
function globToRegExp(pattern) {
  let source = '';
  for (let i = 0; i < pattern.length; i++) {
    const char = pattern[i];
    if (char === '*' && pattern[i + 1] === '*') {
      i++;
      // `**/` also matches zero directories, so `**/Test/**` matches a top-level Test/.
      if (pattern[i + 1] === '/') {
        i++;
        source += '(?:.*/)?';
      } else {
        source += '.*';
      }
    } else if (char === '*') {
      source += '[^/]*';
    } else if (char === '?') {
      source += '[^/]';
    } else {
      source += escapeRegExp(char);
    }
  }
  return new RegExp(`^${source}$`);
}

function matchesAny(path, patterns) {
  return patterns.some((pattern) => globToRegExp(pattern).test(path));
}

module.exports = { globToRegExp, matchesAny };
