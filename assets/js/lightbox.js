/**
 * Gallery lightbox — open photos in a modal instead of the browser's
 * raw image view.
 *
 * Each gallery photo renders as `<a href="full-image"><img></a>` (see
 * src/blocks/workation-photo/render.php). This script intercepts clicks on
 * those anchors and shows the image in an overlay with prev/next navigation,
 * keyboard control (Esc to close, arrows to page) and backdrop-click to
 * dismiss. With JS disabled the anchors still open the image directly, so the
 * gallery keeps working without it.
 */
( function () {
	'use strict';

	var LINK_SELECTOR = '.gallery a, .photo-grid .photo';

	var links = [];
	var current = 0;
	var lastFocus = null;
	var openedByKeyboard = false;
	var overlay, imgEl, capEl, prevBtn, nextBtn;

	/** Build the overlay DOM once and wire up its controls. */
	function build() {
		overlay = document.createElement( 'div' );
		overlay.className = 'wc-lightbox';
		overlay.setAttribute( 'role', 'dialog' );
		overlay.setAttribute( 'aria-modal', 'true' );
		overlay.setAttribute( 'aria-label', 'Image viewer' );

		overlay.innerHTML =
			'<button type="button" class="wc-lightbox-close" aria-label="Close">×</button>' +
			'<button type="button" class="wc-lightbox-nav wc-lightbox-prev" aria-label="Previous image">‹</button>' +
			'<figure class="wc-lightbox-stage">' +
			'<img class="wc-lightbox-img" src="" alt="">' +
			'<figcaption class="wc-lightbox-cap"></figcaption>' +
			'</figure>' +
			'<button type="button" class="wc-lightbox-nav wc-lightbox-next" aria-label="Next image">›</button>';

		imgEl = overlay.querySelector( '.wc-lightbox-img' );
		capEl = overlay.querySelector( '.wc-lightbox-cap' );
		prevBtn = overlay.querySelector( '.wc-lightbox-prev' );
		nextBtn = overlay.querySelector( '.wc-lightbox-next' );

		overlay
			.querySelector( '.wc-lightbox-close' )
			.addEventListener( 'click', close );
		prevBtn.addEventListener( 'click', function () {
			show( current - 1 );
		} );
		nextBtn.addEventListener( 'click', function () {
			show( current + 1 );
		} );

		// Click on the backdrop (but not the image or controls) closes.
		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay || e.target.tagName === 'FIGURE' ) {
				close();
			}
		} );

		document.body.appendChild( overlay );
	}

	/** Visible (not filtered-out) links, in document order. */
	function visibleLinks() {
		return links.filter( function ( link ) {
			return ! link.classList.contains( 'is-hidden' )
				&& link.offsetParent !== null;
		} );
	}

	/** Display the image at index i within the visible set (wraps). */
	function show( i ) {
		var vis = visibleLinks();
		if ( ! vis.length ) {
			return;
		}
		var count = vis.length;
		current = ( ( i % count ) + count ) % count;
		var link = vis[ current ];
		var thumb = link.querySelector( 'img' );

		imgEl.src = link.getAttribute( 'href' );
		imgEl.alt = thumb ? thumb.getAttribute( 'alt' ) || '' : '';

		var caption = imgEl.alt;
		capEl.textContent = caption;
		capEl.hidden = '' === caption;

		// Single-photo galleries don't need paging controls.
		var multiple = count > 1;
		prevBtn.hidden = ! multiple;
		nextBtn.hidden = ! multiple;
	}

	/** Open the overlay at the clicked photo. */
	function open( i ) {
		if ( ! overlay ) {
			build();
		}
		lastFocus = document.activeElement;
		show( i );
		document.documentElement.classList.add( 'wc-lightbox-lock' );
		document.addEventListener( 'keydown', onKey );
		// Defer so the hidden base state paints before the fade-in class
		// lands; focus once the overlay is visible (a hidden element can't
		// take focus).
		requestAnimationFrame( function () {
			overlay.classList.add( 'is-open' );
			overlay.querySelector( '.wc-lightbox-close' ).focus();
		} );
	}

	/** Close the overlay and restore the page. */
	function close() {
		overlay.classList.remove( 'is-open' );
		document.documentElement.classList.remove( 'wc-lightbox-lock' );
		document.removeEventListener( 'keydown', onKey );
		// Return focus to the photo only for keyboard users. Restoring it
		// after a mouse click would paint a focus ring on the thumbnail,
		// because the browser treats programmatic focus as :focus-visible.
		// For mouse users, just drop focus out of the closing overlay.
		if ( openedByKeyboard && lastFocus && lastFocus.focus ) {
			lastFocus.focus();
		} else if ( overlay.contains( document.activeElement ) ) {
			document.activeElement.blur();
		}
	}

	function onKey( e ) {
		if ( 'Escape' === e.key ) {
			close();
		} else if ( 'ArrowLeft' === e.key && visibleLinks().length > 1 ) {
			show( current - 1 );
		} else if ( 'ArrowRight' === e.key && visibleLinks().length > 1 ) {
			show( current + 1 );
		}
	}

	function init() {
		links = Array.prototype.slice.call(
			document.querySelectorAll( LINK_SELECTOR )
		);
		if ( ! links.length ) {
			return;
		}

		links.forEach( function ( link ) {
			link.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				// detail 0 => activated via the keyboard (Enter); a real
				// pointer click reports a click count >= 1.
				openedByKeyboard = 0 === e.detail;
				var vis = visibleLinks();
				var idx = vis.indexOf( link );
				open( idx === -1 ? 0 : idx );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
