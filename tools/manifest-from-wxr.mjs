#!/usr/bin/env node
/**
 * Build seed/manifest.php's entry list from a WordPress XML export.
 *
 * The site's translated pages exist only in the database — patterns/ is
 * English-only — so their slugs and titles cannot be read out of this repo.
 * Transcribing ~24 rows by hand is how a wrong slug becomes a silent
 * `no-match` in a claim preview, so they are generated instead, and this
 * script is committed so it can be re-run against a fresh export immediately
 * before the cutover as a drift check.
 *
 * Usage: node tools/manifest-from-wxr.mjs export.xml > /tmp/entries.php
 */
import fs from 'node:fs';

/** Pages WordPress creates itself. They were never seeded and are not ours. */
const WORDPRESS_DEFAULTS = new Set( [
	'beispiel-seite',
	'sample-page',
	'datenschutzerklaerung',
	'privacy-policy-2',
] );

const DEFAULT_LANGUAGE = 'en';

const tag = ( xml, name ) => {
	const m = xml.match(
		new RegExp( `<${ name }>(?:<!\\[CDATA\\[)?([\\s\\S]*?)(?:\\]\\]>)?</${ name }>` )
	);
	return m ? m[ 1 ].trim() : '';
};

const term = ( xml, domain ) => {
	const m = xml.match( new RegExp( `<category domain="${ domain }" nicename="([^"]*)"` ) );
	return m ? m[ 1 ] : '';
};

/**
 * @param {string} xml Full WXR document.
 * @returns {{entries: object[], warnings: string[]}}
 */
export function buildEntries( xml ) {
	const warnings = [];

	const pages = xml
		.split( '<item>' )
		.slice( 1 )
		.map( ( chunk ) => '<item>' + chunk.split( '</item>' )[ 0 ] )
		.filter( ( chunk ) => tag( chunk, 'wp:post_type' ) === 'page' )
		.map( ( chunk ) => ( {
			id: tag( chunk, 'wp:post_id' ),
			title: tag( chunk, 'title' ),
			slug: decodeURIComponent( tag( chunk, 'wp:post_name' ) ),
			parent: tag( chunk, 'wp:post_parent' ),
			language: term( chunk, 'language' ) || DEFAULT_LANGUAGE,
			group: term( chunk, 'post_translations' ),
		} ) );

	const byId = new Map( pages.map( ( p ) => [ p.id, p ] ) );
	const kept = pages.filter( ( p ) => {
		if ( WORDPRESS_DEFAULTS.has( p.slug ) ) {
			warnings.push(
				`skipped "${ p.slug }" (ID ${ p.id }): a WordPress default page, never seeded.`
			);
			return false;
		}
		return true;
	} );

	// Group members are found by their shared post_translations term. A
	// non-default-language page with no group is an orphan translation: it
	// belongs to no entry, so declaring it is impossible and dropping it
	// quietly would hide a real content problem.
	const translations = new Map();
	for ( const page of kept ) {
		if ( page.language === DEFAULT_LANGUAGE || ! page.group ) {
			continue;
		}
		if ( ! translations.has( page.group ) ) {
			translations.set( page.group, [] );
		}
		translations.get( page.group ).push( page );
	}

	for ( const page of kept ) {
		if ( page.language !== DEFAULT_LANGUAGE && ! page.group ) {
			warnings.push(
				`orphan translation "${ page.slug }" (${ page.language }, ID ${ page.id }): no translation group, so no entry claims it.`
			);
		}
	}

	const entries = kept
		.filter( ( p ) => p.language === DEFAULT_LANGUAGE )
		.map( ( page ) => {
			const languages = {};
			for ( const t of translations.get( page.group ) ?? [] ) {
				languages[ t.language ] = { slug: t.slug, title: t.title };
			}

			return {
				key: page.slug,
				title: page.title,
				slug: page.slug,
				parent: page.parent !== '0' ? byId.get( page.parent )?.slug ?? null : null,
				languages,
			};
		} );

	return { entries, warnings };
}

const php = ( value ) =>
	"'" + String( value ).replace( /\\/g, '\\\\' ).replace( /'/g, "\\'" ) + "'";

function render( entries ) {
	const lines = [];
	for ( const entry of entries ) {
		lines.push( `\t\t${ php( entry.key ) } => array(` );
		lines.push( `\t\t\t'title'   => ${ php( entry.title ) },` );
		lines.push( `\t\t\t'slug'    => ${ php( entry.slug ) },` );
		lines.push( `\t\t\t'pattern' => ${ php( 'workation/' + entry.key ) },` );
		if ( entry.parent ) {
			lines.push( `\t\t\t'parent'  => ${ php( entry.parent ) },` );
		}
		const languages = Object.entries( entry.languages );
		if ( languages.length > 0 ) {
			lines.push( `\t\t\t'languages' => array(` );
			for ( const [ code, override ] of languages ) {
				lines.push(
					`\t\t\t\t${ php( code ) } => array( 'slug' => ${ php( override.slug ) }, 'title' => ${ php( override.title ) } ),`
				);
			}
			lines.push( `\t\t\t),` );
		}
		lines.push( `\t\t),` );
	}
	return lines.join( '\n' );
}

if ( process.argv[ 1 ] && process.argv[ 1 ].endsWith( 'manifest-from-wxr.mjs' ) ) {
	const file = process.argv[ 2 ];
	if ( ! file ) {
		console.error( 'Usage: node tools/manifest-from-wxr.mjs <export.xml>' );
		process.exit( 1 );
	}
	const { entries, warnings } = buildEntries( fs.readFileSync( file, 'utf8' ) );
	for ( const warning of warnings ) {
		console.error( 'warning: ' + warning );
	}
	console.error( `${ entries.length } entries.` );
	console.log( render( entries ) );
}
