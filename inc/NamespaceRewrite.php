<?php
/**
 * One-shot rewrite of stored block names from pediment-child/* to workation/*.
 *
 * TEMPORARY. Ships in 1.0.0, is run exactly once from wp-admin during the
 * cutover, and is deleted in the release that follows.
 *
 * Claimed pages keep their live post_content by design — the seeding engine
 * treats a row with a seed key but no hash as edited and never writes to it —
 * so the renamed blocks cannot arrive through a seed. Between activating this
 * theme and running this tool, every block whose name changed renders as
 * "block not found".
 *
 * Writes go through $wpdb, not wp_update_post(): this is a literal
 * substitution over already-valid stored markup, and running it back through
 * KSES, wptexturize and the block-validation filters risks changing bytes
 * nobody asked to change.
 *
 * @package Workation
 */

declare(strict_types=1);

namespace Workation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rewrites this theme's old block namespace in stored post content.
 */
final class NamespaceRewrite {
	/** Post types whose content can carry this theme's blocks. */
	private const POST_TYPES = array( 'page', 'post', 'wc_activity', 'wc_photo', 'wp_template_part', 'wp_block' );

	/** Statuses considered. The trash is deliberately absent. */
	private const STATUSES = array( 'publish', 'draft', 'pending', 'private', 'future' );

	/**
	 * Ordered substitutions. The block-name pair covers openers and closers at
	 * once, because a closing delimiter `<!-- /wp:pediment-child/x -->`
	 * contains the opening needle verbatim.
	 */
	private const REPLACEMENTS = array(
		'wp:pediment-child/'       => 'wp:workation/',
		'wp-block-pediment-child-' => 'wp-block-workation-',
	);

	/**
	 * Count what a rewrite would touch, without writing.
	 *
	 * @return array{posts:int,blocks:int}
	 */
	public static function plan(): array {
		$posts  = 0;
		$blocks = 0;

		foreach ( self::rows() as $row ) {
			$occurrences = substr_count( $row->post_content, 'wp:pediment-child/' );
			if ( 0 === $occurrences && ! str_contains( $row->post_content, 'wp-block-pediment-child-' ) ) {
				continue;
			}
			++$posts;
			$blocks += $occurrences;
		}

		return array(
			'posts'  => $posts,
			'blocks' => $blocks,
		);
	}

	/**
	 * Rewrite every affected row.
	 *
	 * @return int Posts actually rewritten.
	 */
	public static function apply(): int {
		global $wpdb;

		$written = 0;

		foreach ( self::rows() as $row ) {
			$rewritten = strtr( $row->post_content, self::REPLACEMENTS );
			if ( $rewritten === $row->post_content ) {
				continue;
			}

			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- deliberate: see the file docblock.
				$wpdb->posts,
				array( 'post_content' => $rewritten ),
				array( 'ID' => (int) $row->ID )
			);
			clean_post_cache( (int) $row->ID );
			++$written;
		}

		return $written;
	}

	/**
	 * Every candidate row.
	 *
	 * @return object[] Rows of ID and post_content.
	 */
	private static function rows(): array {
		global $wpdb;

		$types    = implode( ',', array_fill( 0, count( self::POST_TYPES ), '%s' ) );
		$statuses = implode( ',', array_fill( 0, count( self::STATUSES ), '%s' ) );

		// The placeholders are built above and interpolated into the SQL, so the
		// sniffs cannot see them where they expect to.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		return (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type IN ({$types}) AND post_status IN ({$statuses})",
				array_merge( self::POST_TYPES, self::STATUSES )
			)
		);
		// phpcs:enable
	}

	/** Register the Tools screen. */
	public static function register_admin(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_post_workation_namespace_rewrite', array( __CLASS__, 'handle' ) );
	}

	/** Add Tools → Rewrite block namespace. */
	public static function add_page(): void {
		add_management_page(
			__( 'Rewrite block namespace', 'workation' ),
			__( 'Rewrite block namespace', 'workation' ),
			'manage_options',
			'workation-namespace-rewrite',
			array( __CLASS__, 'render' )
		);
	}

	/** Render the Tools screen. */
	public static function render(): void {
		$plan = self::plan();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Rewrite block namespace', 'workation' ); ?></h1>
			<p>
				<?php
				printf(
					/* translators: 1: number of posts, 2: number of blocks. */
					esc_html__( '%1$d posts still carry %2$d pediment-child blocks.', 'workation' ),
					(int) $plan['posts'],
					(int) $plan['blocks']
				);
				?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="workation_namespace_rewrite">
				<?php wp_nonce_field( 'workation_namespace_rewrite' ); ?>
				<?php submit_button( __( 'Rewrite now', 'workation' ) ); ?>
			</form>
		</div>
		<?php
	}

	/** Handle the Tools form submission. */
	public static function handle(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'workation' ) );
		}
		check_admin_referer( 'workation_namespace_rewrite' );

		$written = self::apply();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'workation-namespace-rewrite',
					'written' => $written,
				),
				admin_url( 'tools.php' )
			)
		);
		exit;
	}
}
