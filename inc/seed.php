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
		'home'              => array(
			'title'        => 'Home',
			'pattern_file' => 'patterns/home.php',
		),
		'photos'            => array(
			'title'        => 'Photos',
			'pattern_file' => 'patterns/photos.php',
		),
		'reviews'           => array(
			'title'        => 'Reviews',
			'pattern_file' => 'patterns/reviews.php',
		),
		'activities'        => array(
			'title'        => 'Activities',
			'pattern_file' => 'patterns/activities.php',
		),
		'ways-to-stay'      => array(
			'title'        => 'Ways to Stay',
			'pattern_file' => 'patterns/ways-to-stay.php',
		),
		'team-retreats'     => array(
			'title'        => 'Team retreats',
			'pattern_file' => 'patterns/ways-team-retreats.php',
			'parent'       => 'ways-to-stay',
		),
		'workations'        => array(
			'title'        => 'Workations',
			'pattern_file' => 'patterns/ways-workations.php',
			'parent'       => 'ways-to-stay',
		),
		'family-and-groups' => array(
			'title'        => 'Family & group stays',
			'pattern_file' => 'patterns/ways-family-and-groups.php',
			'parent'       => 'ways-to-stay',
		),
		'guide'             => array(
			'title'        => 'Guide',
			'pattern_file' => 'patterns/guide.php',
		),
		'arrival'           => array(
			'title'        => 'Arrival',
			'pattern_file' => 'patterns/arrival.php',
			'parent'       => 'guide',
		),
		'check-in'          => array(
			'title'        => 'Check-in',
			'pattern_file' => 'patterns/check-in.php',
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

	/** Category slug => label for the photo taxonomy. */
	const PHOTO_TERMS = array(
		'casa-galbiga'  => 'Casa Galbiga',
		'casa-tremezzo' => 'Casa Tremezzo',
		'workspace'     => 'Workspace',
		'garden-castle' => 'Garden & Castle',
		'surroundings'  => 'Surroundings',
	);

	/**
	 * Run the full seed. Returns a structured result instead of printing, so
	 * both the CLI command and the admin handler can present it.
	 *
	 * @return array{ok:bool,summary:string,log:string[]}
	 */
	public static function seed(): array {
		self::seed_photo_terms();
		$photo_log    = self::seed_photos( self::photo_manifest() );
		$activity_log = self::seed_activities( self::activities_manifest() );
		$nav_log      = self::seed_primary_nav();

		list( $ids, $page_log ) = self::upsert_pages();
		$log                    = array_merge( $page_log, $photo_log, $activity_log, $nav_log );

		update_option( 'show_on_front', 'page' );
		if ( ! empty( $ids['home'] ) ) {
			update_option( 'page_on_front', $ids['home'] );
			$log[] = 'Front page set to page ID ' . $ids['home'] . '.';
		}

		global $wp_rewrite;
		if ( '' === get_option( 'permalink_structure' ) ) {
			// set_permalink_structure() updates the option *and* refreshes the
			// in-memory WP_Rewrite (a bare update_option() would leave it
			// holding the stale plain structure it was built with).
			$wp_rewrite->set_permalink_structure( '/%postname%/' );
			$log[] = 'Permalink structure set to /%postname%/.';

			// On a virgin install permalinks start plain (''), and
			// WP_Post_Type::add_rewrite_rules() only registers a CPT's
			// permastruct when is_admin() || permalink_structure is non-empty.
			// Under WP-CLI neither held at `init`, so the wc_activity
			// permastruct was never added — switching to pretty permalinks and
			// flushing can't emit rules that don't exist. Re-register every
			// post type's and taxonomy's rewrite rules now that the structure
			// is pretty, so the flush below actually produces the public
			// /activities/<slug>/ singles instead of 404ing until a later,
			// separate-process flush.
			foreach ( get_post_types( array(), 'objects' ) as $post_type_object ) {
				$post_type_object->add_rewrite_rules();
			}
			foreach ( get_taxonomies( array(), 'objects' ) as $taxonomy_object ) {
				$taxonomy_object->add_rewrite_rules();
			}
		}

		// Always flush so the CPT rewrite rules (the public wc_activity singles
		// at /activities/<slug>/) take effect on existing installs too.
		flush_rewrite_rules( true );
		$log[] = 'Rewrite rules flushed.';

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
	// Photo seed helpers
	// -------------------------------------------------------------------------

	/** Read the version-controlled photo manifest. */
	public static function photo_manifest(): array {
		$path = get_theme_file_path( 'inc/photos-manifest.php' );
		return file_exists( $path ) ? (array) require $path : array();
	}

	/** Read the version-controlled activities manifest. */
	public static function activities_manifest(): array {
		$path = get_theme_file_path( 'inc/activities-manifest.php' );
		return file_exists( $path ) ? (array) require $path : array();
	}

	/**
	 * Sideload + upsert activities from a manifest. Idempotent via the
	 * _wc_activity_source_url meta marker.
	 *
	 * @param array $manifest List of [source_url, slug, title, url, alt, excerpt, order, content].
	 * @return string[] Log lines.
	 */
	public static function seed_activities( array $manifest ): array {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// The manifest is a trusted theme file whose content may include raw
		// HTML (e.g. the Komoot map <iframe>s on the Canyon Tour). KSES would
		// strip that under WP-CLI — no current user means no `unfiltered_html`
		// cap — so disable content filtering for the duration of the insert.
		kses_remove_filters();

		$log = array();
		foreach ( $manifest as $item ) {
			$source = isset( $item['source_url'] ) ? (string) $item['source_url'] : '';
			$url    = isset( $item['url'] ) ? (string) $item['url'] : '';
			$slug   = isset( $item['slug'] ) ? (string) $item['slug'] : '';
			if ( '' === $source || '' === $slug ) {
				continue;
			}

			$existing = get_posts(
				array(
					'post_type'   => PEDIMENT_CHILD_ACTIVITY_CPT,
					'post_status' => 'any',
					'numberposts' => 1,
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_key'    => '_wc_activity_source_url',
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'meta_value'  => $source,
					'fields'      => 'ids',
				)
			);
			if ( $existing ) {
				$log[] = "  skipped (exists): {$slug}";
				continue;
			}

			$content = isset( $item['content'] ) ? (string) $item['content'] : '';

			// Append a Leaflet locator map (castle + destination) when the
			// manifest carries destination coordinates, mirroring the
			// wp-map-block maps on the live activity pages.
			if ( isset( $item['dest_lat'], $item['dest_lng'] ) ) {
				$content .= "\n\n<!-- wp:html -->\n"
					. sprintf(
						'<div class="wc-activity-map" data-lat="%1$s" data-lng="%2$s" data-title="%3$s"></div>',
						esc_attr( (string) $item['dest_lat'] ),
						esc_attr( (string) $item['dest_lng'] ),
						esc_attr( isset( $item['dest_title'] ) ? (string) $item['dest_title'] : '' )
					)
					. "\n<!-- /wp:html -->";
			}

			$post_id = wp_insert_post(
				array(
					'post_type'    => PEDIMENT_CHILD_ACTIVITY_CPT,
					'post_status'  => 'publish',
					'post_title'   => isset( $item['title'] ) ? (string) $item['title'] : $slug,
					'post_name'    => $slug,
					'post_excerpt' => isset( $item['excerpt'] ) ? (string) $item['excerpt'] : '',
					'post_content' => $content,
					'menu_order'   => isset( $item['order'] ) ? (int) $item['order'] : 0,
				),
				true
			);
			if ( is_wp_error( $post_id ) ) {
				$log[] = "  Warning: post failed for {$slug}: " . $post_id->get_error_message();
				continue;
			}

			update_post_meta( $post_id, '_wc_activity_source_url', $source );

			$alt = isset( $item['alt'] ) ? (string) $item['alt'] : '';
			if ( '' !== $url ) {
				$att_id = media_sideload_image( $url, $post_id, $alt, 'id' );
				if ( is_wp_error( $att_id ) ) {
					$log[] = "  Warning: sideload failed for {$url}: " . $att_id->get_error_message();
				} else {
					set_post_thumbnail( $post_id, $att_id );
					if ( '' !== $alt ) {
						update_post_meta( $att_id, '_wp_attachment_image_alt', $alt );
					}
				}
			}

			$log[] = "  created activity: {$post_id} ({$slug})";
		}

		kses_init_filters();

		return $log;
	}

	/**
	 * Seed the editable "Primary" navigation menu, create-if-absent.
	 *
	 * The header's core/navigation block resolves this wp_navigation post by
	 * slug at render time. Create-if-absent so re-seeding never clobbers menu
	 * edits made in the Site Editor.
	 *
	 * @return string[] Log lines.
	 */
	public static function seed_primary_nav(): array {
		$existing = get_posts(
			array(
				'post_type'        => 'wp_navigation',
				'name'             => 'primary',
				'post_status'      => 'any',
				'numberposts'      => 1,
				'suppress_filters' => false,
			)
		);
		if ( ! empty( $existing ) ) {
			return array( 'skipped (exists): wp_navigation "primary"' );
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'wp_navigation',
				'post_title'   => 'Primary',
				'post_name'    => 'primary',
				'post_status'  => 'publish',
				'post_content' => pediment_child_primary_nav_blocks(),
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return array( 'ERROR seeding primary nav: ' . $id->get_error_message() );
		}
		return array( 'created: wp_navigation "primary" (ID ' . $id . ')' );
	}

	/** Ensure the photo category terms exist. Returns slug => term_id. */
	public static function seed_photo_terms(): array {
		$map = array();
		foreach ( self::PHOTO_TERMS as $slug => $label ) {
			$existing = get_term_by( 'slug', $slug, PEDIMENT_CHILD_PHOTO_TAX );
			if ( $existing ) {
				$map[ $slug ] = (int) $existing->term_id;
				continue;
			}
			$res = wp_insert_term( $label, PEDIMENT_CHILD_PHOTO_TAX, array( 'slug' => $slug ) );
			if ( ! is_wp_error( $res ) ) {
				$map[ $slug ] = (int) $res['term_id'];
			}
		}
		return $map;
	}

	/**
	 * Sideload + upsert photos from a manifest. Idempotent via the
	 * _wc_photo_source_url meta marker.
	 *
	 * @param array $manifest List of [url, alt, category, order].
	 * @return string[] Log lines.
	 */
	public static function seed_photos( array $manifest ): array {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$log = array();
		foreach ( $manifest as $item ) {
			$url = isset( $item['url'] ) ? (string) $item['url'] : '';
			if ( '' === $url ) {
				continue;
			}

			$existing = get_posts(
				array(
					'post_type'   => PEDIMENT_CHILD_PHOTO_CPT,
					'post_status' => 'any',
					'numberposts' => 1,
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_key'    => '_wc_photo_source_url',
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'meta_value'  => $url,
					'fields'      => 'ids',
				)
			);
			if ( $existing ) {
				$log[] = "  skipped (exists): {$url}";
				continue;
			}

			$alt   = isset( $item['alt'] ) ? (string) $item['alt'] : '';
			$order = isset( $item['order'] ) ? (int) $item['order'] : 0;

			$post_id = wp_insert_post(
				array(
					'post_type'   => PEDIMENT_CHILD_PHOTO_CPT,
					'post_status' => 'publish',
					'post_title'  => '' !== $alt ? $alt : wp_basename( $url ),
					'menu_order'  => $order,
				),
				true
			);
			if ( is_wp_error( $post_id ) ) {
				$log[] = "  Warning: post failed for {$url}: " . $post_id->get_error_message();
				continue;
			}

			$att_id = media_sideload_image( $url, $post_id, $alt, 'id' );
			if ( is_wp_error( $att_id ) ) {
				$log[] = "  Warning: sideload failed for {$url}: " . $att_id->get_error_message();
				wp_delete_post( $post_id, true );
				continue;
			}
			set_post_thumbnail( $post_id, $att_id );
			update_post_meta( $post_id, '_wc_photo_source_url', $url );
			if ( '' !== $alt ) {
				update_post_meta( $att_id, '_wp_attachment_image_alt', $alt );
			}

			if ( ! empty( $item['category'] ) ) {
				wp_set_object_terms( $post_id, array( (string) $item['category'] ), PEDIMENT_CHILD_PHOTO_TAX );
			}

			$log[] = "  created photo: {$post_id} ({$url})";
		}
		return $log;
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

		// Patterns are trusted theme files and may contain raw HTML (e.g. the
		// map <iframe>s on the Arrival page). KSES would strip that under
		// WP-CLI — no current user means no `unfiltered_html` cap — so disable
		// content filtering for the duration of the upsert.
		kses_remove_filters();

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

			// Optional nesting: a page with a 'parent' slug becomes that page's
			// child (e.g. arrival → /guide/arrival/). The parent is listed
			// earlier in PAGES, so its ID is already resolved.
			$parent_id = ( ! empty( $info['parent'] ) && ! empty( $ids[ $info['parent'] ] ) )
				? (int) $ids[ $info['parent'] ]
				: 0;
			$path      = $parent_id ? "{$info['parent']}/{$slug}" : $slug;

			$existing = get_page_by_path( $path );
			$postarr  = array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $info['title'],
				'post_name'    => $slug,
				'post_parent'  => $parent_id,
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

		kses_init_filters();

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
