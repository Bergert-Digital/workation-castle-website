/**
 * Photo gallery category filter.
 *
 * Clicking a tab (.photo-tab[data-filter]) shows only the photos whose
 * data-category includes that slug; the "*" tab shows everything. Hidden
 * photos get .is-hidden (CSS fades them out). Progressive enhancement: with
 * no JS, all photos and the plain image links remain available.
 */
( function () {
	'use strict';

	function apply( tabs, photos, filter ) {
		tabs.forEach( function ( tab ) {
			var on = tab.getAttribute( 'data-filter' ) === filter;
			tab.classList.toggle( 'is-active', on );
			tab.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
		} );
		photos.forEach( function ( photo ) {
			var cats = ( photo.getAttribute( 'data-category' ) || '' ).split( ' ' );
			var show = '*' === filter || cats.indexOf( filter ) !== -1;
			photo.classList.toggle( 'is-hidden', ! show );
		} );
	}

	function init() {
		// Deep link: /photos/?filter=<slug> preselects a category on load.
		var initial = new URLSearchParams( window.location.search ).get(
			'filter'
		);
		var groups = document.querySelectorAll( '.photos' );
		groups.forEach( function ( group ) {
			var tabs = Array.prototype.slice.call(
				group.querySelectorAll( '.photo-tab' )
			);
			var photos = Array.prototype.slice.call(
				group.querySelectorAll( '.photo-grid .photo' )
			);
			if ( ! tabs.length || ! photos.length ) {
				return;
			}
			tabs.forEach( function ( tab ) {
				tab.addEventListener( 'click', function () {
					apply( tabs, photos, tab.getAttribute( 'data-filter' ) );
				} );
			} );
			// Only apply the URL filter when a matching tab exists, so a bogus
			// slug never hides every photo.
			var hasFilter =
				initial &&
				tabs.some( function ( tab ) {
					return tab.getAttribute( 'data-filter' ) === initial;
				} );
			if ( hasFilter ) {
				apply( tabs, photos, initial );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
