<?php
/**
 * Activities: public custom post type for the /activities/ page.
 *
 * Each wc_activity post is one activity with a featured image, a card blurb
 * (excerpt) and a full writeup (content). Singles live at /activities/<slug>/.
 * Content is seeded from inc/activities-manifest.php; after the one-time seed,
 * activities are managed in wp-admin.
 *
 * Unlike the Photos CPT (rewrite=false, no single pages), this CPT is public
 * with browsable singles. The WordPress page with post_name "activities" and
 * the CPT rewrite base "activities" intentionally coexist: the page handles
 * /activities/ (the landing/archive view) while CPT singles resolve to
 * /activities/<slug>/.
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WORKATION_ACTIVITY_CPT' ) ) {
	define( 'WORKATION_ACTIVITY_CPT', 'wc_activity' );
}

/**
 * Register the activity CPT (public, with single pages, no taxonomy).
 */
function workation_register_activities() {
	register_post_type(
		WORKATION_ACTIVITY_CPT,
		array(
			'labels'             => array(
				'name'          => __( 'Activities', 'workation' ),
				'singular_name' => __( 'Activity', 'workation' ),
				'add_new_item'  => __( 'Add New Activity', 'workation' ),
				'edit_item'     => __( 'Edit Activity', 'workation' ),
				'menu_name'     => __( 'Activities', 'workation' ),
			),
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'publicly_queryable' => true,
			'has_archive'        => false,
			'rewrite'            => array( 'slug' => 'activities' ),
			'menu_icon'          => 'dashicons-palmtree',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'workation_register_activities' );

/**
 * URL of the activities landing page in the visitor's language.
 *
 * Under Polylang the four language versions of the page share the "activities"
 * slug, so the file-based single template cannot hardcode /activities/ — that
 * always lands on the default language. Resolve the page and map it to the
 * current language instead.
 *
 * @return string
 */
function workation_activities_page_url() {
	$page = get_page_by_path( 'activities' );
	if ( $page && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $page->ID );
		if ( $translated ) {
			$page = get_post( $translated );
		}
	}
	if ( $page ) {
		return (string) get_permalink( $page );
	}
	return home_url( '/activities/' );
}

/**
 * The "back to activities" anchor shown under an activity single.
 *
 * Lives here rather than in the pattern file so the label is scanned into
 * the POT (i18n:pot excludes patterns/).
 *
 * @return string Escaped anchor markup.
 */
function workation_back_to_activities_link() {
	return sprintf(
		'<a class="text-link" href="%s"><span class="arr">←</span>%s</a>',
		esc_url( workation_activities_page_url() ),
		esc_html__( 'Back to activities', 'workation' )
	);
}
