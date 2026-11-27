/**
 * @author    Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license   https://magebit.com/code-license
 */
'use strict';

const fs = require('fs');
const path = require('path');
const { parseYaml, YamlError } = require('./yaml');
const { DOCS_DIRECTORY, CHECKER_DIRECTORY } = require('./config');

const FRONTMATTER = /^---\n(?:([\s\S]*?)\n)?---[ \t]*(?:\n|$)([\s\S]*)$/;

/**
 * Frontmatter follows the Open Knowledge Format (OKF v0.2): only `type` is required; `covers` is a
 * local extension, which OKF permits as an additional key.
 */
function parseDocument(contents, relativePath) {
  const normalised = contents.replace(/^﻿/, '').replace(/\r\n?/g, '\n');
  const match = FRONTMATTER.exec(normalised);
  if (!match) return { path: relativePath, frontmatter: null, body: normalised, parseError: null };

  let parsed;
  try {
    parsed = parseYaml(match[1] || '');
  } catch (error) {
    if (!(error instanceof YamlError)) throw error;
    // The reader counts from the first line inside the block; the file's own count starts at the opening ---.
    const where = error.line === undefined ? '' : `line ${error.line + 1}: `;
    return { path: relativePath, frontmatter: null, body: match[2], parseError: where + error.detail };
  }

  return { path: relativePath, frontmatter: parsed, body: match[2], parseError: null };
}

const get = (document, key) => {
  const value = document.frontmatter ? document.frontmatter[key] : undefined;
  return value === undefined || value === null ? null : value;
};

// Repository paths this document is responsible for describing: `covers` plus the OKF `resource`.
function coveredPaths(document) {
  const covers = get(document, 'covers');
  const paths = Array.isArray(covers) ? [...covers] : typeof covers === 'string' ? [covers] : [];

  const resource = get(document, 'resource');
  if (typeof resource === 'string' && resource !== '') paths.push(resource);

  return [...new Set(paths.filter((entry) => typeof entry === 'string' && entry !== ''))];
}

const pathCovers = (covered, candidate) => {
  const trimmed = covered.replace(/^\/+|\/+$/g, '');
  return candidate === trimmed || candidate.startsWith(`${trimmed}/`);
};

function walk(directory, found) {
  for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
    const full = path.join(directory, entry.name);
    if (entry.isDirectory()) walk(full, found);
    else if (entry.isFile() && path.extname(entry.name) === '.md') found.push(full);
  }
}

// Every Markdown file under docs/ that git knows, that `exclude` does not name and that is not the checker's own folder.
function loadDocuments(repoRoot, config, knownPaths) {
  const root = path.join(repoRoot, DOCS_DIRECTORY);
  if (!fs.existsSync(root) || !fs.statSync(root).isDirectory()) return [];

  const files = [];
  walk(root, files);

  const documents = [];
  for (const file of files) {
    const relativePath = path.relative(repoRoot, file).split(path.sep).join('/');
    // A gitignored doc exists only on this machine, so it neither blocks nor satisfies the check.
    if (knownPaths && !knownPaths.has(relativePath)) continue;
    if (pathCovers(CHECKER_DIRECTORY, relativePath)) continue;
    if (config.exclude.some((excluded) => pathCovers(excluded, relativePath))) continue;

    documents.push(parseDocument(fs.readFileSync(file, 'utf8'), relativePath));
  }

  return documents.sort((a, b) => (a.path < b.path ? -1 : a.path > b.path ? 1 : 0));
}

module.exports = { parseDocument, loadDocuments, coveredPaths, pathCovers, get };
