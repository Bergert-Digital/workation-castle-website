( function () {
	const header = document.getElementById( 'siteHeader' );

	if ( ! header ) {
		return;
	}

	const updateHeader = () => {
		header.classList.toggle( 'scrolled', window.scrollY > 60 );
	};

	window.addEventListener( 'scroll', updateHeader, { passive: true } );
	updateHeader();

	// Mobile navigation toggle. The hamburger is only visible below the 900px
	// breakpoint (see style.css); the `nav-open` class on the header reveals the
	// nav panel, and `aria-expanded` mirrors the state for assistive tech.
	const toggle = header.querySelector( '.menu-toggle' );
	const nav = header.querySelector( 'nav.main-nav' );

	if ( toggle && nav ) {
		const setOpen = ( open ) => {
			header.classList.toggle( 'nav-open', open );
			toggle.setAttribute( 'aria-expanded', String( open ) );
			toggle.setAttribute( 'aria-label', open ? 'Close menu' : 'Open menu' );
		};

		toggle.addEventListener( 'click', () => {
			setOpen( ! header.classList.contains( 'nav-open' ) );
		} );

		// Tapping any nav link navigates away; collapse first so the panel is
		// closed if the destination is a same-page anchor or the click is the
		// dropdown trigger (which itself is a link).
		nav.addEventListener( 'click', ( event ) => {
			if ( event.target.closest( 'a' ) ) {
				setOpen( false );
			}
		} );

		// Escape closes the open panel.
		document.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' && header.classList.contains( 'nav-open' ) ) {
				setOpen( false );
				toggle.focus();
			}
		} );

		// Resizing up to the desktop layout hides the toggle; clear the open
		// state so the nav isn't stuck in its mobile-panel styling.
		window.addEventListener( 'resize', () => {
			if ( window.innerWidth >= 900 && header.classList.contains( 'nav-open' ) ) {
				setOpen( false );
			}
		}, { passive: true } );
	}
}() );
