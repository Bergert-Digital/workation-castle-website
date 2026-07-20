<?php
/**
 * GitHub-release auto-updates for the Workation Castle theme.
 *
 * Points Plugin Update Checker at the (private) GitHub repo's releases so theme
 * updates arrive through wp-admin's normal one-click flow (Dashboard → Updates
 * / Appearance → Themes) instead of manual zip uploads. Because the repo is
 * private, both the release lookup and the asset download are authenticated
 * with an access token (see github_token()).
 *
 * @package PedimentChild
 */

declare(strict_types=1);

namespace PedimentChild;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ThemeUpdater {
	/** Private repo whose GitHub Releases drive theme updates (needs a token). */
	private const REPO_URL = 'https://github.com/Bergert-Digital/workation-castle-website/';

	/** Name of the wp-config constant / env var holding the repo access token. */
	private const TOKEN_KEY = 'WORKATION_CASTLE_UPDATE_TOKEN';

	/**
	 * Wire the update checker to this repo's GitHub releases.
	 */
	public static function register(): void {
		if ( ! class_exists( PucFactory::class ) ) {
			return;
		}

		// Skip update checks in local/dev environments (wp-env, CI). There is no
		// point hitting the GitHub API on every admin load there, and the
		// synchronous check slows the block editor enough to flake e2e tests.
		// Real client sites default to the 'production' environment type.
		if ( function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type() ) {
			return;
		}

		// get_stylesheet_directory(): the active theme dir — here, the child.
		// Slug must equal the theme folder name (pediment-child-theme) so WP
		// matches the update to the installed theme.
		$checker = PucFactory::buildUpdateChecker(
			self::REPO_URL,
			get_stylesheet_directory() . '/style.css',
			'pediment-child-theme'
		);

		// The releases repo is private, so authenticate the release lookup and the
		// asset download with a token. Without it WordPress cannot see or fetch
		// updates from a private repo, so one-click updates silently do nothing.
		$token = self::github_token();
		if ( '' !== $token && method_exists( $checker, 'setAuthentication' ) ) {
			$checker->setAuthentication( $token );
		}

		// Fallback branch for reading the version header if a release is ever absent.
		if ( method_exists( $checker, 'setBranch' ) ) {
			$checker->setBranch( 'main' );
		}

		// Install the built release asset (workation-castle-theme.zip) rather than
		// GitHub's auto-generated "Source code" zip, which has the wrong folder
		// name and ships no vendor/ autoloader.
		$api = $checker->getVcsApi();
		if ( method_exists( $api, 'enableReleaseAssets' ) ) {
			$api->enableReleaseAssets( '/workation-castle-theme\.zip$/' );
		}
	}

	/**
	 * Access token for the private releases repo, or '' if none is configured.
	 *
	 * Read from the WORKATION_CASTLE_UPDATE_TOKEN constant (define it in
	 * wp-config.php) with an environment variable of the same name as a
	 * fallback. The token only needs read access to the repo's contents. Never
	 * commit a token — keep it in wp-config.php or the server environment.
	 *
	 * @return string
	 */
	private static function github_token(): string {
		if ( defined( self::TOKEN_KEY ) && is_string( constant( self::TOKEN_KEY ) ) ) {
			return trim( (string) constant( self::TOKEN_KEY ) );
		}
		$env = getenv( self::TOKEN_KEY );
		return is_string( $env ) ? trim( $env ) : '';
	}
}
