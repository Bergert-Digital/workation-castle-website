<?php
/**
 * Translations for the Pediment form's success panel.
 *
 * Pediment ships no catalog for its 'pediment' text domain, so the panel's
 * default heading and its reset button render in English in all five
 * languages. This maps those two strings to the theme's own catalog, where
 * they are kept as literal __() calls so `wp i18n make-pot` extracts them.
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translate the Pediment form's success-panel strings via the theme catalog.
 *
 * @param string $translation Translated text (the msgid, as Pediment has no catalog).
 * @param string $text        Original text.
 * @return string
 */
function workation_pediment_form_strings( $translation, $text ) {
	switch ( $text ) {
		case 'Message sent':
			return __( 'Message sent', 'workation' );
		case 'Send another message':
			return __( 'Send another message', 'workation' );
	}
	return $translation;
}
add_filter( 'gettext_pediment', 'workation_pediment_form_strings', 10, 2 );
