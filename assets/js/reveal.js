/**
 * Entrance animations — reveal on scroll.
 *
 * Elements listed in REVEAL_SELECTOR start hidden via CSS (see style.css,
 * gated behind `html.js` + `prefers-reduced-motion: no-preference`). This
 * script watches them with an IntersectionObserver and adds `.is-in` when
 * they scroll into view, which triggers the CSS fade/slide-up transition.
 *
 * If IntersectionObserver is unavailable or the visitor prefers reduced
 * motion, the `js` class is removed so nothing is ever hidden.
 */
( function () {
	'use strict';

	var REVEAL_SELECTOR = [
		'.sec-head',
		'.hero .wc-wrap > *',
		'.intro .wc-wrap > *',
		'.ways-grid > .way',
		'.wc-wrap > .space-row',
		'.modes > .mode',
		'.act-grid > .act',
		'.gallery > .wp-block-pediment-child-workation-photo',
		'.reviews-grid > .review',
		'.gallery-foot',
		'.closing-inner',
	].join( ',' );

	var reduceMotion =
		window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Fallback: ensure all content is visible if we can't animate safely.
	if ( ! ( 'IntersectionObserver' in window ) || reduceMotion ) {
		document.documentElement.classList.remove( 'js' );
		return;
	}

	function init() {
		var els = document.querySelectorAll( REVEAL_SELECTOR );
		if ( ! els.length ) {
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-in' );
						observer.unobserve( entry.target );
					}
				} );
			},
			// Trigger slightly before the element's bottom edge is reached.
			{ rootMargin: '0px 0px -10% 0px', threshold: 0.1 }
		);

		els.forEach( function ( el ) {
			observer.observe( el );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
