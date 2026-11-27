/**
 * @author    Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license   https://magebit.com/code-license
 */
'use strict';

class YamlError extends Error {
  constructor(message, line) {
    super(line === undefined ? message : `line ${line}: ${message}`);
    this.name = 'YamlError';
    this.detail = message;
    this.line = line;
  }
}

const MAX_DEPTH = 2;
const ESCAPES = { '"': '"', '\\': '\\', n: '\n', t: '\t', r: '\r', '/': '/', 0: '\0' };

function scalar(text) {
  if (text === 'true') return true;
  if (text === 'false') return false;
  return text;
}

function checkPlain(text, line) {
  if (/^[&*!|>@`]/.test(text)) {
    throw new YamlError(`"${text[0]}" starts an anchor, alias, tag, block scalar or reserved indicator; quote the value`, line);
  }
  if (/^[?:-](\s|$)/.test(text)) throw new YamlError('a value cannot start with an indicator; quote it', line);
  if (/:(\s|$)/.test(text)) throw new YamlError('a plain value cannot hold ": "; quote it', line);
}

// Reads a quoted scalar at the start of `text`; returns the value and what follows the closing quote.
function readQuoted(text, line) {
  const quote = text[0];
  let value = '';
  let i = 1;
  for (; i < text.length; i++) {
    const char = text[i];
    if (quote === '"' && char === '\\') {
      const escaped = ESCAPES[text[i + 1]];
      if (escaped === undefined) throw new YamlError(`unsupported escape \\${text[i + 1] || ''}`, line);
      value += escaped;
      i++;
    } else if (char === quote) {
      if (quote === "'" && text[i + 1] === "'") {
        value += "'";
        i++;
      } else {
        return { value, rest: text.slice(i + 1) };
      }
    } else {
      value += char;
    }
  }
  throw new YamlError('a quoted value must close on the same line', line);
}

function expectEnd(rest, line) {
  const trimmed = rest.trim();
  if (trimmed !== '' && !(trimmed[0] === '#' && /^\s/.test(rest))) {
    throw new YamlError(`unexpected text after the value: ${trimmed}`, line);
  }
}

function plainText(text) {
  const comment = text.search(/(^|\s)#/);
  return (comment === -1 ? text : text.slice(0, comment)).trim();
}

function parseScalar(text, line) {
  if (text[0] === '"' || text[0] === "'") {
    const { value, rest } = readQuoted(text, line);
    expectEnd(rest, line);
    return value;
  }
  const plain = plainText(text);
  checkPlain(plain, line);
  return scalar(plain);
}

function parseFlowList(text, line) {
  const items = [];
  let i = 1;
  const skip = () => {
    while (text[i] === ' ') i++;
  };
  skip();
  while (i < text.length) {
    if (text[i] === ']') {
      expectEnd(text.slice(i + 1), line);
      return items;
    }
    if (text[i] === '[' || text[i] === '{') throw new YamlError('nested flow collections are not supported', line);
    if (text[i] === ',') throw new YamlError('empty item in a flow list', line);
    if (text[i] === '"' || text[i] === "'") {
      const { value, rest } = readQuoted(text.slice(i), line);
      items.push(value);
      i = text.length - rest.length;
    } else {
      const end = text.slice(i).search(/[,\]]/);
      if (end === -1) break;
      const plain = text.slice(i, i + end).trim();
      checkPlain(plain, line);
      items.push(scalar(plain));
      i += end;
    }
    skip();
    if (text[i] === ',') {
      i++;
      skip();
    } else if (text[i] !== ']') {
      throw new YamlError('expected "," or "]" in a flow list', line);
    }
  }
  throw new YamlError('a flow list must close with "]" on the same line', line);
}

function splitKey(content, line) {
  if (content[0] === '"' || content[0] === "'") {
    const { value, rest } = readQuoted(content, line);
    if (!rest.startsWith(':')) throw new YamlError('expected "key: value"', line);
    return { key: value, value: rest.slice(1) };
  }
  const match = /:(\s|$)/.exec(content);
  if (!match || match.index === 0) throw new YamlError('expected "key: value"', line);
  return { key: content.slice(0, match.index).trim(), value: content.slice(match.index + 1) };
}

function tokenize(text) {
  const lines = [];
  text.split('\n').forEach((raw, index) => {
    const line = index + 1;
    const content = raw.replace(/\s+$/, '');
    if (content.trim() === '' || content.trim()[0] === '#') return;
    const indent = content.length - content.trimStart().length;
    if (/\t/.test(content.slice(0, indent))) throw new YamlError('tabs are not allowed for indentation', line);
    lines.push({ line, indent, content: content.slice(indent) });
  });
  return lines;
}

const isItem = (content) => content === '-' || content.startsWith('- ');

function parseList(lines, pos, indent) {
  const items = [];
  while (pos < lines.length && lines[pos].indent === indent && isItem(lines[pos].content)) {
    const { line, content } = lines[pos];
    const rest = content.slice(1).trim();
    if (rest === '' || rest[0] === '#') {
      items.push(null);
    } else if (rest[0] === '[' || rest[0] === '{' || isItem(rest)) {
      throw new YamlError('only a list of plain or quoted values is supported', line);
    } else {
      items.push(parseScalar(rest, line));
    }
    pos++;
  }
  if (pos < lines.length && lines[pos].indent > indent) {
    throw new YamlError('unexpected indentation', lines[pos].line);
  }
  return { value: items, pos };
}

function parseMapping(lines, pos, indent, depth) {
  const result = {};
  while (pos < lines.length) {
    const { line, indent: lineIndent, content } = lines[pos];
    if (lineIndent !== indent) throw new YamlError('unexpected indentation', line);
    if (isItem(content)) throw new YamlError('a list item where a key was expected', line);

    const { key, value } = splitKey(content, line);
    if (key === '__proto__') throw new YamlError('"__proto__" is not a usable key', line);
    if (Object.prototype.hasOwnProperty.call(result, key)) throw new YamlError(`duplicate key "${key}"`, line);
    pos++;

    const trimmed = value.trim();
    if (trimmed === '' || trimmed[0] === '#') {
      const next = lines[pos];
      if (next && next.indent === indent && isItem(next.content)) {
        ({ value: result[key], pos } = parseList(lines, pos, indent));
      } else if (next && next.indent > indent) {
        if (isItem(next.content)) {
          ({ value: result[key], pos } = parseList(lines, pos, next.indent));
        } else if (depth + 1 >= MAX_DEPTH) {
          throw new YamlError('mappings nested deeper than one level are not supported', next.line);
        } else {
          ({ value: result[key], pos } = parseMapping(lines, pos, next.indent, depth + 1));
        }
      } else {
        result[key] = null;
      }
    } else {
      if (trimmed[0] === '[') {
        result[key] = parseFlowList(trimmed, line);
      } else if (trimmed[0] === '{') {
        throw new YamlError('flow mappings are not supported', line);
      } else {
        result[key] = parseScalar(trimmed, line);
      }
      if (lines[pos] && lines[pos].indent > indent) throw new YamlError('unexpected indentation', lines[pos].line);
    }

    if (pos < lines.length && lines[pos].indent < indent) break;
  }
  return { value: result, pos };
}

function parseYaml(text) {
  const normalised = text.replace(/^﻿/, '').replace(/\r\n?/g, '\n');
  const lines = tokenize(normalised);
  if (lines.length > 0 && lines[0].content === '---' && lines[0].indent === 0) lines.shift();
  if (lines.length === 0) return {};

  const first = lines[0];
  if (isItem(first.content)) throw new YamlError('expected a key/value map, found a list', first.line);
  const { value, pos } = parseMapping(lines, 0, first.indent, 0);
  if (pos < lines.length) throw new YamlError('unexpected indentation', lines[pos].line);
  return value;
}

module.exports = { parseYaml, YamlError };
