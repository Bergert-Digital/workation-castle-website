/**
 * Estate map — link legend rows, map buildings and pins via a shared data-poi id.
 * Progressive enhancement: the static map + legend work without this script.
 *
 * Any element carrying data-poi is interactive: legend rows, the SVG building
 * shapes, and the pins. One POI is active at a time. Hover/focus sets a transient
 * `hovered` id; click toggles a persistent `pinned` id. The rendered `.is-active`
 * element is `pinned || hovered`, so hover never stomps a pin and a pin survives
 * leave/blur.
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

			function bind( el ) {
				var id = el.getAttribute( 'data-poi' );
				if ( ! id ) {
					return;
				}
				function setHover() {
					hovered = id;
					render();
				}
				function clearHover() {
					if ( hovered === id ) {
						hovered = null;
						render();
					}
				}
				el.addEventListener( 'pointerenter', setHover );
				el.addEventListener( 'pointerleave', clearHover );
				el.addEventListener( 'pointercancel', clearHover );
				// focus/blur only fire for the focusable legend buttons.
				el.addEventListener( 'focus', setHover );
				el.addEventListener( 'blur', clearHover );
				// Tap/click toggles a persistent pin, independent of hover.
				el.addEventListener( 'click', function () {
					pinned = pinned === id ? null : id;
					render();
				} );
			}

			// Legend rows, plus the map's own buildings and pins.
			var targets = map.querySelectorAll(
				'.estate-map__row, .estate-map__building[data-poi], .estate-map__pin[data-poi]'
			);
			Array.prototype.forEach.call( targets, bind );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
