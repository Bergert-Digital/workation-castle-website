<?php
/**
 * GitHub-release auto-updates for the Workation Castle theme.
 *
 * Points Plugin Update Checker at the public GitHub repo's releases so theme
 * updates arrive through wp-admin's normal one-click flow (Dashboard → Updates
 * / Appearance → Themes) instead of manual zip uploads. The repo is public, so
 * neither the release lookup nor the asset download needs a token.
 *
 * PUC itself is vendored by the Pediment plugin (which loads before themes);
 * register() no-ops when that class is absent, so a site without the plugin
 * simply gets no auto-updates rather than a fatal.
 *
 * @package Workation
 */

declare(strict_types=1);

namespace Workation;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ThemeUpdater {
	/** Public repo whose GitHub Releases drive theme updates. */
	private const REPO_URL = 'https://github.com/Bergert-Digital/workation-castle-website/';

	/**
	 * Release-asset regex. The built zip is `workation.zip` (named after the
	 * theme's Text Domain by the release workflow) and its name is independent
	 * of the installed theme-folder slug, so it is not derived from
	 * get_stylesheet().
	 */
	private const ASSET_REGEX = '/workation\.zip$/';

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

		// Slug must equal the installed theme-folder name so WP matches the update
		// to the active theme; get_stylesheet() is that name by definition, and
		// equals the zip's top directory (both derive from the Text Domain).
		$checker = PucFactory::buildUpdateChecker(
			self::REPO_URL,
			get_stylesheet_directory() . '/style.css',
			get_stylesheet()
		);

		// Fallback branch for reading the version header if a release is ever absent.
		if ( method_exists( $checker, 'setBranch' ) ) {
			$checker->setBranch( 'main' );
		}

		// Install the built release asset (workation.zip) rather than GitHub's
		// auto-generated "Source code" zip, which has the wrong folder name and
		// ships neither vendor/ nor build/ (both gitignored).
		//
		// REQUIRE, not PREFER: PUC's default preference silently falls back to
		// $release->zipball_url when no asset matches the pattern, and a release
		// legitimately has no assets during the minutes between release-please
		// publishing it and the attach-zip job finishing its build. Installing
		// that source zip strips build/blocks — every block silently unregisters
		// and each page renders empty — and strips vendor/, which kills the
		// update checker itself so the site cannot recover on its own. REQUIRE
		// makes PUC report "no update" for that window instead, which is
		// recoverable: the next check picks up the real asset.
		$api = $checker->getVcsApi();
		if ( method_exists( $api, 'enableReleaseAssets' ) ) {
			// Read the constant off the instance so this does not hard-code PUC's
			// vendored vXpY namespace, which changes on every PUC bump.
			$api_class = get_class( $api );
			if ( defined( $api_class . '::REQUIRE_RELEASE_ASSETS' ) ) {
				$api->enableReleaseAssets( self::ASSET_REGEX, constant( $api_class . '::REQUIRE_RELEASE_ASSETS' ) );
			} else {
				$api->enableReleaseAssets( self::ASSET_REGEX );
			}
		}
	}

	/**
	 * The GitHub repository URL that drives updates.
	 */
	public static function repo_url(): string {
		return self::REPO_URL;
	}

	/**
	 * The release-asset regex the updater installs from.
	 */
	public static function asset_pattern(): string {
		return self::ASSET_REGEX;
	}
}
