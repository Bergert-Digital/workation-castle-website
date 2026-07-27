#!/usr/bin/env node
/**
 * Activate Polylang in the dev environment and configure it, so a freshly
 * started dev server is a working multilingual site rather than a WordPress
 * with a dormant plugin.
 *
 * Runs after the content seed in setup-env.mjs, and that order is deliberate:
 * the seed creates pages, the navigation menu and CPT posts with no language
 * attached, and untagged content behaves inconsistently under Polylang — an
 * untagged navigation menu is what silently removed the header on a live site.
 * Tagging therefore has to follow every seed, not happen once.
 *
 * The actual work lives in tools/polylang-setup.php, because Polylang's free
 * build ships no WP-CLI commands and everything has to go through its PHP API.
 * This wrapper only resolves the theme slug (wp-env names the mount after the
 * host directory, so it differs per workspace) and shells out.
 *
 * Idempotent: safe on every `npm run env:setup`.
 */

import { execFileSync } from 'node:child_process';
import { basename } from 'node:path';
import process from 'node:process';

const themeSlug = basename(process.cwd());

const wp = (...args) =>
	execFileSync('npx', ['wp-env', 'run', 'cli', 'wp', ...args], {
		encoding: 'utf8',
		stdio: ['ignore', 'pipe', 'pipe'],
	});

export function setupPolylang() {
	// Installed here rather than through .wp-env.json, which only accepts paths,
	// zip URLs or git repos — not wordpress.org slugs. Keeping it here also keeps
	// the whole Polylang concern in one file. Unpinned on purpose: this is a dev
	// convenience, and tracking whatever clients actually run is the point.
	const installed = wp('plugin', 'list', '--field=name').includes('polylang');
	if (!installed) {
		console.log('  installing polylang…');
		wp('plugin', 'install', 'polylang');
	}

	const active = wp('plugin', 'list', '--status=active', '--field=name').includes('polylang');
	if (!active) {
		console.log(`  ${wp('plugin', 'activate', 'polylang').trim()}`);
	}

	const output = wp(
		'eval-file',
		`wp-content/themes/${themeSlug}/tools/polylang-setup.php`
	);
	process.stdout.write(
		output
			.split('\n')
			.filter((line) => line.startsWith('polylang:'))
			.map((line) => `  ${line}\n`)
			.join('')
	);
}

// Allow `node tools/setup-polylang.mjs` as well as being imported by setup-env.
if (import.meta.url === `file://${process.argv[1]}`) {
	try {
		setupPolylang();
	} catch (err) {
		console.error(err.stderr?.toString() || err.message);
		process.exit(err.status ?? 1);
	}
}
