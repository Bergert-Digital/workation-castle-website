<?php
/**
 * Photos: custom post type + category taxonomy for the filterable gallery.
 *
 * Each wc_photo post is a single photo; its featured image is the image.
 * Photos are managed in wp-admin after the one-time seed; they render only
 * through the pediment-child/photo-gallery block (no public single pages).
 *
 * @package PedimentChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PEDIMENT_CHILD_PHOTO_CPT' ) ) {
	define( 'PEDIMENT_CHILD_PHOTO_CPT', 'wc_photo' );
}
if ( ! defined( 'PEDIMENT_CHILD_PHOTO_TAX' ) ) {
	define( 'PEDIMENT_CHILD_PHOTO_TAX', 'wc_photo_category' );
}

/**
 * Register the photo CPT and its category taxonomy.
 */
function pediment_child_register_photos() {
	register_post_type(
		PEDIMENT_CHILD_PHOTO_CPT,
		array(
			'labels'              => array(
				'name'          => __( 'Photos', 'pediment-child' ),
				'singular_name' => __( 'Photo', 'pediment-child' ),
				'add_new_item'  => __( 'Add New Photo', 'pediment-child' ),
				'edit_item'     => __( 'Edit Photo', 'pediment-child' ),
				'menu_name'     => __( 'Photos', 'pediment-child' ),
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
			'taxonomies'          => array( PEDIMENT_CHILD_PHOTO_TAX ),
		)
	);

	register_taxonomy(
		PEDIMENT_CHILD_PHOTO_TAX,
		PEDIMENT_CHILD_PHOTO_CPT,
		array(
			'labels'            => array(
				'name'          => __( 'Photo categories', 'pediment-child' ),
				'singular_name' => __( 'Photo category', 'pediment-child' ),
				'menu_name'     => __( 'Categories', 'pediment-child' ),
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
add_action( 'init', 'pediment_child_register_photos' );
