<?php
/**
 * GitHub-release auto-updates for the Workation Castle theme.
 *
 * Points Plugin Update Checker at the (private) GitHub repo's releases so theme
 * updates arrive through wp-admin's normal one-click flow (Dashboard → Updates
 * / Appearance → Themes) instead of manual zip uploads. Because the repo is
 * private, both the release lookup and the asset download are authenticated
 * with an access token resolved by UpdateToken (constant → env → stored option).
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

	/**
	 * Release-asset regex: the built zip is `workation-castle-theme.zip`, whose
	 * name is fixed by the release workflow and independent of the installed
	 * theme-folder slug — so it is not derived from get_stylesheet().
	 */
	private const ASSET_REGEX = '/workation-castle-theme\.zip$/';

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

		// Fallback branch for reading the version header if a release is ever absent.
		if ( method_exists( $checker, 'setBranch' ) ) {
			$checker->setBranch( 'main' );
		}

		// Install the built release asset (workation-castle-theme.zip) rather than
		// GitHub's auto-generated "Source code" zip, which has the wrong folder
		// name and ships no vendor/ autoloader.
		$api = $checker->getVcsApi();
		if ( method_exists( $api, 'enableReleaseAssets' ) ) {
			$api->enableReleaseAssets( self::assetPattern() );
		}

		// The releases repo is private, so authenticate the release lookup and the
		// asset download with a token. Precedence: WORKATION_CASTLE_UPDATE_TOKEN
		// constant → env var of the same name → the encrypted option saved in
		// Settings → Pediment Theme → Updates → none. Unset everywhere → no
		// setAuthentication() call → updates simply absent, never a fatal.
		$auth = UpdateToken::resolve();
		if ( '' !== $auth['token'] && method_exists( $checker, 'setAuthentication' ) ) {
			$checker->setAuthentication( $auth['token'] );
		}
	}

	/**
	 * The GitHub repository URL that drives updates.
	 *
	 * Exposed so the settings "Test connection" probe hits the same private repo
	 * the updater installs from.
	 */
	// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- camelCase mirrors the template's ThemeUpdater API so the vendored inc/settings-updates.php calls it unchanged.
	public static function repoUrl(): string {
		return self::REPO_URL;
	}

	/**
	 * The release-asset regex the updater installs.
	 *
	 * Exposed for the settings "Test connection" probe. The asset name is fixed
	 * (`workation-castle-theme.zip`), so the $slug the template's probe passes is
	 * accepted for call-site compatibility but intentionally ignored here.
	 *
	 * @param string $slug Unused; kept to match the template settings call site.
	 */
	// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- camelCase mirrors the template's ThemeUpdater API so the vendored inc/settings-updates.php calls it unchanged.
	public static function assetPattern( string $slug = '' ): string {
		unset( $slug );
		return self::ASSET_REGEX;
	}
}
