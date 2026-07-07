/**
 * Estate map — link legend rows to buildings/pins via a shared data-poi id.
 * Progressive enhancement: the static map + legend work without this script.
 *
 * One POI is active at a time. Hover/focus sets a transient `hovered` id;
 * click toggles a persistent `pinned` id. The rendered `.is-active` element is
 * `pinned || hovered`, so hover never stomps a pin and a pin survives leave/blur.
 */
( function () {
	'use strict';

	function init() {
		var maps = document.querySelectorAll( '.estate-map' );
		Array.prototype.forEach.call( maps, function ( map ) {
			if ( map.dataset.estateReady ) {
				return;
			}
			map.dataset.estateReady = '1';

			var pinned = null;
			var hovered = null;

			function render() {
				var active = pinned || hovered;
				var nodes = map.querySelectorAll( '[data-poi]' );
				Array.prototype.forEach.call( nodes, function ( n ) {
					n.classList.toggle(
						'is-active',
						n.getAttribute( 'data-poi' ) === active
					);
				} );
			}

			var rows = map.querySelectorAll( '.estate-map__row' );
			Array.prototype.forEach.call( rows, function ( row ) {
				var id = row.getAttribute( 'data-poi' );
				row.addEventListener( 'pointerenter', function () {
					hovered = id;
					render();
				} );
				function clearHover() {
					if ( hovered === id ) {
						hovered = null;
						render();
					}
				}
				row.addEventListener( 'pointerleave', clearHover );
				row.addEventListener( 'pointercancel', clearHover );
				row.addEventListener( 'blur', clearHover );
				row.addEventListener( 'focus', function () {
					hovered = id;
					render();
				} );
				// Tap/click toggles a persistent pin, independent of hover.
				row.addEventListener( 'click', function () {
					pinned = pinned === id ? null : id;
					render();
				} );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
