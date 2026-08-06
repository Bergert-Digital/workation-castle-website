<?php
/**
 * Pediment seed manifest — Workation Castle.
 *
 * The entry list and its per-language overrides are GENERATED. Do not hand-edit
 * them: fix tools/manifest-from-wxr.mjs and regenerate, so re-running the
 * generator against a fresh export stays a meaningful drift check.
 *
 * The nav is hand-written, because it encodes a structure the export does not
 * contain. It is deliberately not the page tree: `check-in` is a top-level page
 * shown inside the Guide submenu, and `casa-galbiga` is a child page that
 * appears in no menu. Every `entry` item omits `label` on purpose, so each
 * language's menu takes that entry's own translated title.
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'languages' => array(
		'en' => array( 'name' => 'English', 'locale' => 'en_US', 'default' => true ),
		'de' => array( 'name' => 'Deutsch', 'locale' => 'de_DE' ),
		'nl' => array( 'name' => 'Nederlands', 'locale' => 'nl_NL' ),
		'fr' => array( 'name' => 'Français', 'locale' => 'fr_FR' ),
		'it' => array( 'name' => 'Italiano', 'locale' => 'it_IT' ),
	),
	'pages'     => array(
		'home' => array(
			'title'   => 'Home',
			'slug'    => 'home',
			'pattern' => 'workation/home',
			// Hand-added: the generator cannot know which page is the front page.
			'front_page' => true,
			'languages' => array(
				'nl' => array( 'slug' => 'home', 'title' => 'Home - Nederlands' ),
				'fr' => array( 'slug' => 'home', 'title' => 'Home - Français' ),
				'it' => array( 'slug' => 'home', 'title' => 'Home - Italiano' ),
				'de' => array( 'slug' => 'startseite', 'title' => 'Startseite' ),
			),
		),
		'photos' => array(
			'title'   => 'Photos',
			'slug'    => 'photos',
			'pattern' => 'workation/photos',
		),
		'reviews' => array(
			'title'   => 'Reviews',
			'slug'    => 'reviews',
			'pattern' => 'workation/reviews',
		),
		'activities' => array(
			'title'   => 'Activities',
			'slug'    => 'activities',
			'pattern' => 'workation/activities',
		),
		'ways-to-stay' => array(
			'title'   => 'Ways to Stay',
			'slug'    => 'ways-to-stay',
			'pattern' => 'workation/ways-to-stay',
		),
		'team-retreats' => array(
			'title'   => 'Team retreats',
			'slug'    => 'team-retreats',
			'pattern' => 'workation/team-retreats',
			'parent'  => 'ways-to-stay',
		),
		'workations' => array(
			'title'   => 'Workations',
			'slug'    => 'workations',
			'pattern' => 'workation/workations',
			'parent'  => 'ways-to-stay',
		),
		'family-and-groups' => array(
			'title'   => 'Family & group stays',
			'slug'    => 'family-and-groups',
			'pattern' => 'workation/family-and-groups',
			'parent'  => 'ways-to-stay',
		),
		'guide' => array(
			'title'   => 'Guide',
			'slug'    => 'guide',
			'pattern' => 'workation/guide',
		),
		'arrival' => array(
			'title'   => 'Arrival',
			'slug'    => 'arrival',
			'pattern' => 'workation/arrival',
			'parent'  => 'guide',
		),
		'map' => array(
			'title'   => 'Map',
			'slug'    => 'map',
			'pattern' => 'workation/map',
			'parent'  => 'guide',
		),
		'casa-galbiga' => array(
			'title'   => 'Casa Galbiga',
			'slug'    => 'casa-galbiga',
			'pattern' => 'workation/casa-galbiga',
			'parent'  => 'guide',
		),
		'faq' => array(
			'title'   => 'FAQ',
			'slug'    => 'faq',
			'pattern' => 'workation/faq',
			'parent'  => 'guide',
		),
		'check-in' => array(
			'title'   => 'Check-in',
			'slug'    => 'check-in',
			'pattern' => 'workation/check-in',
		),
		'contact-us' => array(
			'title'   => 'Contact',
			'slug'    => 'contact-us',
			'pattern' => 'workation/contact-us',
			'languages' => array(
				'de' => array( 'slug' => 'kontakt', 'title' => 'Kontakt' ),
				'nl' => array( 'slug' => 'contact', 'title' => 'Contact' ),
			),
		),
		'feedback' => array(
			'title'   => 'Feedback',
			'slug'    => 'feedback',
			'pattern' => 'workation/feedback',
		),
		'imprint' => array(
			'title'   => 'Imprint',
			'slug'    => 'imprint',
			'pattern' => 'workation/imprint',
		),
		'privacy-policy' => array(
			'title'   => 'Privacy Policy',
			'slug'    => 'privacy-policy',
			'pattern' => 'workation/privacy-policy',
		),
	),
	'navs'      => array(
		'primary' => array(
			'title' => 'Primary',
			'items' => array(
				array( 'entry' => 'activities' ),
				array( 'entry' => 'photos' ),
				array(
					'entry'    => 'ways-to-stay',
					'children' => array(
						array( 'entry' => 'team-retreats' ),
						array( 'entry' => 'workations' ),
						array( 'entry' => 'family-and-groups' ),
					),
				),
				array(
					'entry'    => 'guide',
					'children' => array(
						array( 'entry' => 'arrival' ),
						array( 'entry' => 'check-in' ),
						array( 'entry' => 'map' ),
						array( 'entry' => 'faq' ),
					),
				),
				array( 'entry' => 'contact-us' ),
			),
		),
	),
);
