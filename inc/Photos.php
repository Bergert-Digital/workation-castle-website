<?php
/**
 * Photos: custom post type + category taxonomy for the filterable gallery.
 *
 * Each wc_photo post is a single photo; its featured image is the image.
 * Photos are managed in wp-admin after the one-time seed; they render only
 * through the workation/photo-gallery block (no public single pages).
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WORKATION_PHOTO_CPT' ) ) {
	define( 'WORKATION_PHOTO_CPT', 'wc_photo' );
}
if ( ! defined( 'WORKATION_PHOTO_TAX' ) ) {
	define( 'WORKATION_PHOTO_TAX', 'wc_photo_category' );
}

/**
 * Register the photo CPT and its category taxonomy.
 */
function workation_register_photos() {
	register_post_type(
		WORKATION_PHOTO_CPT,
		array(
			'labels'              => array(
				'name'          => __( 'Photos', 'workation' ),
				'singular_name' => __( 'Photo', 'workation' ),
				'add_new_item'  => __( 'Add New Photo', 'workation' ),
				'edit_item'     => __( 'Edit Photo', 'workation' ),
				'menu_name'     => __( 'Photos', 'workation' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'menu_icon'           => 'dashicons-format-gallery',
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
			'taxonomies'          => array( WORKATION_PHOTO_TAX ),
		)
	);

	register_taxonomy(
		WORKATION_PHOTO_TAX,
		WORKATION_PHOTO_CPT,
		array(
			'labels'            => array(
				'name'          => __( 'Photo categories', 'workation' ),
				'singular_name' => __( 'Photo category', 'workation' ),
				'menu_name'     => __( 'Categories', 'workation' ),
			),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'workation_register_photos' );
