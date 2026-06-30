/**
 * Entrance animations — reveal on scroll.
 *
 * Elements listed in REVEAL_SELECTOR start hidden via CSS (see style.css,
 * gated behind `html.js` + `prefers-reduced-motion: no-preference`). This
 * script watches them with an IntersectionObserver and adds `.is-in` when
 * they scroll into view, which triggers the CSS fade/slide-up transition.
 *
 * The hero is above the fold by definition, so its content is revealed on
 * load instead of waiting for a scroll intersection. The hero is a
 * full-height (100svh), flex-end section, so its lowest items (the
 * "custom offer" line and the chip pills) sit inside the observer's bottom
 * rootMargin dead zone and would otherwise never trigger on load.
 *
 * If IntersectionObserver is unavailable or the visitor prefers reduced
 * motion, the `js` class is removed so nothing is ever hidden.
 */
( function () {
	'use strict';

	// Hero content is above the fold; revealed immediately on load.
	var HERO_SELECTOR = '.hero .wc-wrap > *';

	// Everything else fades/slides up as it scrolls into view.
	var REVEAL_SELECTOR = [
		'.sec-head',
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
		// Reveal the hero on load so its full staggered entrance plays,
		// including items below the observer's trigger line. rAF lets the
		// browser paint the hidden state first so the transition runs.
		var heroEls = document.querySelectorAll( HERO_SELECTOR );
		if ( heroEls.length ) {
			requestAnimationFrame( function () {
				heroEls.forEach( function ( el ) {
					el.classList.add( 'is-in' );
				} );
			} );
		}

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
