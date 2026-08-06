#!/usr/bin/env node
/**
 * Delete the placeholder pages WordPress creates for itself on install, so they
 * cannot squat a slug the seed manifest declares.
 *
 * A fresh WordPress install creates a draft "Privacy Policy" page at
 * `privacy-policy` and a published "Sample Page" at `sample-page`. Neither is
 * ours. But `seed/manifest.php` declares a `privacy-policy` entry, and
 * `wp_unique_post_slug()` does not care that the squatter is an untouched
 * draft: the seeded page lands at `privacy-policy-2` and the seeder's verifier
 * — correctly — fails the run.
 *
 * Staging already looks like this: WordPress's placeholder there was renamed to
 * `datenschutzerklaerung` years ago, which is why the live site's privacy page
 * holds the plain slug. This makes a fresh environment match.
 *
 * Only untouched placeholders are removed: a page is left alone the moment
 * anyone has edited it (post_modified differs from post_date) or the seeder has
 * claimed it. Idempotent — a second run finds nothing to do.
 *
 * Usage: node tools/free-wp-default-pages.mjs
 */

import { execFileSync } from 'node:child_process';
import process from 'node:process';

// Slug => the status WordPress leaves its own placeholder in. A page in any
// other status has been acted on by a human and is not a placeholder any more.
const PLACEHOLDERS = { 'privacy-policy': 'draft', 'sample-page': 'publish' };

const phpArray = ( obj ) =>
	'array( ' +
	Object.entries( obj )
		.map( ( [ k, v ] ) => `"${ k }" => "${ v }"` )
		.join( ', ' ) +
	' )';

// One line, deliberately: `wp-env run` does not preserve newlines in a command
// argument, so a multi-line snippet reaches WP-CLI truncated at the first one.
const php = [
	'$out = array();',
	`foreach ( ${ phpArray( PLACEHOLDERS ) } as $slug => $status ) {`,
	'$pages = get_posts( array( "post_type" => "page", "post_status" => $status, "name" => $slug, "posts_per_page" => 1, "lang" => "", "suppress_filters" => true ) );',
	'if ( ! $pages ) { continue; }',
	'$page = $pages[0];',
	// An edited page is somebody's work, not a placeholder.
	'if ( $page->post_modified_gmt !== $page->post_date_gmt ) { continue; }',
	// Never touch a page the seeder owns.
	'if ( get_post_meta( $page->ID, "_pediment_seed_key", true ) ) { continue; }',
	'wp_delete_post( $page->ID, true );',
	'$out[] = $slug . " (ID " . $page->ID . ")";',
	'}',
	'echo "FREED:" . ( $out ? implode( "; ", $out ) : "nothing" );',
].join( ' ' );

try {
	const raw = execFileSync( 'npx', [ 'wp-env', 'run', 'cli', 'wp', 'eval', php ], {
		encoding: 'utf8',
		stdio: [ 'ignore', 'pipe', 'inherit' ],
	} );
	// WP_DEBUG is on, so a notice can land ahead of our own output. A
	// sentinel-prefixed token survives that; matching the whole stream would not.
	const match = raw.match( /FREED:(.*)/ );
	process.stdout.write( `[free-wp-default-pages] ${ match ? match[ 1 ].trim() : 'no result' }\n` );
	if ( ! match ) {
		process.exit( 1 );
	}
} catch ( err ) {
	process.stderr.write( `[free-wp-default-pages] ${ err.message }\n` );
	process.exit( 1 );
}
