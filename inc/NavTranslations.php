<?php
/**
 * Per-language Primary menus.
 *
 * One wp_navigation post per Polylang language, generated from the theme's
 * canonical nav source. Lives apart from inc/PrimaryNav.php, which owns menu
 * lookup and rendering, and apart from inc/seed.php, which would otherwise have
 * to know about Polylang as well as pages, photos and rewrite rules.
 *
 * @package PedimentChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu labels, keyed by the English label they replace.
 *
 * Only labels live here. Menu *structure* stays in
 * pediment_child_primary_nav_blocks(), which is the single source of truth for
 * what the menu contains -- five hardcoded markup blobs would drift the moment
 * somebody edited the English menu, and the drift would stay invisible until a
 * client clicked it.
 *
 * NavTranslationsTest asserts that every label in that source appears here, and
 * that every entry covers the same languages, so editing the nav source without
 * translating the new item fails the build rather than shipping.
 */
const PEDIMENT_CHILD_NAV_LABELS = array(
	'Activities'           => array(
		'de' => 'Aktivitäten',
		'nl' => 'Activiteiten',
		'fr' => 'Activités',
		'it' => 'Attività',
	),
	'Photos'               => array(
		'de' => 'Fotos',
		'nl' => 'Fotogalerij',
		'fr' => 'Photographies',
		'it' => 'Fotografie',
	),
	'Ways to stay'         => array(
		'de' => 'Aufenthaltsarten',
		'nl' => 'Manieren van verblijf',
		'fr' => 'Façons de séjourner',
		'it' => 'Modi di soggiornare',
	),
	'Team retreats'        => array(
		'de' => 'Team-Retreats',
		'nl' => 'Teamretraites',
		'fr' => "Séminaires d'équipe",
		'it' => 'Ritiri aziendali',
	),
	'Workations'           => array(
		'de' => 'Workations',
		'nl' => 'Workations',
		'fr' => 'Workations',
		'it' => 'Workation',
	),
	'Family & group stays' => array(
		'de' => 'Familien & Gruppen',
		'nl' => 'Familie & groepen',
		'fr' => 'Familles & groupes',
		'it' => 'Famiglie e gruppi',
	),
	'Guest Guide'          => array(
		'de' => 'Gästeführer',
		'nl' => 'Gastengids',
		'fr' => 'Guide du séjour',
		'it' => "Guida dell'ospite",
	),
	'How to get here'      => array(
		'de' => 'Anreise',
		'nl' => 'Hoe u ons bereikt',
		'fr' => 'Comment venir',
		'it' => 'Come arrivare',
	),
	'Checking in'          => array(
		'de' => 'Anmeldung',
		'nl' => 'Inchecken',
		'fr' => 'Enregistrement',
		'it' => 'Registrazione',
	),
	'Find your way around' => array(
		'de' => 'Orientierung',
		'nl' => 'Vind uw weg',
		'fr' => "S'orienter",
		'it' => 'Orientarsi',
	),
	'FAQ'                  => array(
		'de' => 'FAQ',
		'nl' => 'FAQ',
		'fr' => 'FAQ',
		'it' => 'FAQ',
	),
	'More'                 => array(
		'de' => 'Mehr',
		'nl' => 'Meer',
		'fr' => 'Plus',
		'it' => 'Altro',
	),
	'Contact'              => array(
		'de' => 'Kontakt',
		'nl' => 'Contact opnemen',
		'fr' => 'Contactez-nous',
		'it' => 'Contatti',
	),
);
