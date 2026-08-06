import { test } from 'node:test';
import assert from 'node:assert/strict';
import { buildEntries } from './manifest-from-wxr.mjs';

const item = ( { id, title, slug, parent = '0', lang, group, status = 'publish' } ) => `
<item>
	<title>${ title }</title>
	<wp:post_id>${ id }</wp:post_id>
	<wp:post_name><![CDATA[${ slug }]]></wp:post_name>
	<wp:post_parent>${ parent }</wp:post_parent>
	<wp:status><![CDATA[${ status }]]></wp:status>
	<wp:post_type><![CDATA[page]]></wp:post_type>
	<category domain="language" nicename="${ lang }"><![CDATA[${ lang }]]></category>
	${ group ? `<category domain="post_translations" nicename="${ group }"><![CDATA[${ group }]]></category>` : '' }
</item>`;

const wrap = ( items ) => `<rss><channel>${ items.join( '' ) }</channel></rss>`;

test( 'an untranslated page becomes a plain entry', () => {
	const { entries } = buildEntries(
		wrap( [ item( { id: 190, title: 'Photos', slug: 'photos', lang: 'en' } ) ] )
	);

	assert.equal( entries.length, 1 );
	assert.deepEqual( entries[ 0 ], {
		key: 'photos',
		title: 'Photos',
		slug: 'photos',
		parent: null,
		languages: {},
	} );
} );

test( 'a translation group becomes per-language slug and title overrides', () => {
	const { entries } = buildEntries(
		wrap( [
			item( { id: 176, title: 'Home', slug: 'home', lang: 'en', group: 'g1' } ),
			item( { id: 582, title: 'Startseite', slug: 'startseite', lang: 'de', group: 'g1' } ),
			item( { id: 261, title: 'Home - Français', slug: 'home', lang: 'fr', group: 'g1' } ),
		] )
	);

	assert.equal( entries.length, 1 );
	assert.equal( entries[ 0 ].key, 'home' );
	assert.deepEqual( entries[ 0 ].languages, {
		de: { slug: 'startseite', title: 'Startseite' },
		fr: { slug: 'home', title: 'Home - Français' },
	} );
} );

test( 'a child page records its parent by key, not by id', () => {
	const { entries } = buildEntries(
		wrap( [
			item( { id: 201, title: 'Guide', slug: 'guide', lang: 'en' } ),
			item( { id: 241, title: 'FAQ', slug: 'faq', parent: '201', lang: 'en' } ),
		] )
	);

	assert.equal( entries.find( ( e ) => e.key === 'faq' ).parent, 'guide' );
} );

test( 'WordPress default pages are skipped and warned about', () => {
	const { entries, warnings } = buildEntries(
		wrap( [
			item( { id: 2, title: 'Beispiel-Seite', slug: 'beispiel-seite', lang: 'en' } ),
			item( { id: 190, title: 'Photos', slug: 'photos', lang: 'en' } ),
		] )
	);

	assert.deepEqual( entries.map( ( e ) => e.key ), [ 'photos' ] );
	assert.equal( warnings.length, 1 );
	assert.match( warnings[ 0 ], /beispiel-seite/ );
} );

test( 'a non-default-language page with no group is warned about, not silently dropped', () => {
	const { entries, warnings } = buildEntries(
		wrap( [ item( { id: 900, title: 'Waise', slug: 'waise', lang: 'de' } ) ] )
	);

	assert.equal( entries.length, 0 );
	assert.match( warnings[ 0 ], /waise/ );
} );
