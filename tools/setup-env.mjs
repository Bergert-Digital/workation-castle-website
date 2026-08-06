#!/usr/bin/env node
/**
 * One-shot dev-env bootstrap. Boots wp-env, activates the theme and the
 * Pediment plugin, configures Polylang from the manifest's language list, and
 * builds the whole site from seed/manifest.php — so a fresh clone goes from
 * `npm install` to a working site in a single step.
 *
 * Why this script exists:
 *   - `wp-env start` installs themes and plugins but does not reliably leave
 *     the right one active, and every `wp pediment …` command needs the plugin
 *     loaded.
 *   - The theme is mounted under its host directory's basename (that is how
 *     wp-env names the mount), which is the Conductor workspace name, not
 *     `workation`. Hard-coding a slug in package.json would break for anyone
 *     who clones into a differently-named folder, so it is resolved here.
 *
 * Order matters: languages before the seed. `wp pediment seed` crosses every
 * entry with every language Polylang reports, so seeding first would create the
 * English pages and then have to reconcile them.
 *
 * Idempotent: every step is a no-op on already-active / already-seeded state.
 *
 * Exit codes:
 *   0 — full flow completed
 *   non-zero — propagated from whichever child step failed
 */

import { execFileSync } from 'node:child_process';
import { basename } from 'node:path';
import process from 'node:process';
import { ensurePort } from './ensure-port.mjs';

const themeSlug = basename(process.cwd());

const run = (label, cmd, args) => {
	console.log(`\n› ${label}`);
	execFileSync(cmd, args, { stdio: 'inherit' });
};

const wp = (label, ...args) =>
	run(label, 'npx', ['wp-env', 'run', 'cli', 'wp', ...args]);

try {
	const { port } = await ensurePort();
	console.log(`\n› wp-env port ${port} → http://localhost:${port}`);
	run('wp-env start', 'npx', ['wp-env', 'start']);
	wp(`activate theme (${themeSlug})`, 'theme', 'activate', themeSlug);
	wp('activate the Pediment plugin', 'plugin', 'activate', 'pediment-plugin');
	wp('activate Polylang', 'plugin', 'activate', 'polylang');
	wp('configure languages', 'pediment', 'languages');
	wp('seed pages and navigations', 'pediment', 'seed');
	wp('seed the photo library and activities', 'workation', 'content');
	// The seed's flush_rewrite_rules() runs under WP-CLI, where got_mod_rewrite()
	// is false, so it never writes .htaccess and every pretty URL 404s at Apache.
	wp('flush rewrite rules', 'rewrite', 'flush', '--hard');
	console.log('\n✔ env ready.');
} catch (err) {
	process.exit(err.status ?? 1);
}
