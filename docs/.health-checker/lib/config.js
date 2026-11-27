/**
 * @author    Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license   https://magebit.com/code-license
 */
'use strict';

const fs = require('fs');
const path = require('path');
const { parseYaml } = require('./yaml');
const { matchesAny } = require('./glob');

const DOCS_DIRECTORY = 'docs';
const CHECKER_DIRECTORY = 'docs/.health-checker';
const CONFIG_PATH = `${CHECKER_DIRECTORY}/health-checker.yaml`;
const DEFAULT_VERSION = '1.0.0';
const DEFAULT_EXCLUDE = ['docs/archive/', 'docs/superpowers/'];
// Paths the check ignores on every project: a change to a test or a lock file says nothing a doc states.
const DEFAULT_IGNORE = [
  '**/Test/**',
  'composer.lock',
  '**/package.json',
  '**/package-lock.json',
  '**/yarn.lock',
  '**/pnpm-lock.yaml',
];

function readList(parsed, key) {
  if (!(key in parsed)) return null;
  // An empty key (`exclude:`) deliberately clears the default.
  if (parsed[key] === null) return [];

  const value = parsed[key];
  if (!Array.isArray(value)) throw new Error(`\`${key}\` in ${CONFIG_PATH} must be a list.`);
  for (const entry of value) {
    if (typeof entry !== 'string' || entry.trim() === '') {
      throw new Error(`\`${key}\` in ${CONFIG_PATH} must list non-empty strings.`);
    }
  }
  return value;
}

function loadConfig(repoRoot) {
  const defaults = {
    version: DEFAULT_VERSION,
    bases: null,
    exclude: DEFAULT_EXCLUDE,
    require: [],
    ignore: DEFAULT_IGNORE,
  };

  const file = path.join(repoRoot, CONFIG_PATH);
  if (!fs.existsSync(file)) return defaults;

  let parsed;
  try {
    parsed = parseYaml(fs.readFileSync(file, 'utf8'));
  } catch (error) {
    throw new Error(`${CONFIG_PATH} does not parse: ${error.message}`);
  }

  // The names before 1.3.0 still work: `claimable` is `require`; `unclaimable` and `untouched_ignore` are `ignore`.
  const requirePatterns = [];
  for (const key of ['require', 'claimable']) {
    for (const pattern of readList(parsed, key) || []) {
      // Each path is matched one folder level per `*`, so a `**` entry would silently over-match.
      if (pattern.includes('**')) {
        throw new Error(`\`${key}\` in ${CONFIG_PATH} cannot use ** (${pattern}). Use one * per directory level.`);
      }
      requirePatterns.push(pattern);
    }
  }
  const ignore = [
    ...DEFAULT_IGNORE,
    ...['ignore', 'unclaimable', 'untouched_ignore'].flatMap((key) => readList(parsed, key) || []),
  ];

  const base = parsed.base === undefined || parsed.base === null ? null : parsed.base;
  const bases = base === null ? null : Array.isArray(base) ? base : [base];
  if (bases !== null && (bases.length === 0 || bases.some((ref) => typeof ref !== 'string' || ref.trim() === ''))) {
    throw new Error(`\`base\` in ${CONFIG_PATH} must be a ref, e.g. origin/master, or a list of refs.`);
  }

  const version = parsed.version === undefined || parsed.version === null ? DEFAULT_VERSION : parsed.version;
  if (typeof version !== 'string' || !/^\d+\.\d+\.\d+$/.test(version)) {
    throw new Error(`\`version\` in ${CONFIG_PATH} must be a version such as 1.0.0.`);
  }

  return {
    version,
    bases,
    exclude: readList(parsed, 'exclude') || DEFAULT_EXCLUDE,
    require: requirePatterns,
    ignore,
  };
}

// An entry matches the path itself or a folder above it, so a folder entry covers everything below it.
// A trailing `/**` is dropped, so `dir/**` names `dir` itself as well.
const isIgnored = (config, filePath) => {
  const patterns = config.ignore.map((pattern) => pattern.replace(/^\/+|\/+$/g, '').replace(/\/\*\*$/, ''));
  for (let current = filePath; current !== '.' && current !== ''; current = path.posix.dirname(current)) {
    if (matchesAny(current, patterns)) return true;
  }
  return false;
};

module.exports = {
  loadConfig,
  isIgnored,
  DOCS_DIRECTORY,
  CHECKER_DIRECTORY,
  CONFIG_PATH,
};
