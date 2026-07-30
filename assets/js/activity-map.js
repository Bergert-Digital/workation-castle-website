/**
 * Activity locator maps.
 *
 * Renders a Leaflet/OpenStreetMap map for every `.wc-activity-map` container
 * on an activity single page, showing the Workation Castle (fixed marker) and
 * the activity's destination (from the container's data attributes). Mirrors
 * the wp-map-block maps on the live workationcastle.com activity pages.
 */
( function () {
	'use strict';

	var L10N = window.wcActivityMap || {};

	/** Escape a translated string for interpolation into innerHTML. */
	function esc( value ) {
		return String( value )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	// The castle is the same on every activity map.
	var CASTLE = {
		lat: 46.03897378894044,
		lng: 9.149152651631791,
		title: 'Workation Castle',
		icon: 'https://workationcastle.com/wp-content/uploads/2023/08/cropped-Logo.png',
	};

	function init() {
		if ( typeof window.L === 'undefined' ) {
			return;
		}

		var containers = document.querySelectorAll( '.wc-activity-map' );
		Array.prototype.forEach.call( containers, function ( el ) {
			if ( el.dataset.mapReady ) {
				return;
			}
			el.dataset.mapReady = '1';

			var lat = parseFloat( el.dataset.lat );
			var lng = parseFloat( el.dataset.lng );
			if ( isNaN( lat ) || isNaN( lng ) ) {
				return;
			}
			var title = el.dataset.title || '';

			var map = window.L.map( el, { scrollWheelZoom: false } );

			window.L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 19,
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
			} ).addTo( map );

			// Workation Castle — custom shield icon.
			var castleIcon = window.L.icon( {
				iconUrl: CASTLE.icon,
				iconSize: [ 40, 40 ],
				iconAnchor: [ 20, 40 ],
				popupAnchor: [ 0, -36 ],
			} );
			window.L.marker( [ CASTLE.lat, CASTLE.lng ], { icon: castleIcon } )
				.addTo( map )
				.bindPopup( CASTLE.title );

			// Destination — default Leaflet pin, popup links to Google Maps.
			var gmaps = 'https://www.google.com/maps/search/?api=1&query=' + lat + ',' + lng;
			var popup = ( title ? '<strong>' + title + '</strong><br>' : '' ) +
				'<a href="' + gmaps + '" target="_blank" rel="noopener">' +
				esc( L10N.seeOnGoogleMaps || 'See on Google Maps' ) +
				'</a>';
			window.L.marker( [ lat, lng ] ).addTo( map ).bindPopup( popup );

			// Frame both markers so the castle is always visible.
			map.fitBounds(
				[ [ CASTLE.lat, CASTLE.lng ], [ lat, lng ] ],
				{ padding: [ 40, 40 ], maxZoom: 13 }
			);
		} );
	}

	function ready( fn ) {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', fn );
		} else {
			fn();
		}
	}

	function maybeInit() {
		// Only initialize (and request OSM tiles) once Functional consent exists.
		if ( window.wcConsent && window.wcConsent.functional ) {
			init();
		}
	}

	ready( maybeInit );
	// React to a later opt-in via the consent manager.
	document.addEventListener( 'wc-consent-change', maybeInit );
} )();
