/**
 * Availability range date picker.
 *
 * Progressive enhancement for the "Check availability" form: replaces the two
 * native date inputs with a single Airbnb-style range field that opens a
 * calendar popover (2 months on desktop, 1 on mobile). The native inputs stay
 * in the DOM (hidden) as the only submitted fields and the no-JS fallback.
 *
 * All display text and month/weekday names come from the localized
 * `wcRangePicker` object (see functions.php / pediment_child_range_picker_l10n),
 * so translation plugins localize the picker via WordPress's locale with no JS
 * changes. With JS disabled the native date inputs work as-is.
 */
( function () {
	'use strict';

	var L = window.wcRangePicker;
	if ( ! L ) {
		return;
	}

	var DESKTOP = window.matchMedia( '(min-width: 700px)' );

	function pad( n ) {
		return ( n < 10 ? '0' : '' ) + n;
	}
	function iso( d ) {
		return d.getFullYear() + '-' + pad( d.getMonth() + 1 ) + '-' + pad( d.getDate() );
	}
	function parseISO( s ) {
		var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( s || '' );
		return m ? new Date( +m[1], +m[2] - 1, +m[3] ) : null;
	}
	function startOfDay( d ) {
		return new Date( d.getFullYear(), d.getMonth(), d.getDate() );
	}
	function firstOfMonth( d ) {
		return new Date( d.getFullYear(), d.getMonth(), 1 );
	}
	function addMonths( d, n ) {
		return new Date( d.getFullYear(), d.getMonth() + n, 1 );
	}
	function sameDay( a, b ) {
		return !! a && !! b && a.getFullYear() === b.getFullYear() &&
			a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
	}
	function shortLabel( d ) {
		return d.getDate() + ' ' + L.monthsShort[ d.getMonth() ];
	}

	function RangePicker( root ) {
		this.root = root;
		this.fallback = root.querySelector( '.wc-rangepicker__fallback' );
		this.trigger = root.querySelector( '.wc-rangepicker__field' );
		this.labelEl = root.querySelector( '.wc-rangepicker__label' );
		this.inEl = root.querySelector( '[data-role="checkin"]' );
		this.outEl = root.querySelector( '[data-role="checkout"]' );

		this.start = parseISO( this.inEl.value );
		this.end = parseISO( this.outEl.value );
		this.today = startOfDay( new Date() );
		this.view = firstOfMonth( this.start || this.today );
		this.pop = null;

		this.trigger.hidden = false;
		this.fallback.hidden = true;

		this.trigger.addEventListener( 'click', this.toggle.bind( this ) );
		document.addEventListener( 'click', this.onDocClick.bind( this ) );
		document.addEventListener( 'keydown', this.onKey.bind( this ) );

		this.renderLabel();
	}

	RangePicker.prototype.monthCount = function () {
		return DESKTOP.matches ? 2 : 1;
	};

	RangePicker.prototype.isOpen = function () {
		return !! this.pop;
	};

	RangePicker.prototype.toggle = function () {
		if ( this.isOpen() ) {
			this.close();
		} else {
			this.openPop();
		}
	};

	RangePicker.prototype.openPop = function () {
		this.trigger.setAttribute( 'aria-expanded', 'true' );
		this.pop = document.createElement( 'div' );
		this.pop.className = 'wc-rangepicker__pop';
		this.pop.setAttribute( 'role', 'dialog' );
		this.pop.setAttribute( 'aria-label', L.i18n.dialogLabel );
		// Append to body so the popover escapes any containing stacking context
		// (e.g. the hero section's z-index creates a context the fixed header
		// would otherwise paint over).
		document.body.appendChild( this.pop );
		this.render();
		this.positionPop();
	};

	RangePicker.prototype.positionPop = function () {
		if ( ! this.pop ) {
			return;
		}
		var rect = this.trigger.getBoundingClientRect();
		var ph = this.pop.offsetHeight;
		var vh = window.innerHeight;
		var top;
		// Prefer opening below; fall back to above if not enough space.
		if ( rect.bottom + 10 + ph <= vh ) {
			top = rect.bottom + 10;
		} else {
			top = Math.max( 4, rect.top - 10 - ph );
		}
		this.pop.style.position = 'fixed';
		this.pop.style.insetBlockStart = top + 'px';
		this.pop.style.insetInlineStart = rect.left + 'px';
	};

	RangePicker.prototype.close = function () {
		this.trigger.setAttribute( 'aria-expanded', 'false' );
		if ( this.pop ) {
			this.pop.parentNode.removeChild( this.pop );
			this.pop = null;
		}
	};

	RangePicker.prototype.onDocClick = function ( e ) {
		// Ignore clicks on nodes that are no longer in the document — this can
		// happen when a day button click triggers render(), which replaces
		// pop.innerHTML, detaching the original click target before this handler
		// fires on the bubbling phase.
		if ( ! document.contains( e.target ) ) {
			return;
		}
		if ( this.isOpen() && ! this.root.contains( e.target ) && ! this.pop.contains( e.target ) ) {
			this.close();
		}
	};

	RangePicker.prototype.onKey = function ( e ) {
		if ( this.isOpen() && e.key === 'Escape' ) {
			this.close();
			this.trigger.focus();
		}
	};

	RangePicker.prototype.pick = function ( day ) {
		if ( ! this.start || ( this.start && this.end ) || day <= this.start ) {
			this.start = day;
			this.end = null;
		} else {
			this.end = day;
		}
		this.sync();
		this.renderLabel();
		if ( this.start && this.end ) {
			this.close();
			this.trigger.focus();
		} else {
			this.render();
		}
	};

	RangePicker.prototype.sync = function () {
		this.inEl.value = this.start ? iso( this.start ) : '';
		this.outEl.value = this.end ? iso( this.end ) : '';
	};

	RangePicker.prototype.renderLabel = function () {
		if ( this.start && this.end ) {
			this.labelEl.textContent = shortLabel( this.start ) + ' – ' + shortLabel( this.end );
		} else if ( this.start ) {
			this.labelEl.textContent = shortLabel( this.start );
		} else {
			this.labelEl.textContent = L.i18n.addDates;
		}
	};

	RangePicker.prototype.render = function () {
		if ( ! this.pop ) {
			return;
		}
		var self = this;
		this.pop.innerHTML = '';

		var nav = document.createElement( 'div' );
		nav.className = 'wc-rangepicker__nav';

		var prev = navButton( '‹', L.i18n.prevMonth );
		prev.className = 'wc-rangepicker__prev';
		prev.addEventListener( 'click', function () {
			self.view = addMonths( self.view, -1 );
			self.render();
		} );

		var next = navButton( '›', L.i18n.nextMonth );
		next.className = 'wc-rangepicker__next';
		next.addEventListener( 'click', function () {
			self.view = addMonths( self.view, 1 );
			self.render();
		} );

		nav.appendChild( prev );
		nav.appendChild( next );
		this.pop.appendChild( nav );

		var grids = document.createElement( 'div' );
		grids.className = 'wc-rangepicker__grids';
		for ( var i = 0; i < this.monthCount(); i++ ) {
			grids.appendChild( this.monthGrid( addMonths( this.view, i ) ) );
		}
		this.pop.appendChild( grids );
		this.positionPop();
	};

	function navButton( glyph, label ) {
		var b = document.createElement( 'button' );
		b.type = 'button';
		b.textContent = glyph;
		if ( label ) {
			b.setAttribute( 'aria-label', label );
		}
		return b;
	}

	RangePicker.prototype.monthGrid = function ( month ) {
		var self = this;
		var wrap = document.createElement( 'div' );
		wrap.className = 'wc-rangepicker__month';

		var cap = document.createElement( 'div' );
		cap.className = 'wc-rangepicker__caption';
		cap.textContent = L.months[ month.getMonth() ] + ' ' + month.getFullYear();
		wrap.appendChild( cap );

		var head = document.createElement( 'div' );
		head.className = 'wc-rangepicker__weekdays';
		for ( var w = 0; w < 7; w++ ) {
			var wd = document.createElement( 'span' );
			wd.textContent = L.weekdaysShort[ ( L.startOfWeek + w ) % 7 ];
			head.appendChild( wd );
		}
		wrap.appendChild( head );

		var grid = document.createElement( 'div' );
		grid.className = 'wc-rangepicker__days';
		grid.setAttribute( 'role', 'grid' );

		var offset = ( new Date( month.getFullYear(), month.getMonth(), 1 ).getDay() - L.startOfWeek + 7 ) % 7;
		var daysIn = new Date( month.getFullYear(), month.getMonth() + 1, 0 ).getDate();

		for ( var b = 0; b < offset; b++ ) {
			var blank = document.createElement( 'span' );
			blank.className = 'wc-rangepicker__blank';
			grid.appendChild( blank );
		}

		for ( var d = 1; d <= daysIn; d++ ) {
			grid.appendChild( this.dayCell( new Date( month.getFullYear(), month.getMonth(), d ) ) );
		}

		wrap.appendChild( grid );
		return wrap;
	};

	RangePicker.prototype.dayCell = function ( date ) {
		var self = this;
		var cell = document.createElement( 'button' );
		cell.type = 'button';
		cell.className = 'wc-rangepicker__day';
		cell.textContent = date.getDate();
		cell.setAttribute(
			'aria-label',
			date.getDate() + ' ' + L.months[ date.getMonth() ] + ' ' + date.getFullYear()
		);

		if ( date < this.today ) {
			cell.disabled = true;
			cell.setAttribute( 'aria-disabled', 'true' );
			return cell;
		}

		if ( sameDay( date, this.start ) ) {
			cell.classList.add( 'is-start' );
			cell.setAttribute( 'aria-pressed', 'true' );
		}
		if ( sameDay( date, this.end ) ) {
			cell.classList.add( 'is-end' );
			cell.setAttribute( 'aria-pressed', 'true' );
		}
		if ( this.start && this.end && date > this.start && date < this.end ) {
			cell.classList.add( 'is-in-range' );
		}

		cell.addEventListener( 'click', function () {
			self.pick( date );
		} );
		return cell;
	};

	function init() {
		var roots = document.querySelectorAll( '.wc-rangepicker' );
		for ( var i = 0; i < roots.length; i++ ) {
			new RangePicker( roots[ i ] );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
