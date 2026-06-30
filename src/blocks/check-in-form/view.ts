/**
 * Front-end wizard for the check-in form block.
 *
 * Reads the JSON config emitted by render.php, builds an adaptive multi-step
 * form (counts → one step per guest → one step per house → review), validates
 * each step client-side, and submits to the REST endpoint.
 */

type Option = { value: string; label: string };
type GuestField = {
	key: string;
	label: string;
	type: 'text' | 'date' | 'radio';
	required: boolean;
	options?: Option[];
};
type Config = {
	caps: {
		minGuests: number;
		maxGuests: number;
		minHouses: number;
		maxHouses: number;
	};
	guestFields: GuestField[];
	docTypes: Option[];
	consentText: string;
	strings: Record< string, string >;
	restUrl: string;
	nonce: string;
};

type Guest = Record< string, string >;
type Id = { guest_index: string; doc_type: string; doc_number: string };

function sprintf2( tmpl: string, a: number, b: number ): string {
	return tmpl.replace( '%1$d', String( a ) ).replace( '%2$d', String( b ) );
}

/** Today as YYYY-MM-DD, used as the max for birthdate inputs. */
function todayISO(): string {
	const d = new Date();
	const mm = String( d.getMonth() + 1 ).padStart( 2, '0' );
	const dd = String( d.getDate() ).padStart( 2, '0' );
	return `${ d.getFullYear() }-${ mm }-${ dd }`;
}

/**
 * True for a real calendar date in YYYY-MM-DD form that is not in the future.
 * Mirrors the server's checkdate()+not-future rule so the wizard rejects bad
 * dates per-step instead of failing opaquely at submit.
 * @param v
 */
function isValidPastDate( v: string ): boolean {
	if ( ! /^\d{4}-\d{2}-\d{2}$/.test( v ) ) {
		return false;
	}
	const [ y, m, dd ] = v.split( '-' ).map( Number );
	const d = new Date( y, m - 1, dd );
	if (
		d.getFullYear() !== y ||
		d.getMonth() !== m - 1 ||
		d.getDate() !== dd
	) {
		return false;
	}
	const today = new Date();
	today.setHours( 0, 0, 0, 0 );
	return d.getTime() <= today.getTime();
}

function el< K extends keyof HTMLElementTagNameMap >(
	tag: K,
	attrs: Record< string, string > = {},
	children: ( Node | string )[] = []
): HTMLElementTagNameMap[ K ] {
	const node = document.createElement( tag );
	Object.entries( attrs ).forEach( ( [ k, v ] ) =>
		node.setAttribute( k, v )
	);
	children.forEach( ( c ) =>
		node.appendChild(
			typeof c === 'string' ? document.createTextNode( c ) : c
		)
	);
	return node;
}

const DRAFT_KEY = 'wc-checkin-draft';
const DRAFT_VERSION = 1;
const DRAFT_TTL_MS = 14 * 24 * 60 * 60 * 1000;

type Draft = {
	step: number;
	guestCount: number;
	houseCount: number;
	guests: Guest[];
	ids: Id[];
};

/** Remove the saved draft. Best-effort: a blocked localStorage no-ops. */
function clearDraft(): void {
	try {
		window.localStorage.removeItem( DRAFT_KEY );
	} catch ( e ) {
		// localStorage unavailable — nothing to clear.
	}
}

/**
 * Read and validate the saved draft. Returns null (and removes the entry) when
 * absent, unparseable, the wrong schema version, or older than the 14-day TTL.
 * @param now Current time in ms (Date.now()).
 */
function loadDraft( now: number ): Draft | null {
	try {
		const raw = window.localStorage.getItem( DRAFT_KEY );
		if ( ! raw ) {
			return null;
		}
		const parsed = JSON.parse( raw );
		if (
			! parsed ||
			parsed.v !== DRAFT_VERSION ||
			typeof parsed.savedAt !== 'number' ||
			now - parsed.savedAt * 1000 > DRAFT_TTL_MS
		) {
			clearDraft();
			return null;
		}
		return {
			step: Number( parsed.step ) || 0,
			guestCount: Number( parsed.guestCount ) || 1,
			houseCount: Number( parsed.houseCount ) || 1,
			guests: Array.isArray( parsed.guests ) ? parsed.guests : [],
			ids: Array.isArray( parsed.ids ) ? parsed.ids : [],
		};
	} catch ( e ) {
		// Corrupt/unreadable entry — remove it so it is not re-evaluated
		// on every load.
		clearDraft();
		return null;
	}
}

/**
 * Persist the draft. Stores savedAt as epoch seconds. Best-effort: a full or
 * disabled localStorage (private mode) silently no-ops.
 * @param state Current wizard state (consent is intentionally excluded).
 * @param now   Current time in ms (Date.now()).
 */
function saveDraft( state: Draft, now: number ): void {
	try {
		window.localStorage.setItem(
			DRAFT_KEY,
			JSON.stringify( {
				v: DRAFT_VERSION,
				savedAt: Math.floor( now / 1000 ),
				step: state.step,
				guestCount: state.guestCount,
				houseCount: state.houseCount,
				guests: state.guests,
				ids: state.ids,
			} )
		);
	} catch ( e ) {
		// localStorage full or unavailable — persistence is best-effort.
	}
}

/**
 * Clamp a restored step into the valid range for the current total.
 * @param step       The step number to clamp.
 * @param totalSteps The total number of steps in the wizard.
 */
function clampStep( step: number, totalSteps: number ): number {
	if ( step < 0 ) {
		return 0;
	}
	if ( step > totalSteps - 1 ) {
		return totalSteps - 1;
	}
	return step;
}

class Wizard {
	private cfg: Config;
	private root: HTMLElement;
	private step = 0;
	private guestCount = 1;
	private houseCount = 1;
	private guests: Guest[] = [];
	private ids: Id[] = [];
	private consent = false;
	private justRestored = false;

	constructor( root: HTMLElement, cfg: Config ) {
		this.root = root;
		this.cfg = cfg;
		const draft = loadDraft( Date.now() );
		if ( draft ) {
			this.guestCount = draft.guestCount;
			this.houseCount = draft.houseCount;
			this.guests = draft.guests;
			this.ids = draft.ids;
			this.step = clampStep( draft.step, this.totalSteps() );
			this.justRestored = true;
		}
		this.render();
	}

	private totalSteps(): number {
		// counts + guests + ids + review
		return 1 + this.guestCount + this.houseCount + 1;
	}

	private persist(): void {
		saveDraft(
			{
				step: this.step,
				guestCount: this.guestCount,
				houseCount: this.houseCount,
				guests: this.guests,
				ids: this.ids,
			},
			Date.now()
		);
	}

	/** Discard the saved draft and reset the wizard to a clean step 0. */
	private startOver(): void {
		clearDraft();
		this.step = 0;
		this.guestCount = 1;
		this.houseCount = 1;
		this.guests = [];
		this.ids = [];
		this.consent = false;
		this.justRestored = false;
		this.render();
		this.scrollToTop();
	}

	private renderRestoreBanner(): HTMLElement {
		const s = this.cfg.strings;
		const banner = el(
			'div',
			{ class: 'wc-checkin-restored', role: 'status' },
			[ s.restoredNotice + ' ' ]
		);
		const startOver = el(
			'button',
			{ type: 'button', class: 'wc-btn wc-checkin-startover' },
			[ s.startOver ]
		);
		startOver.addEventListener( 'click', () => this.startOver() );
		banner.appendChild( startOver );
		return banner;
	}

	private render(): void {
		this.root.innerHTML = '';
		const form = el( 'form', {
			class: 'wc-checkin-form',
			novalidate: 'novalidate',
		} );
		form.addEventListener( 'submit', ( e ) => e.preventDefault() );

		// Honeypot.
		const hp = el( 'input', {
			type: 'text',
			name: 'website',
			class: 'wc-checkin-hp',
			tabindex: '-1',
			autocomplete: 'off',
			'aria-hidden': 'true',
		} );
		( hp as HTMLElement ).style.position = 'absolute';
		( hp as HTMLElement ).style.left = '-9999px';
		form.appendChild( hp );

		if ( this.justRestored ) {
			form.appendChild( this.renderRestoreBanner() );
			this.justRestored = false;
		}

		const progress = el( 'p', { class: 'wc-checkin-progress' }, [
			`${ this.step + 1 } / ${ this.totalSteps() }`,
		] );
		form.appendChild( progress );

		form.appendChild( this.renderStep() );
		form.appendChild( this.renderNav() );
		this.root.appendChild( form );
	}

	private renderStep(): HTMLElement {
		if ( this.step === 0 ) {
			return this.renderCounts();
		}
		const guestStart = 1;
		const guestEnd = guestStart + this.guestCount; // exclusive
		if ( this.step >= guestStart && this.step < guestEnd ) {
			return this.renderGuest( this.step - guestStart );
		}
		const idEnd = guestEnd + this.houseCount;
		if ( this.step >= guestEnd && this.step < idEnd ) {
			return this.renderId( this.step - guestEnd );
		}
		return this.renderReview();
	}

	private field(
		labelText: string,
		input: HTMLElement,
		key: string
	): HTMLElement {
		return el( 'div', { class: 'wc-checkin-field', 'data-field': key }, [
			el( 'label', {}, [ labelText, input ] ),
			el( 'span', { class: 'wc-checkin-error', hidden: 'hidden' } ),
		] );
	}

	private renderCounts(): HTMLElement {
		const s = this.cfg.strings;
		const wrap = el( 'div', { class: 'wc-checkin-step' }, [
			el( 'h2', {}, [ s.countsHeading ] ),
		] );
		const g = el( 'input', {
			type: 'number',
			name: 'guest_count',
			min: String( this.cfg.caps.minGuests ),
			max: String( this.cfg.caps.maxGuests ),
			value: String( this.guestCount ),
		} );
		const h = el( 'input', {
			type: 'number',
			name: 'house_count',
			min: String( this.cfg.caps.minHouses ),
			max: String( this.cfg.caps.maxHouses ),
			value: String( this.houseCount ),
		} );
		wrap.appendChild( this.field( s.guestsLabel, g, 'guest_count' ) );
		wrap.appendChild( this.field( s.housesLabel, h, 'house_count' ) );
		return wrap;
	}

	private renderGuest( index: number ): HTMLElement {
		const s = this.cfg.strings;
		const existing = this.guests[ index ] || {};
		const wrap = el( 'div', { class: 'wc-checkin-step' }, [
			el( 'h2', {}, [
				sprintf2( s.guestHeading, index + 1, this.guestCount ),
			] ),
		] );
		this.cfg.guestFields.forEach( ( f ) => {
			if ( f.type === 'radio' && f.options ) {
				const group = el( 'fieldset', { class: 'wc-checkin-radio' }, [
					el( 'legend', {}, [ f.label ] ),
				] );
				f.options.forEach( ( o ) => {
					const id = `g${ index }_${ f.key }_${ o.value }`;
					const input = el( 'input', {
						type: 'radio',
						name: f.key,
						id,
						value: o.value,
					} );
					if ( existing[ f.key ] === o.value ) {
						( input as HTMLInputElement ).checked = true;
					}
					group.appendChild(
						el( 'label', { for: id }, [ input, ' ' + o.label ] )
					);
				} );
				group.appendChild(
					el( 'span', {
						class: 'wc-checkin-error',
						hidden: 'hidden',
					} )
				);
				wrap.appendChild( group );
			} else {
				const attrs: Record< string, string > = {
					type: f.type,
					name: f.key,
					value: existing[ f.key ] || '',
				};
				if ( f.type === 'date' ) {
					attrs.max = todayISO();
				}
				const input = el( 'input', attrs );
				wrap.appendChild( this.field( f.label, input, f.key ) );
			}
		} );
		return wrap;
	}

	/**
	 * Display name for a guest, falling back to "Guest N" if unnamed.
	 * @param index
	 */
	private guestLabel( index: number ): string {
		const g = this.guests[ index ] || {};
		const name = `${ g.first_name || '' } ${ g.last_name || '' }`.trim();
		return (
			name ||
			sprintf2(
				this.cfg.strings.guestHeading,
				index + 1,
				this.guestCount
			)
		);
	}

	private renderId( index: number ): HTMLElement {
		const s = this.cfg.strings;
		const existing = this.ids[ index ] || {
			guest_index: '',
			doc_type: '',
			doc_number: '',
		};
		const wrap = el( 'div', { class: 'wc-checkin-step' }, [
			el( 'h2', {}, [
				sprintf2( s.houseHeading, index + 1, this.houseCount ),
			] ),
			el( 'p', { class: 'wc-checkin-intro' }, [ s.idIntro ] ),
		] );

		// Which entered guest does this document belong to?
		const guestSelect = el( 'select', { name: 'guest_index' } );
		guestSelect.appendChild( el( 'option', { value: '' }, [ '—' ] ) );
		this.guests.forEach( ( _g, gi ) => {
			const opt = el( 'option', { value: String( gi ) }, [
				this.guestLabel( gi ),
			] );
			if ( existing.guest_index === String( gi ) ) {
				( opt as HTMLOptionElement ).selected = true;
			}
			guestSelect.appendChild( opt );
		} );
		wrap.appendChild(
			this.field( s.idGuestLabel, guestSelect, 'guest_index' )
		);

		const select = el( 'select', { name: 'doc_type' } );
		select.appendChild( el( 'option', { value: '' }, [ '—' ] ) );
		this.cfg.docTypes.forEach( ( d ) => {
			const opt = el( 'option', { value: d.value }, [ d.label ] );
			if ( existing.doc_type === d.value ) {
				( opt as HTMLOptionElement ).selected = true;
			}
			select.appendChild( opt );
		} );
		const number = el( 'input', {
			type: 'text',
			name: 'doc_number',
			value: existing.doc_number || '',
		} );
		wrap.appendChild( this.field( s.idTypeLabel, select, 'doc_type' ) );
		wrap.appendChild( this.field( s.idNumberLabel, number, 'doc_number' ) );
		return wrap;
	}

	private renderReview(): HTMLElement {
		const s = this.cfg.strings;
		const wrap = el( 'div', { class: 'wc-checkin-step' }, [
			el( 'h2', {}, [ s.reviewHeading ] ),
		] );
		const gl = el( 'ol', { class: 'wc-checkin-review-guests' } );
		this.guests.forEach( ( g ) => {
			gl.appendChild(
				el( 'li', {}, [
					`${ g.first_name } ${ g.last_name } — ${ g.nationality }`,
				] )
			);
		} );
		wrap.appendChild( gl );
		const il = el( 'ol', { class: 'wc-checkin-review-ids' } );
		this.ids.forEach( ( id ) => {
			const who = this.guestLabel( Number( id.guest_index ) );
			il.appendChild(
				el( 'li', {}, [
					`${ who }: ${ id.doc_type } — ${ id.doc_number }`,
				] )
			);
		} );
		wrap.appendChild( il );

		const consentBox = el( 'input', { type: 'checkbox' } );
		( consentBox as HTMLInputElement ).checked = this.consent;
		consentBox.addEventListener( 'change', () => {
			this.consent = ( consentBox as HTMLInputElement ).checked;
			const btn =
				this.root.querySelector< HTMLButtonElement >(
					'.wc-checkin-submit'
				);
			if ( btn ) {
				btn.disabled = ! this.consent;
			}
		} );
		wrap.appendChild(
			el( 'label', { class: 'wc-checkin-consent' }, [
				consentBox,
				' ' + this.cfg.consentText,
			] )
		);
		return wrap;
	}

	private renderNav(): HTMLElement {
		const s = this.cfg.strings;
		const nav = el( 'div', { class: 'wc-checkin-nav' } );
		if ( this.step > 0 ) {
			const back = el(
				'button',
				{ type: 'button', class: 'wc-btn wc-checkin-back' },
				[ s.back ]
			);
			back.addEventListener( 'click', () => {
				this.step--;
				this.persist();
				this.render();
				this.scrollToTop();
			} );
			nav.appendChild( back );
		}
		const isReview = this.step === this.totalSteps() - 1;
		if ( isReview ) {
			const submit = el(
				'button',
				{
					type: 'button',
					class: 'wc-btn wc-btn-yellow wc-checkin-submit',
				},
				[ s.submit ]
			);
			( submit as HTMLButtonElement ).disabled = ! this.consent;
			submit.addEventListener( 'click', () => this.submit() );
			nav.appendChild( submit );
		} else {
			const next = el(
				'button',
				{
					type: 'button',
					class: 'wc-btn wc-btn-yellow wc-checkin-next',
				},
				[ s.next ]
			);
			next.addEventListener( 'click', () => this.next() );
			nav.appendChild( next );
		}
		return nav;
	}

	private clearErrors(): void {
		this.root
			.querySelectorAll< HTMLElement >( '.wc-checkin-error' )
			.forEach( ( e ) => {
				e.textContent = '';
				e.hidden = true;
			} );
	}

	private showError( name: string, message?: string ): void {
		const fieldEl =
			this.root.querySelector(
				`[data-field="${ name }"] .wc-checkin-error`
			) ||
			this.root.querySelector( '.wc-checkin-radio .wc-checkin-error' );
		if ( fieldEl ) {
			fieldEl.textContent = message || this.cfg.strings.errorRequired;
			( fieldEl as HTMLElement ).hidden = false;
		}
	}

	/** Validate + capture the current step. Returns true if valid. */
	private capture(): boolean {
		this.clearErrors();
		let ok = true;

		if ( this.step === 0 ) {
			const g = this.num( 'guest_count' );
			const h = this.num( 'house_count' );
			const c = this.cfg.caps;
			if ( g < c.minGuests || g > c.maxGuests ) {
				this.showError( 'guest_count' );
				ok = false;
			}
			if ( h < c.minHouses || h > c.maxHouses ) {
				this.showError( 'house_count' );
				ok = false;
			}
			if ( ok ) {
				this.guestCount = g;
				this.houseCount = h;
				this.guests.length = Math.min(
					this.guests.length,
					this.guestCount
				);
				this.ids.length = Math.min( this.ids.length, this.houseCount );
			}
			return ok;
		}

		const guestEnd = 1 + this.guestCount;
		if ( this.step < guestEnd ) {
			const data: Guest = {};
			this.cfg.guestFields.forEach( ( f ) => {
				const val = this.value( f.key );
				if ( f.required && ! val ) {
					this.showError( f.key );
					ok = false;
				} else if (
					f.type === 'date' &&
					val &&
					! isValidPastDate( val )
				) {
					this.showError( f.key, this.cfg.strings.errorBirthdate );
					ok = false;
				}
				data[ f.key ] = val;
			} );
			if ( ok ) {
				this.guests[ this.step - 1 ] = data;
			}
			return ok;
		}

		const idEnd = guestEnd + this.houseCount;
		if ( this.step < idEnd ) {
			const guestIndex = this.value( 'guest_index' );
			const docType = this.value( 'doc_type' );
			const docNumber = this.value( 'doc_number' );
			if ( ! guestIndex ) {
				this.showError( 'guest_index' );
				ok = false;
			}
			if ( ! docType ) {
				this.showError( 'doc_type' );
				ok = false;
			}
			if ( ! docNumber ) {
				this.showError( 'doc_number' );
				ok = false;
			}
			if ( ok ) {
				this.ids[ this.step - guestEnd ] = {
					guest_index: guestIndex,
					doc_type: docType,
					doc_number: docNumber,
				};
			}
			return ok;
		}

		return ok;
	}

	private value( name: string ): string {
		const node = this.root.querySelector<
			HTMLInputElement | HTMLSelectElement
		>( `[name="${ name }"]` );
		if ( node && node.type === 'radio' ) {
			const checked = this.root.querySelector< HTMLInputElement >(
				`[name="${ name }"]:checked`
			);
			return checked ? checked.value : '';
		}
		return node ? node.value.trim() : '';
	}

	private num( name: string ): number {
		return parseInt( this.value( name ), 10 ) || 0;
	}

	private next(): void {
		if ( ! this.capture() ) {
			return;
		}
		this.step++;
		this.persist();
		this.render();
		this.scrollToTop();
	}

	/** Scroll the form back to its top after a step change. */
	private scrollToTop(): void {
		const target = this.root.closest( '.wc-checkin' ) || this.root;
		target.scrollIntoView( { block: 'start' } );
	}

	private async submit(): Promise< void > {
		if ( ! this.capture() || ! this.consent ) {
			return;
		}
		const btn =
			this.root.querySelector< HTMLButtonElement >(
				'.wc-checkin-submit'
			);
		if ( btn ) {
			btn.disabled = true;
		}
		const payload = {
			website: '',
			consent: true,
			counts: { guests: this.guestCount, houses: this.houseCount },
			guests: this.guests,
			ids: this.ids,
		};
		try {
			const res = await fetch( this.cfg.restUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': this.cfg.nonce,
				},
				body: JSON.stringify( payload ),
			} );
			const data = await res.json();
			if ( res.ok && data.ok ) {
				this.done();
				return;
			}
			if (
				data &&
				data.errors &&
				this.handleServerErrors( data.errors )
			) {
				return;
			}
			this.fail();
		} catch ( e ) {
			this.fail();
		}
	}

	/**
	 * Map a server validation error back to its step, jump there, and show the
	 * field message — so a rejected submit tells the guest exactly what to fix
	 * instead of an opaque "something went wrong". Returns false if the errors
	 * can't be mapped (caller falls back to the generic message).
	 * @param errors
	 */
	private handleServerErrors( errors: Record< string, string > ): boolean {
		const keys = Object.keys( errors );
		if ( ! keys.length ) {
			return false;
		}
		const key = keys[ 0 ];
		const message = errors[ key ];

		let targetStep: number;
		let fieldName = '';
		if ( key === 'counts' ) {
			targetStep = 0;
			fieldName = 'guest_count';
		} else if ( key === 'consent' ) {
			targetStep = this.totalSteps() - 1;
		} else {
			const m = key.match( /^(guests|ids)\.(\d+)\.(.+)$/ );
			if ( ! m ) {
				return false;
			}
			const idx = parseInt( m[ 2 ], 10 );
			fieldName = m[ 3 ];
			targetStep =
				m[ 1 ] === 'guests' ? 1 + idx : 1 + this.guestCount + idx;
		}

		this.step = targetStep;
		this.render();
		this.scrollToTop();
		if ( fieldName ) {
			this.showError( fieldName, message );
		} else {
			this.fail();
		}
		return true;
	}

	private done(): void {
		clearDraft();
		this.root.innerHTML = '';
		this.root.appendChild(
			el( 'div', { class: 'wc-checkin-done' }, [
				el( 'p', {}, [ this.cfg.strings.thankYou ] ),
			] )
		);
		this.scrollToTop();
	}

	private fail(): void {
		const btn =
			this.root.querySelector< HTMLButtonElement >(
				'.wc-checkin-submit'
			);
		if ( btn ) {
			btn.disabled = false;
		}
		const existing = this.root.querySelector( '.wc-checkin-fail' );
		if ( existing ) {
			return;
		}
		const nav = this.root.querySelector( '.wc-checkin-nav' );
		const msg = el( 'p', { class: 'wc-checkin-fail' }, [
			this.cfg.strings.errorSubmit,
		] );
		if ( nav && nav.parentNode ) {
			nav.parentNode.insertBefore( msg, nav );
		} else {
			this.root.appendChild( msg );
		}
	}
}

document
	.querySelectorAll< HTMLElement >( '.wc-checkin' )
	.forEach( ( block ) => {
		const app = block.querySelector< HTMLElement >( '[data-checkin-app]' );
		const raw = block.querySelector( '.wc-checkin-config' );
		if ( ! app || ! raw || ! raw.textContent ) {
			return;
		}
		let cfg: Config;
		try {
			cfg = JSON.parse( raw.textContent );
		} catch ( e ) {
			return;
		}
		new Wizard( app, cfg );
	} );
