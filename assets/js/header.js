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

	document.querySelectorAll( '.avail' ).forEach( ( form ) => {
		form.addEventListener( 'submit', ( event ) => {
			event.preventDefault();
		} );
	} );
}() );
