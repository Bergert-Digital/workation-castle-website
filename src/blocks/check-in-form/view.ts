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
type Id = { doc_type: string; doc_number: string };

function sprintf2( tmpl: string, a: number, b: number ): string {
	return tmpl.replace( '%1$d', String( a ) ).replace( '%2$d', String( b ) );
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

class Wizard {
	private cfg: Config;
	private root: HTMLElement;
	private step = 0;
	private guestCount = 1;
	private houseCount = 1;
	private guests: Guest[] = [];
	private ids: Id[] = [];
	private consent = false;

	constructor( root: HTMLElement, cfg: Config ) {
		this.root = root;
		this.cfg = cfg;
		this.render();
	}

	private totalSteps(): number {
		// counts + guests + ids + review
		return 1 + this.guestCount + this.houseCount + 1;
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
				const input = el( 'input', {
					type: f.type,
					name: f.key,
					value: existing[ f.key ] || '',
				} );
				wrap.appendChild( this.field( f.label, input, f.key ) );
			}
		} );
		return wrap;
	}

	private renderId( index: number ): HTMLElement {
		const s = this.cfg.strings;
		const existing = this.ids[ index ] || { doc_type: '', doc_number: '' };
		const wrap = el( 'div', { class: 'wc-checkin-step' }, [
			el( 'h2', {}, [
				sprintf2( s.houseHeading, index + 1, this.houseCount ),
			] ),
		] );
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
			il.appendChild(
				el( 'li', {}, [ `${ id.doc_type } — ${ id.doc_number }` ] )
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
				this.render();
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

	private showError( name: string ): void {
		const fieldEl =
			this.root.querySelector(
				`[data-field="${ name }"] .wc-checkin-error`
			) ||
			this.root.querySelector( '.wc-checkin-radio .wc-checkin-error' );
		if ( fieldEl ) {
			fieldEl.textContent = this.cfg.strings.errorRequired;
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
			const docType = this.value( 'doc_type' );
			const docNumber = this.value( 'doc_number' );
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
		this.render();
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
			this.fail();
		} catch ( e ) {
			this.fail();
		}
	}

	private done(): void {
		this.root.innerHTML = '';
		this.root.appendChild(
			el( 'div', { class: 'wc-checkin-done' }, [
				el( 'p', {}, [ this.cfg.strings.thankYou ] ),
			] )
		);
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
