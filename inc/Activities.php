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
 * @package PedimentChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PEDIMENT_CHILD_ACTIVITY_CPT' ) ) {
	define( 'PEDIMENT_CHILD_ACTIVITY_CPT', 'wc_activity' );
}

/**
 * Register the activity CPT (public, with single pages, no taxonomy).
 */
function pediment_child_register_activities() {
	register_post_type(
		PEDIMENT_CHILD_ACTIVITY_CPT,
		array(
			'labels'             => array(
				'name'          => __( 'Activities', 'pediment-child' ),
				'singular_name' => __( 'Activity', 'pediment-child' ),
				'add_new_item'  => __( 'Add New Activity', 'pediment-child' ),
				'edit_item'     => __( 'Edit Activity', 'pediment-child' ),
				'menu_name'     => __( 'Activities', 'pediment-child' ),
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
add_action( 'init', 'pediment_child_register_activities' );
