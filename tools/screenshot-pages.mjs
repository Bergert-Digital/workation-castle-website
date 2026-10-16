#!/usr/bin/env node
/**
 * Full-page screenshots of a list of site paths, at several viewport widths.
 * Used to capture "before" and "after" pictures around a Pediment upgrade, so
 * the two sets can be compared page by page.
 *
 * Why this script exists:
 *   - A plugin upgrade can change block markup and CSS without any test
 *     failing. Only a picture of the real page shows it.
 *   - Output has to be reproducible: the same page at the same width must give
 *     the same pixels twice, or a diff shows noise instead of changes. So the
 *     script grants consent up front (no cookie banner over the page), asks
 *     for reduced motion, freezes CSS animations, waits for web fonts and for
 *     the network to settle, and scrolls the whole page once so lazy images
 *     load before the shot.
 *   - It is read-only. It only sends GET requests, two pages at a time, so it
 *     is safe to point at production. Well-known analytics and ad hosts are
 *     blocked, because the consent cookie is fully granted and 150 robot page
 *     views would otherwise land in the client's statistics.
 *
 * The consent cookie is the same one `seedConsent()` sets in
 * tests/e2e/utils.ts, scoped to the origin passed in `--base`.
 *
 * Usage:
 *   node tools/screenshot-pages.mjs --base http://localhost:8890 \
 *     --list .context/screens/dev-legacy-paths.txt \
 *     --out .context/screens/baseline/dev [--widths 375,768,1440]
 *
 * `--list` is a text file with one URL path per line (blank lines and lines
 * starting with # are ignored). Each path and width gives one PNG named
 * `<slug-path>@<width>.png`, for example `/de/guide/anreise/` at 375 pixels
 * becomes `de-guide-anreise@375.png`.
 *
 * Needs a Chromium for Playwright: `npx playwright install chromium`.
 *
 * Exit codes:
 *   0 — every page was captured at every width
 *   1 — at least one page failed (all pages are still tried first)
 *   2 — bad arguments
 */

import { mkdirSync, readFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import process from 'node:process';
import { chromium } from '@playwright/test';

const CONCURRENCY = 2;
const VIEWPORT_HEIGHT = 900;
const NAV_TIMEOUT_MS = 45_000;
const IDLE_TIMEOUT_MS = 15_000;

// Hosts that only collect statistics. Aborting them keeps the run out of the
// client's analytics and out of the network-idle wait.
const TRACKER_HOSTS =
	/(^|\.)(google-analytics\.com|googletagmanager\.com|doubleclick\.net|facebook\.net|facebook\.com|connect\.facebook\.net|hotjar\.com|clarity\.ms|plausible\.io|matomo\.cloud)$/;

const usage = () => {
	console.error(
		'Usage: node tools/screenshot-pages.mjs --base <url> --list <file> --out <dir> [--widths 375,768,1440]'
	);
	process.exit( 2 );
};

const parseArgs = ( argv ) => {
	const args = { widths: '375,768,1440' };
	for ( let i = 0; i < argv.length; i += 2 ) {
		const key = argv[ i ];
		const value = argv[ i + 1 ];
		if ( ! key?.startsWith( '--' ) || value === undefined ) {
			usage();
		}
		args[ key.slice( 2 ) ] = value;
	}
	if ( ! args.base || ! args.list || ! args.out ) {
		usage();
	}
	const widths = args.widths.split( ',' ).map( Number );
	if ( widths.some( ( w ) => ! Number.isInteger( w ) || w <= 0 ) ) {
		usage();
	}
	return { ...args, widths };
};

const slugFor = ( path ) =>
	path.replace( /[?#].*$/, '' ).replace( /^\/+|\/+$/g, '' ).replace( /\//g, '-' ) ||
	'home';

// Same cookie as seedConsent() in tests/e2e/utils.ts.
const consentCookie = ( origin ) => ( {
	name: 'wc_consent',
	value: encodeURIComponent(
		JSON.stringify( {
			version: 1,
			timestamp: 1750000000,
			functional: true,
			analytics: true,
			marketing: true,
		} )
	),
	url: origin,
} );

// Walk down the page in viewport steps so lazy images and scroll-triggered
// content load, then return to the top for the full-page capture.
const loadLazyContent = ( page ) =>
	page.evaluate( async () => {
		const step = Math.max( window.innerHeight - 100, 200 );
		for ( let y = 0; y < document.documentElement.scrollHeight; y += step ) {
			window.scrollTo( 0, y );
			await new Promise( ( r ) => setTimeout( r, 120 ) );
		}
		window.scrollTo( 0, document.documentElement.scrollHeight );
		await new Promise( ( r ) => setTimeout( r, 200 ) );
		await Promise.all(
			Array.from( document.images )
				.filter( ( img ) => ! img.complete )
				.map(
					( img ) =>
						new Promise( ( r ) => {
							img.addEventListener( 'load', r, { once: true } );
							img.addEventListener( 'error', r, { once: true } );
						} )
				)
		);
		window.scrollTo( 0, 0 );
		await document.fonts.ready;
	} );

const capture = async ( context, url, width, file ) => {
	const page = await context.newPage();
	try {
		await page.setViewportSize( { width, height: VIEWPORT_HEIGHT } );
		const response = await page.goto( url, {
			waitUntil: 'load',
			timeout: NAV_TIMEOUT_MS,
		} );
		const status = response?.status();
		if ( ! status || status >= 400 ) {
			throw new Error( `HTTP ${ status }` );
		}
		await page.evaluate( () => document.fonts.ready );
		// Soft wait: a page that never goes idle (long polling, embeds) still
		// gets its picture.
		await page
			.waitForLoadState( 'networkidle', { timeout: IDLE_TIMEOUT_MS } )
			.catch( () => {} );
		await loadLazyContent( page );
		await page
			.waitForLoadState( 'networkidle', { timeout: IDLE_TIMEOUT_MS } )
			.catch( () => {} );
		await page.screenshot( { path: file, fullPage: true, animations: 'disabled' } );
	} finally {
		await page.close();
	}
};

const main = async () => {
	const { base, list, out, widths } = parseArgs( process.argv.slice( 2 ) );
	const origin = new URL( base ).origin;
	const outDir = resolve( out );
	mkdirSync( outDir, { recursive: true } );

	const paths = readFileSync( list, 'utf8' )
		.split( '\n' )
		.map( ( line ) => line.trim() )
		.filter( ( line ) => line && ! line.startsWith( '#' ) );

	const jobs = paths.flatMap( ( path ) =>
		widths.map( ( width ) => ( { path, width } ) )
	);

	const browser = await chromium.launch();
	const failures = [];
	let done = 0;

	const worker = async () => {
		// One context per worker; a fresh context per job would drop the
		// cookie and cost a browser start each time.
		const context = await browser.newContext( {
			reducedMotion: 'reduce',
			deviceScaleFactor: 1,
		} );
		await context.addCookies( [ consentCookie( origin ) ] );
		await context.route( '**/*', ( route ) => {
			const host = new URL( route.request().url() ).hostname;
			return TRACKER_HOSTS.test( host ) ? route.abort() : route.continue();
		} );
		await context.addInitScript( () => {
			const style = document.createElement( 'style' );
			style.textContent =
				'*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}';
			document.addEventListener( 'DOMContentLoaded', () =>
				document.head.append( style )
			);
		} );

		for ( let job = jobs.shift(); job; job = jobs.shift() ) {
			const { path, width } = job;
			const name = `${ slugFor( path ) }@${ width }.png`;
			try {
				await capture(
					context,
					new URL( path, origin ).toString(),
					width,
					join( outDir, name )
				);
				console.log( `ok   ${ ++done } ${ name }` );
			} catch ( error ) {
				failures.push( `${ path } @${ width }: ${ error.message }` );
				console.error( `FAIL ${ path } @${ width }: ${ error.message }` );
			}
		}
		await context.close();
	};

	const total = jobs.length;
	await Promise.all( Array.from( { length: CONCURRENCY }, worker ) );
	await browser.close();

	console.log( `\n${ done }/${ total } screenshots written to ${ outDir }` );
	if ( failures.length ) {
		console.error( `\n${ failures.length } failed:\n${ failures.join( '\n' ) }` );
		process.exit( 1 );
	}
};

main().catch( ( error ) => {
	console.error( error );
	process.exit( 1 );
} );
