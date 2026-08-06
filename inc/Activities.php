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
