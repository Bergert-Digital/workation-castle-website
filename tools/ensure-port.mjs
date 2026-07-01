#!/usr/bin/env node
/**
 * Gives this workspace its own wp-env port so parallel Conductor workspaces
 * don't all collide on the default 8890/8891.
 *
 * Writes a gitignored `.wp-env.override.json` — which @wordpress/env merges
 * over `.wp-env.json` — with random free ports picked once and then reused on
 * every later boot. The port is stable across restarts on purpose: wp-env
 * stores the resolved siteurl in the database and the e2e `baseURL` is derived
 * from this file, so a port that changed each start would break both.
 *
 * Idempotent: if `.wp-env.override.json` already carries a `port`, it is kept
 * as-is. Delete the file to re-roll the port (e.g. after `wp-env destroy`).
 *
 * Usage:
 *   - CLI:    `node tools/ensure-port.mjs`  (prints the resolved URL)
 *   - import: `import { ensurePort } from './ensure-port.mjs'`
 */

import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import { createServer } from 'node:net';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import process from 'node:process';

const overridePath = join(
	dirname( fileURLToPath( import.meta.url ) ),
	'..',
	'.wp-env.override.json'
);

/**
 * Reserve `count` distinct free TCP ports. All sockets are held open at once so
 * the OS hands back distinct ports, then released together. There is an
 * unavoidable (tiny) gap between release and wp-env binding them — acceptable
 * for a dev-only helper.
 *
 * @param {number} count How many distinct free ports to reserve.
 * @return {Promise<number[]>} The reserved port numbers.
 */
function reserveFreePorts( count ) {
	const open = ( portValue ) =>
		new Promise( ( resolve, reject ) => {
			const server = createServer();
			server.on( 'error', reject );
			server.listen( portValue, '127.0.0.1', () => resolve( server ) );
		} );

	return ( async () => {
		const servers = [];
		for ( let i = 0; i < count; i++ ) {
			// eslint-disable-next-line no-await-in-loop -- must hold each open before opening the next.
			servers.push( await open( 0 ) );
		}
		const ports = servers.map( ( server ) => server.address().port );
		await Promise.all(
			servers.map(
				( server ) =>
					new Promise( ( resolve ) => server.close( resolve ) )
			)
		);
		return ports;
	} )();
}

/**
 * Ensure `.wp-env.override.json` carries a port/testsPort, assigning them on
 * first run. The ports are merged into any existing override (which `env:dev`
 * fills with themes/plugins and may hold secrets), preserving every other key,
 * and written with the same 2-space indent `wp-env-mode.mjs` uses so the two
 * tools don't churn the file against each other.
 *
 * @return {Promise<{ port: number, testsPort: number }>} Resolved ports.
 */
export async function ensurePort() {
	const override = existsSync( overridePath )
		? JSON.parse( readFileSync( overridePath, 'utf8' ) )
		: {};

	if ( override.port ) {
		return { port: override.port, testsPort: override.testsPort };
	}

	const [ port, testsPort ] = await reserveFreePorts( 2 );
	override.port = port;
	override.testsPort = testsPort;
	writeFileSync(
		overridePath,
		JSON.stringify( override, null, 2 ) + '\n'
	);
	return { port, testsPort };
}

// Run directly: resolve the port and print the front-end URL.
if ( process.argv[ 1 ] === fileURLToPath( import.meta.url ) ) {
	ensurePort()
		.then( ( { port } ) => {
			process.stdout.write(
				`wp-env port ${ port } → http://localhost:${ port } (.wp-env.override.json)\n`
			);
		} )
		.catch( ( err ) => {
			process.stderr.write( `${ err.stack || err }\n` );
			process.exit( 1 );
		} );
}
