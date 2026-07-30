#!/usr/bin/env node
/**
 * Compile gettext .po catalogs to .mo, with no gettext binary and no wp-cli.
 *
 * WordPress reads .mo at runtime; .po is what humans review in a diff. Keeping
 * the compiler in pure Node means CI, a fresh checkout and a machine without
 * Homebrew gettext can all rebuild and verify the catalogs.
 *
 * Usage: node tools/i18n-mo.mjs [languagesDir]   (default: ./languages)
 */
import { readFileSync, writeFileSync, readdirSync } from 'node:fs';
import { join, basename } from 'node:path';

const EOT = '\u0004'; // msgctxt separator, as gettext defines it.
const NUL = '\u0000'; // plural-form separator.

/** Turn a quoted .po string literal into its value. */
function unquote(line) {
  const start = line.indexOf('"');
  const raw = line.slice(start + 1, line.lastIndexOf('"'));
  return raw.replace(/\\(u[0-9a-fA-F]{4}|.)/g, (_, esc) => {
    switch (esc[0]) {
      case 'n': return '\n';
      case 't': return '\t';
      case 'r': return '\r';
      case '"': return '"';
      case '\\': return '\\';
      case 'u': return String.fromCharCode(parseInt(esc.slice(1), 16));
      default: return esc;
    }
  });
}

/**
 * Parse a .po document into a msgid -> msgstr map.
 *
 * Untranslated (empty msgstr) and fuzzy entries are dropped, which is what
 * msgfmt does: a missing translation must fall through to the English msgid
 * rather than render as an empty string.
 */
export function parsePo(text) {
  const out = new Map();
  const lines = text.split(/\r?\n/);

  let ctxt = null;
  let id = null;
  let plural = null;
  let strs = [];
  let fuzzy = false;
  let target = null; // 'ctxt' | 'id' | 'plural' | number (msgstr index)

  const flush = () => {
    if (id === null) return;
    const translations = strs.filter((s) => s !== undefined);
    const value = plural === null ? (translations[0] ?? '') : translations.join(NUL);
    const isHeader = id === '' && ctxt === null;
    if (!fuzzy && (isHeader || value.replace(/\u0000/g, '') !== '')) {
      out.set(ctxt === null ? id : ctxt + EOT + id, value);
    }
    ctxt = null; id = null; plural = null; strs = []; fuzzy = false; target = null;
  };

  for (const line of lines) {
    const trimmed = line.trim();

    if (trimmed === '') { flush(); continue; }

    if (trimmed.startsWith('#')) {
      if (trimmed.startsWith('#,') && trimmed.includes('fuzzy')) fuzzy = true;
      continue;
    }

    if (trimmed.startsWith('msgctxt ')) { flush(); ctxt = unquote(trimmed); target = 'ctxt'; continue; }
    if (trimmed.startsWith('msgid_plural ')) { plural = unquote(trimmed); target = 'plural'; continue; }
    if (trimmed.startsWith('msgid ')) {
      if (id !== null) flush();
      id = unquote(trimmed); target = 'id'; continue;
    }
    if (trimmed.startsWith('msgstr[')) {
      const index = Number(trimmed.slice(7, trimmed.indexOf(']')));
      strs[index] = unquote(trimmed); target = index; continue;
    }
    if (trimmed.startsWith('msgstr ')) { strs[0] = unquote(trimmed); target = 0; continue; }

    if (trimmed.startsWith('"')) {
      const more = unquote(trimmed);
      if (target === 'ctxt') ctxt += more;
      else if (target === 'id') id += more;
      else if (target === 'plural') plural += more;
      else if (typeof target === 'number') strs[target] = (strs[target] ?? '') + more;
      continue;
    }
  }
  flush();

  return out;
}

/**
 * Serialize a msgid -> msgstr map into the binary MO format.
 *
 * Entries are sorted by msgid because gettext readers binary-search the
 * originals table. No hash table is written (size 0), which every reader,
 * including WordPress's, handles.
 */
export function compileMo(entries) {
  const items = [...entries.entries()]
    .sort(([a], [b]) => (a < b ? -1 : a > b ? 1 : 0))
    .map(([id, str]) => ({ id: Buffer.from(id, 'utf8'), str: Buffer.from(str, 'utf8') }));

  const count = items.length;
  const headerSize = 28;
  const originalsOffset = headerSize;
  const translationsOffset = originalsOffset + count * 8;
  let cursor = translationsOffset + count * 8;

  const originals = Buffer.alloc(count * 8);
  const translations = Buffer.alloc(count * 8);
  const blobs = [];

  items.forEach((item, i) => {
    originals.writeUInt32LE(item.id.length, i * 8);
    originals.writeUInt32LE(cursor, i * 8 + 4);
    blobs.push(item.id, Buffer.from([0]));
    cursor += item.id.length + 1;
  });
  items.forEach((item, i) => {
    translations.writeUInt32LE(item.str.length, i * 8);
    translations.writeUInt32LE(cursor, i * 8 + 4);
    blobs.push(item.str, Buffer.from([0]));
    cursor += item.str.length + 1;
  });

  const header = Buffer.alloc(headerSize);
  header.writeUInt32LE(0x950412de, 0);  // magic
  header.writeUInt32LE(0, 4);           // revision
  header.writeUInt32LE(count, 8);
  header.writeUInt32LE(originalsOffset, 12);
  header.writeUInt32LE(translationsOffset, 16);
  header.writeUInt32LE(0, 20);          // hash table size
  header.writeUInt32LE(cursor, 24);     // hash table offset

  return Buffer.concat([header, originals, translations, ...blobs]);
}

function main() {
  const dir = process.argv[2] || join(process.cwd(), 'languages');
  const files = readdirSync(dir).filter((f) => f.endsWith('.po'));
  if (files.length === 0) {
    console.error(`i18n-mo: no .po files in ${dir}`);
    process.exit(1);
  }
  for (const file of files) {
    const map = parsePo(readFileSync(join(dir, file), 'utf8'));
    const target = join(dir, basename(file, '.po') + '.mo');
    writeFileSync(target, compileMo(map));
    console.log(`i18n-mo: ${file} -> ${basename(target)} (${map.size} entries)`);
  }
}

if (process.argv[1] && process.argv[1].endsWith('i18n-mo.mjs')) {
  main();
}
