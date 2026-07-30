import { test } from 'node:test';
import assert from 'node:assert/strict';
import { parsePo, compileMo } from '../../tools/i18n-mo.mjs';

test('parsePo reads a simple entry', () => {
  const po = [
    'msgid ""',
    'msgstr "Content-Type: text/plain; charset=UTF-8\\n"',
    '',
    'msgid "Arrival"',
    'msgstr "Anreise"',
  ].join('\n');
  const map = parsePo(po);
  assert.equal(map.get('Arrival'), 'Anreise');
});

test('parsePo joins multi-line strings', () => {
  const po = [
    'msgid ""',
    '"We use cookies "',
    '"and external services."',
    'msgstr ""',
    '"Wir verwenden Cookies "',
    '"und externe Dienste."',
  ].join('\n');
  // A leading empty msgid with continuation lines is a real entry, not the header.
  const map = parsePo(po);
  assert.equal(
    map.get('We use cookies and external services.'),
    'Wir verwenden Cookies und externe Dienste.'
  );
});

test('parsePo unescapes quotes, newlines and backslashes', () => {
  const po = 'msgid "a \\"b\\"\\nc\\\\d"\nmsgstr "x \\"y\\"\\nz\\\\w"';
  assert.equal(parsePo(po).get('a "b"\nc\\d'), 'x "y"\nz\\w');
});

test('parsePo keys context entries with the EOT separator', () => {
  const po = 'msgctxt "verb"\nmsgid "Close"\nmsgstr "Schliessen"';
  assert.equal(parsePo(po).get('verb\u0004Close'), 'Schliessen');
});

test('parsePo joins plural forms with NUL', () => {
  const po = [
    'msgid "%d night"',
    'msgid_plural "%d nights"',
    'msgstr[0] "%d Nacht"',
    'msgstr[1] "%d Nächte"',
  ].join('\n');
  assert.equal(parsePo(po).get('%d night'), '%d Nacht\u0000%d Nächte');
});

test('parsePo skips untranslated and fuzzy entries but keeps the header', () => {
  const po = [
    'msgid ""',
    'msgstr "Language: de_DE\\n"',
    '',
    'msgid "Untranslated"',
    'msgstr ""',
    '',
    '#, fuzzy',
    'msgid "Guessed"',
    'msgstr "Geraten"',
    '',
    'msgid "Good"',
    'msgstr "Gut"',
  ].join('\n');
  const map = parsePo(po);
  assert.equal(map.has('Untranslated'), false);
  assert.equal(map.has('Guessed'), false);
  assert.equal(map.get('Good'), 'Gut');
  assert.equal(map.get(''), 'Language: de_DE\n');
});

test('parsePo ignores comment lines', () => {
  const po = [
    '# translator note',
    '#. extracted comment',
    '#: inc/AvailabilityForm.php:50',
    '#, php-format',
    'msgid "Hi %s"',
    'msgstr "Hallo %s"',
  ].join('\n');
  assert.equal(parsePo(po).get('Hi %s'), 'Hallo %s');
});

test('compileMo writes a readable MO with the right magic and count', () => {
  const mo = compileMo(new Map([['', 'Language: de_DE\n'], ['Arrival', 'Anreise']]));
  assert.equal(mo.readUInt32LE(0), 0x950412de, 'little-endian magic');
  assert.equal(mo.readUInt32LE(4), 0, 'revision 0');
  assert.equal(mo.readUInt32LE(8), 2, 'two entries including the header');
});

test('compileMo round-trips entries in sorted msgid order', () => {
  const entries = new Map([
    ['', 'Language: de_DE\n'],
    ['Zebra', 'Zebra'],
    ['Arrival', 'Anreise'],
    ['Gäste', 'Gäste'],
  ]);
  const mo = compileMo(entries);
  const count = mo.readUInt32LE(8);
  const originalsOffset = mo.readUInt32LE(12);
  const translationsOffset = mo.readUInt32LE(16);

  const read = (tableOffset, index) => {
    const len = mo.readUInt32LE(tableOffset + index * 8);
    const off = mo.readUInt32LE(tableOffset + index * 8 + 4);
    return mo.subarray(off, off + len).toString('utf8');
  };

  const ids = [];
  for (let i = 0; i < count; i++) {
    ids.push(read(originalsOffset, i));
    assert.equal(read(translationsOffset, i), entries.get(read(originalsOffset, i)));
  }
  assert.deepEqual(ids, [...ids].sort(), 'msgids must be sorted for binary search');
  assert.equal(ids[0], '', 'the header entry sorts first');
});
