( function () {
	const header = document.getElementById( 'siteHeader' );

	if ( ! header ) {
		return;
	}

	// Transparent-over-hero header turns solid once the page scrolls. The core
	// Navigation block owns all mobile menu behaviour (hamburger + overlay).
	const updateHeader = () => {
		header.classList.toggle( 'scrolled', window.scrollY > 60 );
	};

	window.addEventListener( 'scroll', updateHeader, { passive: true } );
	updateHeader();
}() );
