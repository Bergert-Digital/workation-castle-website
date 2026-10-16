import { test } from 'node:test';
import assert from 'node:assert/strict';
import {
  extractWrapperClass,
  parsePreservedNotes,
  buildCatalog,
  parentBlocksScript,
  parseParentBlocks,
  parseLegacyBlocks,
  legacyScript,
} from '../../tools/blocks-catalog.mjs';

test('extractWrapperClass finds the starter- wrapper class', () => {
  const php = `$w = get_block_wrapper_attributes( array( 'class' => 'starter-hero' ) );`;
  assert.equal(extractWrapperClass(php), 'starter-hero');
});

test('extractWrapperClass returns null when absent', () => {
  assert.equal(extractWrapperClass('<?php echo "nope";'), null);
});

test('parsePreservedNotes recovers human notes keyed by block name', () => {
  const doc = [
    '## pediment/hero',
    '',
    '**Use when:** the section is a page-leading headline with a primary CTA.',
    '',
    '## pediment/cta',
    '',
    '**Use when:** _(add guidance)_',
    '',
  ].join('\n');
  const notes = parsePreservedNotes(doc);
  assert.equal(notes['pediment/hero'], 'the section is a page-leading headline with a primary CTA.');
  assert.equal(notes['pediment/cta'], '_(add guidance)_');
});

test('buildCatalog emits a section per block with attrs, class, source, preserved notes', () => {
  const records = [
    {
      source: 'parent',
      blockJson: {
        name: 'pediment/hero', title: 'Hero', description: 'Page-leading headline.',
        attributes: { heading: { type: 'string', default: '' } },
        supports: { align: ['wide', 'full'] },
      },
      renderPhp: `array( 'class' => 'starter-hero' )`,
    },
    {
      source: 'child',
      blockJson: {
        name: 'workation/promo-banner', title: 'Promo Banner', description: 'Example.',
        attributes: { headline: { type: 'string', default: '' } },
      },
    },
  ];
  const existing = '## pediment/hero\n\n**Use when:** leading headline with CTA.\n';
  const md = buildCatalog(records, existing);

  assert.match(md, /^# Pediment block catalog/m);
  assert.match(md, /## pediment\/hero/);
  assert.match(md, /\*\*Source:\*\* parent/);
  assert.match(md, /\*\*Wrapper class:\*\* `starter-hero`/);
  assert.match(md, /`heading` \(string\)/);
  // preserved human note survives regeneration:
  assert.match(md, /\*\*Use when:\*\* leading headline with CTA\./);
  // new block with no prior note gets the editable marker:
  assert.match(md, /## workation\/promo-banner[\s\S]*\*\*Use when:\*\* _\(add guidance\)_/);
});

test('parentBlocksScript reads the plugin paths, not the retired theme path', () => {
  const script = parentBlocksScript();
  // Publish mode: the release zip installs as `pediment-plugin`.
  assert.match(script, /wp-content\/plugins\/pediment-plugin\/build\/blocks/);
  // Dev mode: wp-env mounts ../pediment/plugin under its basename, `plugin`.
  assert.match(script, /wp-content\/plugins\/plugin\/build\/blocks/);
  // Pediment stopped shipping as a theme; reading this path finds nothing.
  assert.doesNotMatch(script, /wp-content\/themes\/pediment/);
});

test('parseParentBlocks parses delimited block.json + render.php chunks', () => {
  const raw = [
    'wp-env banner noise',
    '@@@FILE:wp-content/plugins/pediment-plugin/build/blocks/hero/',
    '{"name":"pediment/hero","title":"Hero"}',
    '@@@RENDER:wp-content/plugins/pediment-plugin/build/blocks/hero/',
    `$w = get_block_wrapper_attributes( array( 'class' => 'starter-hero' ) );`,
    // Unmatched shell glob: FILE chunk with no JSON must be skipped, not fatal.
    '@@@FILE:wp-content/plugins/plugin/build/blocks/*/',
    '',
    '@@@RENDER:wp-content/plugins/plugin/build/blocks/*/',
    '',
  ].join('\n');
  const records = parseParentBlocks(raw);
  assert.equal(records.length, 1);
  assert.equal(records[0].source, 'parent');
  assert.equal(records[0].blockJson.name, 'pediment/hero');
  assert.match(records[0].renderPhp, /starter-hero/);
});

test('parseParentBlocks returns no records from noise-only output', () => {
  assert.deepEqual(parseParentBlocks('✔ Ran sh in cli.\n'), []);
});

test('parseLegacyBlocks reads Policy::LEGACY and ignores the other lists', () => {
  const php = `
	public const FUNCTIONAL = array(
		'pediment/form',
	);

	public const LEGACY = array(
		'pediment/hero',
		'pediment/faq-item',
	);

	public const RULES = array(
		'Use only the blocks in allowedBlocks.',
	);`;
  assert.deepEqual(parseLegacyBlocks(php), ['pediment/hero', 'pediment/faq-item']);
  assert.deepEqual(parseLegacyBlocks('<?php // no policy'), []);
});

test('legacyScript reads Policy.php from both plugin roots', () => {
  const script = legacyScript();
  assert.match(script, /plugins\/pediment-plugin\/src\/Content\/Policy\.php/);
  assert.match(script, /plugins\/plugin\/src\/Content\/Policy\.php/);
});

test('buildCatalog moves legacy blocks into a do-not-use part without attributes', () => {
  const records = [
    { source: 'parent', blockJson: { name: 'pediment/hero', title: 'Hero', description: 'Old.',
      attributes: { heading: { type: 'string' } } }, renderPhp: `'class' => 'starter-hero'` },
    { source: 'parent', blockJson: { name: 'pediment/form', title: 'Form', description: 'Forms.' } },
  ];
  const existing = '## pediment/hero\n\n**Use when:** leading headline with CTA.\n';
  const md = buildCatalog(records, existing, ['pediment/hero']);
  const legacyPart = md.slice(md.indexOf('# Legacy blocks'));
  assert.ok(md.indexOf('## pediment/form') < md.indexOf('# Legacy blocks'));
  assert.match(legacyPart, /## pediment\/hero[\s\S]*\*\*Status:\*\* legacy/);
  assert.match(legacyPart, /\*\*Use when:\*\* Do not use\./);
  assert.doesNotMatch(md, /leading headline with CTA/);
  assert.doesNotMatch(legacyPart, /Attributes|starter-hero/);
});
