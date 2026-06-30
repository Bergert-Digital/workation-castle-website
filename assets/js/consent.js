/**
 * GDPR consent manager.
 *
 * Renders a blocking, four-category consent modal, persists the choice in a
 * first-party cookie, and gates non-essential resources: it restores defused
 * third-party iframes (data-consent-src -> src), boots PostHog, and broadcasts
 * a `wc-consent-change` event so other scripts (e.g. the Leaflet maps) can react.
 *
 * No dependencies; same IIFE / 'use strict' style as the theme's other scripts.
 */
( function () {
	'use strict';

	var CONFIG = window.wcConsentConfig || {};
	var COOKIE = CONFIG.cookieName || 'wc_consent';
	var VERSION = CONFIG.version || 1;
	var DAYS = CONFIG.days || 365;

	var CATEGORIES = [
		{
			id: 'necessary',
			label: 'Necessary',
			desc: 'Required for the site to work. Always on.',
			locked: true,
		},
		{
			id: 'functional',
			label: 'Functional',
			desc: 'External maps and embeds (Komoot, Google Maps).',
			locked: false,
		},
		{
			id: 'analytics',
			label: 'Analytics',
			desc: 'Anonymous usage statistics (PostHog) to improve the site.',
			locked: false,
		},
		{
			id: 'marketing',
			label: 'Marketing',
			desc: 'Personalised content and ad measurement.',
			locked: false,
		},
	];

	var root = document.documentElement;
	var modal = null;
	var lastFocus = null;

	// --- Storage -----------------------------------------------------------

	function readConsent() {
		var match = document.cookie.match(
			new RegExp( '(?:^|; )' + COOKIE + '=([^;]*)' )
		);
		if ( ! match ) {
			return null;
		}
		try {
			var data = JSON.parse( decodeURIComponent( match[ 1 ] ) );
			if ( ! data || data.version !== VERSION ) {
				return null;
			}
			return data;
		} catch ( e ) {
			return null;
		}
	}

	function writeConsent( state ) {
		var data = {
			version: VERSION,
			timestamp: Math.floor( new Date().getTime() / 1000 ),
			functional: !! state.functional,
			analytics: !! state.analytics,
			marketing: !! state.marketing,
		};
		var expires = new Date(
			new Date().getTime() + DAYS * 864e5
		).toUTCString();
		var secure = 'https:' === location.protocol ? '; Secure' : '';
		document.cookie =
			COOKIE +
			'=' +
			encodeURIComponent( JSON.stringify( data ) ) +
			'; Path=/; Max-Age=' +
			DAYS * 86400 +
			'; Expires=' +
			expires +
			'; SameSite=Lax' +
			secure;
		return data;
	}

	// --- Gating ------------------------------------------------------------

	function applyConsent( state ) {
		window.wcConsent = {
			functional: !! state.functional,
			analytics: !! state.analytics,
			marketing: !! state.marketing,
		};

		if ( state.functional ) {
			restoreEmbeds();
		}
		if ( state.analytics ) {
			initAnalytics();
		}

		document.dispatchEvent(
			new CustomEvent( 'wc-consent-change', { detail: window.wcConsent } )
		);
	}

	function restoreEmbeds() {
		var frames = document.querySelectorAll(
			'[data-consent-src][data-consent-category="functional"]'
		);
		Array.prototype.forEach.call( frames, function ( el ) {
			if ( el.getAttribute( 'data-consent-restored' ) ) {
				return;
			}
			el.setAttribute( 'data-consent-restored', '1' );
			el.setAttribute( 'src', el.getAttribute( 'data-consent-src' ) );
			var wrap = el.closest( '.wc-consent-embed' );
			if ( wrap ) {
				var overlay = wrap.querySelector( '.wc-consent-embed__overlay' );
				if ( overlay ) {
					overlay.parentNode.removeChild( overlay );
				}
			}
		} );
	}

	function initAnalytics() {
		if ( window.__wcPosthogLoaded || ! CONFIG.posthogKey ) {
			return;
		}
		window.__wcPosthogLoaded = true;
		// Standard PostHog array-stub snippet (loads array.js, then init).
		!( function ( t, e ) {
			var o, n, p, r;
			e.__SV ||
				( ( window.posthog = e ),
				( e._i = [] ),
				( e.init = function ( i, s, a ) {
					function g( t, e ) {
						var o = e.split( '.' );
						2 == o.length && ( ( t = t[ o[ 0 ] ] ), ( e = o[ 1 ] ) );
						t[ e ] = function () {
							t.push(
								[ e ].concat(
									Array.prototype.slice.call( arguments, 0 )
								)
							);
						};
					}
					( ( p = t.createElement( 'script' ) ).type = 'text/javascript' ),
						( p.async = ! 0 ),
						( p.src = s.api_host + '/static/array.js' ),
						( r = t.getElementsByTagName( 'script' )[ 0 ] ).parentNode.insertBefore(
							p,
							r
						);
					var u = e;
					for (
						void 0 !== a ? ( u = e[ a ] = [] ) : ( a = 'posthog' ),
							u.people = u.people || [],
							u.toString = function ( t ) {
								var e = 'posthog';
								return (
									'posthog' !== a && ( e += '.' + a ),
									t || ( e += ' (stub)' ),
									e
								);
							},
							u.people.toString = function () {
								return u.toString( 1 ) + '.people (stub)';
							},
							o =
								'init capture register register_once register_for_session unregister unregister_for_session getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty createPersonProfile opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing debug'.split(
								' '
							),
							n = 0;
						n < o.length;
						n++
					)
						g( u, o[ n ] );
					e._i.push( [ i, s, a ] );
				} ),
				( e.__SV = 1 ) );
		} )( document, window.posthog || [] );
		window.posthog.init( CONFIG.posthogKey, {
			api_host: CONFIG.posthogHost || 'https://eu.i.posthog.com',
			person_profiles: 'identified_only',
		} );
	}

	// --- UI ----------------------------------------------------------------

	function lockScroll( on ) {
		root.classList.toggle( 'wc-consent-locked', !! on );
	}

	function buildModal() {
		if ( modal ) {
			return modal;
		}
		modal = document.createElement( 'div' );
		modal.className = 'wc-consent-modal';
		modal.setAttribute( 'role', 'dialog' );
		modal.setAttribute( 'aria-modal', 'true' );
		modal.setAttribute( 'aria-labelledby', 'wc-consent-title' );
		modal.hidden = true;

		var rows = CATEGORIES.map( function ( c ) {
			var checked = c.locked ? 'checked disabled' : '';
			return (
				'<label class="wc-consent-row">' +
				'<span class="wc-consent-row__text"><strong>' +
				c.label +
				'</strong><span>' +
				c.desc +
				'</span></span>' +
				'<input type="checkbox" class="wc-consent-toggle" data-category="' +
				c.id +
				'" ' +
				checked +
				'></label>'
			);
		} ).join( '' );

		modal.innerHTML =
			'<div class="wc-consent-modal__backdrop"></div>' +
			'<div class="wc-consent-modal__panel">' +
			'<h2 id="wc-consent-title" class="wc-consent-modal__title">Your privacy</h2>' +
			'<p class="wc-consent-modal__intro">We use cookies and external services. Choose what to allow. You can change this anytime via "Cookie settings" in the footer.</p>' +
			'<div class="wc-consent-modal__detail" hidden>' +
			rows +
			'</div>' +
			'<div class="wc-consent-modal__actions">' +
			'<button type="button" class="wc-consent-modal__reject-all">Reject all</button>' +
			'<button type="button" class="wc-consent-modal__customize">Customize</button>' +
			'<button type="button" class="wc-consent-modal__save" hidden>Save preferences</button>' +
			'<button type="button" class="wc-consent-modal__accept-all">Accept all</button>' +
			'</div>' +
			'</div>';

		document.body.appendChild( modal );
		wireModal();
		return modal;
	}

	function setToggles( state ) {
		var inputs = modal.querySelectorAll( '.wc-consent-toggle' );
		Array.prototype.forEach.call( inputs, function ( input ) {
			var cat = input.getAttribute( 'data-category' );
			if ( 'necessary' === cat ) {
				input.checked = true;
				return;
			}
			input.checked = !! ( state && state[ cat ] );
		} );
	}

	function collectToggles() {
		var state = { functional: false, analytics: false, marketing: false };
		var inputs = modal.querySelectorAll( '.wc-consent-toggle' );
		Array.prototype.forEach.call( inputs, function ( input ) {
			var cat = input.getAttribute( 'data-category' );
			if ( 'necessary' !== cat ) {
				state[ cat ] = input.checked;
			}
		} );
		return state;
	}

	function showDetail( on ) {
		modal.querySelector( '.wc-consent-modal__detail' ).hidden = ! on;
		modal.querySelector( '.wc-consent-modal__save' ).hidden = ! on;
		modal.querySelector( '.wc-consent-modal__customize' ).hidden = on;
	}

	function openModal( opts ) {
		buildModal();
		lastFocus = document.activeElement;
		var existing = readConsent();
		setToggles( existing );
		showDetail( !! ( opts && opts.detail ) );
		modal.hidden = false;
		lockScroll( true );
		var focusables = modal.querySelectorAll(
			'button:not([hidden]), [href], input:not([disabled])'
		);
		var first = Array.prototype.filter.call( focusables, function ( el ) {
			return ! el.closest( '[hidden]' );
		} )[ 0 ];
		if ( first ) {
			first.focus();
		}
	}

	function closeModal() {
		if ( ! modal ) {
			return;
		}
		modal.hidden = true;
		lockScroll( false );
		if ( lastFocus && lastFocus.focus ) {
			lastFocus.focus();
		}
	}

	function finalize( state ) {
		var saved = writeConsent( state );
		applyConsent( saved );
		closeModal();
	}

	function trapFocus( e ) {
		if ( modal.hidden || 'Tab' !== e.key ) {
			return;
		}
		var f = Array.prototype.filter.call(
			modal.querySelectorAll( 'button:not([hidden]), input:not([disabled])' ),
			function ( el ) {
				return ! el.closest( '[hidden]' );
			}
		);
		if ( ! f.length ) {
			return;
		}
		var first = f[ 0 ];
		var last = f[ f.length - 1 ];
		if ( e.shiftKey && document.activeElement === first ) {
			e.preventDefault();
			last.focus();
		} else if ( ! e.shiftKey && document.activeElement === last ) {
			e.preventDefault();
			first.focus();
		}
	}

	function wireModal() {
		modal
			.querySelector( '.wc-consent-modal__accept-all' )
			.addEventListener( 'click', function () {
				finalize( {
					functional: true,
					analytics: true,
					marketing: true,
				} );
			} );

		modal
			.querySelector( '.wc-consent-modal__reject-all' )
			.addEventListener( 'click', function () {
				finalize( {
					functional: false,
					analytics: false,
					marketing: false,
				} );
			} );

		modal
			.querySelector( '.wc-consent-modal__customize' )
			.addEventListener( 'click', function () {
				showDetail( true );
			} );

		modal
			.querySelector( '.wc-consent-modal__save' )
			.addEventListener( 'click', function () {
				finalize( collectToggles() );
			} );

		modal.addEventListener( 'keydown', function ( e ) {
			trapFocus( e );
			// ESC only dismisses when a choice already exists (reopen case).
			if ( 'Escape' === e.key && readConsent() ) {
				closeModal();
			}
		} );
	}

	// --- Public API + per-embed buttons + bootstrap ------------------------

	window.wcConsentOpen = function () {
		openModal( { detail: true } );
	};

	// Delegated handler for the per-embed "Load external content" buttons.
	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest
			? e.target.closest( '.wc-consent-embed__load' )
			: null;
		if ( ! btn ) {
			return;
		}
		var current = readConsent() || {};
		current.functional = true;
		finalize( {
			functional: true,
			analytics: !! current.analytics,
			marketing: !! current.marketing,
		} );
	} );

	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest
			? e.target.closest( '.wc-consent-settings-link' )
			: null;
		if ( link ) {
			e.preventDefault();
			window.wcConsentOpen();
		}
	} );

	function boot() {
		var existing = readConsent();
		if ( existing ) {
			applyConsent( existing );
		} else {
			openModal( { detail: false } );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
