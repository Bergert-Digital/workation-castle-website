<?php
/**
 * Pediment Child content seed.
 *
 * Rebuilds the site's pages from the theme's block patterns so the homepage
 * (and any other seeded page) lives in version control, not only in a database.
 * If the database is reset or a fresh environment is created, re-running the
 * seed restores every page from `patterns/`.
 *
 *   1. Renders each block pattern and upserts a published Page for it.
 *   2. Sets the Home page as the static front page + pretty permalinks.
 *
 * Runnable two ways, both calling the same idempotent core:
 *   - WP-CLI:  `wp pediment-child seed`
 *   - wp-admin: Tools → Seed content (no CLI required)
 *
 * Images are referenced by absolute URL inside the patterns, so there is no
 * Media Library import step — the patterns are fully self-contained.
 *
 * @package PedimentChild
 */

namespace PedimentChild;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Seed {

	/**
	 * Map of page slug => [ 'title', 'pattern_file' ].
	 * post_name values must match header/footer nav URLs exactly.
	 */
	const PAGES = array(
		'home' => array(
			'title'        => 'Home',
			'pattern_file' => 'patterns/home.php',
		),
	);

	/** Register the WP-CLI command. */
	public static function register(): void {
		\WP_CLI::add_command( 'pediment-child seed', array( __CLASS__, 'run_cli' ) );
	}

	/** Register the wp-admin Tools page + its form handler. */
	public static function register_admin(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_page' ) );
		add_action( 'admin_post_pediment_child_seed_run', array( __CLASS__, 'handle_admin_run' ) );
	}

	/** Register the pattern category used by the theme's patterns. */
	public static function register_pattern_category(): void {
		register_block_pattern_category(
			'pediment-child',
			array( 'label' => __( 'Workation Castle', 'pediment-child' ) )
		);
	}

	// -------------------------------------------------------------------------
	// Core (context-agnostic)
	// -------------------------------------------------------------------------

	/**
	 * Run the full seed. Returns a structured result instead of printing, so
	 * both the CLI command and the admin handler can present it.
	 *
	 * @return array{ok:bool,summary:string,log:string[]}
	 */
	public static function seed(): array {
		list( $ids, $page_log ) = self::upsert_pages();
		$log                    = $page_log;

		update_option( 'show_on_front', 'page' );
		if ( ! empty( $ids['home'] ) ) {
			update_option( 'page_on_front', $ids['home'] );
			$log[] = 'Front page set to page ID ' . $ids['home'] . '.';
		}

		if ( '' === get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
			flush_rewrite_rules( true );
			$log[] = 'Permalink structure set to /%postname%/.';
		}

		return array(
			'ok'      => true,
			'summary' => __( 'Pediment Child content seeded.', 'pediment-child' ),
			'log'     => $log,
		);
	}

	// -------------------------------------------------------------------------
	// WP-CLI entry point
	// -------------------------------------------------------------------------

	/** CLI command callback: run the seed and stream the log to WP-CLI. */
	public static function run_cli(): void {
		$result = self::seed();
		foreach ( $result['log'] as $line ) {
			\WP_CLI::log( $line );
		}
		\WP_CLI::success( $result['summary'] );
	}

	// -------------------------------------------------------------------------
	// wp-admin entry point
	// -------------------------------------------------------------------------

	/** Add the "Seed content" page under Tools. */
	public static function add_admin_page(): void {
		add_management_page(
			__( 'Seed content', 'pediment-child' ),
			__( 'Seed content', 'pediment-child' ),
			'manage_options',
			'pediment-child-seed',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/** Render the Tools page: a description, a result notice, and the run button. */
	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$key    = 'pediment_child_seed_result_' . get_current_user_id();
		$result = get_transient( $key );
		if ( false !== $result ) {
			delete_transient( $key );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Seed content', 'pediment-child' ); ?></h1>
			<p style="max-width:640px">
				<?php esc_html_e( 'Rebuilds the site’s pages from the theme’s block patterns and sets Home as the front page. Use this on a fresh install, or after a database reset, to restore the homepage from version control.', 'pediment-child' ); ?>
			</p>
			<p style="max-width:640px">
				<strong><?php esc_html_e( 'Note:', 'pediment-child' ); ?></strong>
				<?php esc_html_e( 'This is idempotent and safe to re-run. Existing pages with those slugs are overwritten with the pattern content.', 'pediment-child' ); ?>
			</p>

			<?php if ( is_array( $result ) ) : ?>
				<div class="notice notice-<?php echo $result['ok'] ? 'success' : 'warning'; ?> is-dismissible">
					<p><strong><?php echo esc_html( $result['summary'] ); ?></strong></p>
					<?php if ( ! empty( $result['log'] ) ) : ?>
						<p>
							<a href="#" onclick="this.nextElementSibling.hidden=!this.nextElementSibling.hidden;return false;"><?php esc_html_e( 'Show details', 'pediment-child' ); ?></a>
							<textarea hidden readonly rows="10" style="width:100%;max-width:640px;font-family:monospace"><?php echo esc_textarea( implode( "\n", $result['log'] ) ); ?></textarea>
						</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'pediment_child_seed_run' ); ?>
				<input type="hidden" name="action" value="pediment_child_seed_run">
				<?php submit_button( __( 'Seed content now', 'pediment-child' ), 'primary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	/** Handle the form POST: verify, run the seed, stash the result, redirect back. */
	public static function handle_admin_run(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'pediment-child' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'pediment_child_seed_run' );

		$result = self::seed();
		set_transient( 'pediment_child_seed_result_' . get_current_user_id(), $result, MINUTE_IN_SECONDS );

		wp_safe_redirect( add_query_arg( 'page', 'pediment-child-seed', admin_url( 'tools.php' ) ) );
		exit;
	}

	// -------------------------------------------------------------------------
	// Page upsert
	// -------------------------------------------------------------------------

	/**
	 * Render each pattern via include and upsert a published Page.
	 *
	 * @return array{0:array<string,int>,1:string[]} [ slug => page ID, log lines ].
	 */
	private static function upsert_pages(): array {
		$ids = array();
		$log = array();

		foreach ( self::PAGES as $slug => $info ) {
			$pattern_path = get_theme_file_path( $info['pattern_file'] );

			if ( ! file_exists( $pattern_path ) ) {
				$log[]        = "  Warning: pattern file missing: {$info['pattern_file']}";
				$ids[ $slug ] = 0;
				continue;
			}

			ob_start();
			// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			include $pattern_path;
			$content = trim( (string) ob_get_clean() );

			$existing = get_page_by_path( $slug );
			$postarr  = array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $info['title'],
				'post_name'    => $slug,
				'post_content' => $content,
			);

			if ( $existing ) {
				$postarr['ID'] = $existing->ID;
				$id            = wp_update_post( $postarr, true );
			} else {
				$id = wp_insert_post( $postarr, true );
			}

			if ( is_wp_error( $id ) ) {
				$log[]        = "  Warning: failed to upsert page '{$slug}': " . $id->get_error_message();
				$ids[ $slug ] = 0;
				continue;
			}

			$verb         = $existing ? 'updated' : 'created';
			$ids[ $slug ] = $id;
			$log[]        = "  {$verb} page: {$slug} (#{$id})";
		}

		return array( $ids, $log );
	}
}

add_action( 'init', array( __NAMESPACE__ . '\\Seed', 'register_pattern_category' ) );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	Seed::register();
}
if ( is_admin() ) {
	Seed::register_admin();
}
