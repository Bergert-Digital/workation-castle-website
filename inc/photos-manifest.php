<?php
/**
 * Baseline photo manifest for the one-time seed.
 *
 * Each entry: url (source image), alt, category (term slug), order (menu_order).
 * Consumed by inc/seed.php; after seeding, photos are managed in wp-admin.
 *
 * @package PedimentChild
 */

return array(
	array(
		'url'      => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2019.jpeg',
		'alt'      => 'Terrace and garden view from the castle',
		'category' => 'workspace-garden-castle-surroundings',
		'order'    => 10,
	),
	// Expanded to the full set in Task 8.
);
