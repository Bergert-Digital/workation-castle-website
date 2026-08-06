<?php
/**
 * Client-owned CPT content seeding: the photo library and the activities.
 *
 * Extracted verbatim from the retired inc/seed.php when page seeding moved to
 * the Pediment plugin's manifest engine. The plugin owns pages, navigations and
 * languages; it does not own these two custom post types, whose registrations
 * stay in PHP (step 6b design decision 8) and whose rows are built by
 * sideloading remote images listed in the version-controlled manifests.
 *
 * Both passes are idempotent, keyed on a source-URL meta marker, so re-running
 * costs nothing and never duplicates a row.
 *
 * Runnable two ways, both calling the same core:
 *   - WP-CLI:  `wp pediment-child content`
 *   - wp-admin: Tools → Seed CPT content
 *
 * @package PedimentChild
 */

namespace PedimentChild;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sideloads the photo library and the activities from their theme manifests.
 */
class CptContent {

	/** Category slug => label for the photo taxonomy. */
	const PHOTO_TERMS = array(
		'casa-galbiga'  => 'Casa Galbiga',
		'casa-tremezzo' => 'Casa Tremezzo',
		'workspace'     => 'Workspace',
		'garden-castle' => 'Garden & Castle',
		'surroundings'  => 'Surroundings',
	);

	/** Register the WP-CLI command. */
	public static function register(): void {
		\WP_CLI::add_command( 'pediment-child content', array( __CLASS__, 'run_cli' ) );
	}

	/** Register the wp-admin Tools page + its form handler. */
	public static function register_admin(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_page' ) );
		add_action( 'admin_post_pediment_child_seed_cpt_content', array( __CLASS__, 'handle_admin_run' ) );
	}

	// -------------------------------------------------------------------------
	// Core (context-agnostic)
	// -------------------------------------------------------------------------

	/**
	 * Seed the photo taxonomy, the photo library and the activities.
	 *
	 * @return array{ok:bool,summary:string,log:string[]}
	 */
	public static function seed(): array {
		$log = array( 'Photo categories:' );
		$log = array_merge( $log, array_map( static fn( $slug ) => "  term: {$slug}", array_keys( self::seed_photo_terms() ) ) );

		$log[] = 'Photos:';
		$log = array_merge( $log, self::seed_photos( self::photo_manifest() ) );

		$log[] = 'Activities:';
		$log = array_merge( $log, self::seed_activities( self::activities_manifest() ) );

		return array(
			'ok'      => true,
			'summary' => 'CPT content seeded.',
			'log'     => $log,
		);
	}

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
				$att_id = self::get_or_sideload_attachment_id( $url, $post_id, $alt );
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

			$att_id = self::get_or_sideload_attachment_id( $url, $post_id, $alt );
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

	/**
	 * Import a remote image once, reusing any attachment already imported from
	 * the same URL.
	 *
	 * The photo library, the activities and the pattern images can all name the
	 * same remote file, and the marker meta `_wc_source_url` is what keeps a
	 * second pass from downloading it again.
	 *
	 * @param string $url     Remote image URL.
	 * @param int    $post_id Attachment parent.
	 * @param string $desc    Optional attachment title/description.
	 * @return int|\WP_Error Attachment ID, or WP_Error on failure.
	 */
	public static function get_or_sideload_attachment_id( string $url, int $post_id, string $desc = '' ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$existing = get_posts(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'numberposts' => 1,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_key'    => '_wc_source_url',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_value'  => $url,
				'fields'      => 'ids',
			)
		);
		if ( $existing ) {
			return (int) $existing[0];
		}

		$att_id = media_sideload_image( $url, $post_id, '' !== $desc ? $desc : null, 'id' );
		if ( is_wp_error( $att_id ) ) {
			return $att_id;
		}
		update_post_meta( $att_id, '_wc_source_url', $url );

		return (int) $att_id;
	}

	// -------------------------------------------------------------------------
	// WP-CLI
	// -------------------------------------------------------------------------

	/**
	 * Seed the photo library and the activities.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pediment-child content
	 *
	 * @param array $args       Positional args (unused).
	 * @param array $assoc_args Associative args (unused).
	 */
	public static function run_cli( $args = array(), $assoc_args = array() ): void {
		unset( $args, $assoc_args );

		$result = self::seed();
		foreach ( $result['log'] as $line ) {
			\WP_CLI::log( $line );
		}
		\WP_CLI::success( $result['summary'] );
	}

	// -------------------------------------------------------------------------
	// wp-admin
	// -------------------------------------------------------------------------

	/** Add Tools → Seed CPT content. */
	public static function add_admin_page(): void {
		add_management_page(
			__( 'Seed CPT content', 'pediment-child' ),
			__( 'Seed CPT content', 'pediment-child' ),
			'manage_options',
			'pediment-child-cpt-content',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/** Render the Tools screen. */
	public static function render_admin_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Seed CPT content', 'pediment-child' ); ?></h1>
			<p>
				<?php
				esc_html_e(
					'Imports the photo library and the activities from the theme manifests. Idempotent: rows already imported are skipped, and nothing is overwritten.',
					'pediment-child'
				);
				?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="pediment_child_seed_cpt_content">
				<?php wp_nonce_field( 'pediment_child_seed_cpt_content' ); ?>
				<?php submit_button( __( 'Seed now', 'pediment-child' ) ); ?>
			</form>
		</div>
		<?php
	}

	/** Handle the Tools form submission. */
	public static function handle_admin_run(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'pediment-child' ) );
		}
		check_admin_referer( 'pediment_child_seed_cpt_content' );

		self::seed();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'   => 'pediment-child-cpt-content',
					'seeded' => '1',
				),
				admin_url( 'tools.php' )
			)
		);
		exit;
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	CptContent::register();
}
if ( is_admin() ) {
	CptContent::register_admin();
}
