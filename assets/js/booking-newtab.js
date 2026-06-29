/**
 * Open the external booking site in a new tab.
 *
 * The "Check availability" buttons live in several places — the header and
 * footer (static links), the hero search <form>, the closing-CTA blocks on the
 * Ways-to-stay pages and the parent theme's pediment/cta block on the Arrival
 * page. Rather than thread a target attribute through each of those renderers
 * (some of which live in the parent theme), we enhance every link and form that
 * points at the booking host so it opens in a new tab.
 *
 * Progressive enhancement: with JS disabled the links still work, just in the
 * same tab.
 */
( function () {
	'use strict';

	var BOOKING_HOST = 'workationcastle.holiduhost.com';

	function pointsToBooking( url ) {
		if ( ! url ) {
			return false;
		}
		try {
			return new URL( url, window.location.href ).hostname === BOOKING_HOST;
		} catch ( e ) {
			return false;
		}
	}

	function init() {
		document.querySelectorAll( 'a[href]' ).forEach( function ( link ) {
			if ( pointsToBooking( link.getAttribute( 'href' ) ) ) {
				link.target = '_blank';
				link.rel = 'noopener noreferrer';
			}
		} );

		document.querySelectorAll( 'form[action]' ).forEach( function ( form ) {
			if ( pointsToBooking( form.getAttribute( 'action' ) ) ) {
				form.target = '_blank';
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
