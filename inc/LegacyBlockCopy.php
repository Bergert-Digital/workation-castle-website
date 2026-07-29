<?php
/**
 * Section copy for pages stored before it moved into the pattern markup.
 *
 * This copy used to live in block.json as attribute defaults. Defaults cannot
 * be translated: Gutenberg omits any attribute whose value equals its default
 * when it serializes a block, so the text never reached post_content, and
 * Polylang and DeepL can only translate what post_content actually carries.
 * Every language re-read the same English string straight from the theme.
 *
 * The copy now lives in the patterns, where it is serialized into each page and
 * can be translated. Pages seeded before that change do not carry it, and
 * without the defaults there would be nothing left to render -- an update would
 * silently blank the hero, the section headers and the closing call to action
 * on a live site. This supplies the old text for those pages only.
 *
 * Transitional: once every site has re-seeded (Tools -> Seed content), nothing
 * reaches this map and the whole file can be deleted.
 *
 * @package PedimentChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The section copy that used to be block.json attribute defaults.
 *
 * @return array<string, array<string, string>> Block name => attribute => text.
 */
function pediment_child_legacy_block_copy(): array {
	return array(
		'pediment-child/page-hero'            => array(
			'imageAlt' => 'Aerial view of the castle hamlet above Lago di Piano with the mountains beyond',
		),
		'pediment-child/workation-activities' => array(
			'eyebrow'     => 'When laptops close',
			'headline'    => 'Lakes, trails and mountain air are part of the stay.',
			'lead'        => 'Swim before dinner, walk the nature reserve, chase waterfalls or head into the mountains — without turning the trip into logistics.',
			'primaryText' => 'Browse all activities',
		),
		'pediment-child/workation-audience'   => array(
			'eyebrow'  => 'Three ways to stay',
			'headline' => 'One castle, made for the way you want to be together.',
		),
		'pediment-child/workation-closing'    => array(
			'headline'      => 'Bring your next week of work somewhere memorable.',
			'imageAlt'      => 'Garden terrace at Workation Castle',
			'primaryText'   => 'Check availability',
			'secondaryText' => 'Ask for a custom offer',
			'linkText'      => 'Follow on Instagram',
		),
		'pediment-child/workation-gallery'    => array(
			'eyebrow'     => 'Inside the castle',
			'headline'    => 'Old stone, warm rooms and space to breathe.',
			'primaryText' => 'See more photos',
		),
		'pediment-child/workation-hero'       => array(
			'eyebrow'       => 'Castello di Carlazzo · Northern Italy',
			'headline'      => 'An Italian castle to <span class="hl">work, gather</span> and unwind.',
			'lead'          => 'Centuries-old walls and bright, modern interiors on a hill between Lake Como and Lake Lugano — with a full co-working space built for teams who\'d rather work somewhere extraordinary.',
			'imageAlt'      => 'Aerial view of the castle hamlet above Lago di Piano with the mountains beyond',
			'primaryText'   => 'Check availability',
			'secondaryText' => 'Ask for a custom offer',
		),
		'pediment-child/workation-intro'      => array(
			'eyebrow'  => 'Why here',
			'headline' => 'Centuries-old walls, bright modern interiors',
			'lead'     => 'Set on a small hill beside the Lago di Piano nature reserve, Workation Castle pairs historic stone with light, comfortable rooms. Come to focus, to bring your team together, or simply to slow down — with lakes, mountains and quiet walks right outside the door.',
		),
		'pediment-child/workation-location'   => array(
			'eyebrow'     => 'Location & getting here',
			'headline'    => 'Between two lakes, near the Swiss border.',
			'lead'        => 'In an Italian valley near the Swiss border, between Lake Lugano and Lake Como — with the little Lago di Piano a short walk away. Restaurants, a bakery and shops are within walking distance; most guests leave the car parked all week.',
			'imageAlt'    => 'Map showing the castle\'s location between Lake Lugano and Lake Como',
			'primaryText' => 'Plan your arrival',
		),
		'pediment-child/workation-reviews'    => array(
			'eyebrow'  => 'Guest reviews',
			'headline' => 'People come for focus, family time and the quiet.',
		),
		'pediment-child/workation-spaces'     => array(
			'eyebrow'  => 'The spaces',
			'headline' => 'Room to work, and room to stay.',
			'lead'     => 'Original stone and vaulted ceilings, paired with modern, comfortable interiors — historic character without the heaviness.',
		),
	);
}

/**
 * Supply section copy that a stored page does not carry.
 *
 * Runs on the parsed block, so it sees exactly what post_content holds: an
 * attribute the page carries wins, including one deliberately emptied in the
 * editor to hide that element. Only a wholly absent attribute is treated as
 * pre-existing content and filled in.
 *
 * @param array $parsed_block The block about to be rendered.
 * @return array The block, with any missing legacy copy supplied.
 */
function pediment_child_supply_legacy_block_copy( $parsed_block ) {
	$name = isset( $parsed_block['blockName'] ) ? (string) $parsed_block['blockName'] : '';
	$copy = pediment_child_legacy_block_copy();

	if ( ! isset( $copy[ $name ] ) ) {
		return $parsed_block;
	}

	$attrs = isset( $parsed_block['attrs'] ) ? (array) $parsed_block['attrs'] : array();
	foreach ( $copy[ $name ] as $key => $text ) {
		if ( ! array_key_exists( $key, $attrs ) ) {
			$attrs[ $key ] = $text;
		}
	}
	$parsed_block['attrs'] = $attrs;

	return $parsed_block;
}
add_filter( 'render_block_data', 'pediment_child_supply_legacy_block_copy' );
