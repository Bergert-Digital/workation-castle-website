import { __ } from '@wordpress/i18n';
import {
	ButtonBlockAppender,
	useBlockProps,
	useInnerBlocksProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';

type Attrs = {
	eyebrow: string;
	headline: string;
	lead: string;
	imageUrl: string;
	imageAlt: string;
	imageId: number;
	primaryText: string;
	bookingUrl: string;
	checkInParam: string;
	checkOutParam: string;
	adultsParam: string;
	childrenAgesParam: string;
	secondaryText: string;
	secondaryUrl: string;
};

const ALLOWED = [ 'pediment-child/workation-chip' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[ 'pediment-child/workation-chip', { title: 'Up to 9 guests' } ],
	[ 'pediment-child/workation-chip', { title: '2 vacation homes' } ],
	[ 'pediment-child/workation-chip', { title: '5 bedrooms' } ],
	[ 'pediment-child/workation-chip', { title: 'Co-working space' } ],
	[ 'pediment-child/workation-chip', { title: 'By a nature reserve' } ],
	[ 'pediment-child/workation-chip', { title: '~1h from Milan Malpensa' } ],
];

export default function Edit( {
	attributes,
	setAttributes,
	clientId,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
	clientId: string;
} ) {
	const blockProps = useBlockProps( { className: 'hero' } );
	const chipProps = useInnerBlocksProps(
		{ className: 'hero-chips' },
		{
			allowedBlocks: ALLOWED,
			template: TEMPLATE,
			templateLock: false,
			orientation: 'horizontal',
			renderAppender: () => (
				<ButtonBlockAppender rootClientId={ clientId } />
			),
		}
	);
	return (
		<section { ...blockProps }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Hero image & links', 'pediment-child' ) }
				>
					{ attributes.imageUrl && (
						<img
							src={ attributes.imageUrl }
							alt=""
							style={ { width: '100%', marginBottom: 8 } }
						/>
					) }
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							value={ attributes.imageId }
							onSelect={ ( m: any ) =>
								setAttributes( {
									imageId: m.id,
									imageUrl: m.url,
									imageAlt: m.alt || '',
								} )
							}
							render={ ( { open }: { open: () => void } ) => (
								<Button variant="secondary" onClick={ open }>
									{ attributes.imageUrl
										? __(
												'Replace image',
												'pediment-child'
										  )
										: __(
												'Select image',
												'pediment-child'
										  ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<TextControl
						label={ __( 'Alt text', 'pediment-child' ) }
						value={ attributes.imageAlt }
						onChange={ ( v ) => setAttributes( { imageAlt: v } ) }
					/>
					<TextControl
						label={ __( 'Secondary URL', 'pediment-child' ) }
						value={ attributes.secondaryUrl }
						onChange={ ( v ) =>
							setAttributes( { secondaryUrl: v } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Booking link', 'pediment-child' ) }
					initialOpen={ false }
				>
					<TextControl
						label={ __( 'Booking base URL', 'pediment-child' ) }
						help={ __(
							'The “Check availability” form submits to this URL with the dates and guests appended as query parameters.',
							'pediment-child'
						) }
						value={ attributes.bookingUrl }
						onChange={ ( v ) => setAttributes( { bookingUrl: v } ) }
					/>
					<TextControl
						label={ __( 'Arrival parameter', 'pediment-child' ) }
						value={ attributes.checkInParam }
						onChange={ ( v ) =>
							setAttributes( { checkInParam: v } )
						}
					/>
					<TextControl
						label={ __( 'Departure parameter', 'pediment-child' ) }
						value={ attributes.checkOutParam }
						onChange={ ( v ) =>
							setAttributes( { checkOutParam: v } )
						}
					/>
					<TextControl
						label={ __( 'Guests parameter', 'pediment-child' ) }
						value={ attributes.adultsParam }
						onChange={ ( v ) =>
							setAttributes( { adultsParam: v } )
						}
					/>
					<TextControl
						label={ __(
							'Children ages parameter',
							'pediment-child'
						) }
						value={ attributes.childrenAgesParam }
						onChange={ ( v ) =>
							setAttributes( { childrenAgesParam: v } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div className="hero-img">
				{ attributes.imageUrl && (
					<img
						src={ attributes.imageUrl }
						alt={ attributes.imageAlt }
					/>
				) }
			</div>
			<div className="hero-grad"></div>
			<div className="hero-content">
				<div className="wc-wrap">
					<RichText
						tagName="span"
						className="eyebrow"
						value={ attributes.eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Eyebrow…', 'pediment-child' ) }
					/>
					<RichText
						tagName="h1"
						value={ attributes.headline }
						onChange={ ( v ) => setAttributes( { headline: v } ) }
						placeholder={ __( 'Headline…', 'pediment-child' ) }
					/>
					<RichText
						tagName="p"
						className="lede"
						value={ attributes.lead }
						onChange={ ( v ) => setAttributes( { lead: v } ) }
						placeholder={ __( 'Lead…', 'pediment-child' ) }
					/>
					<div className="avail">
						<div className="avail-field">
							{ /* eslint-disable-next-line jsx-a11y/label-has-associated-control */ }
							<label htmlFor="preview-arrival">Arrival</label>
							<input id="preview-arrival" type="date" disabled />
						</div>
						<div className="avail-field">
							{ /* eslint-disable-next-line jsx-a11y/label-has-associated-control */ }
							<label htmlFor="preview-departure">Departure</label>
							<input
								id="preview-departure"
								type="date"
								disabled
							/>
						</div>
						<div className="avail-field select-wrap">
							{ /* eslint-disable-next-line jsx-a11y/label-has-associated-control */ }
							<label htmlFor="preview-guests">Guests</label>
							<select id="preview-guests" disabled>
								<option>2 guests</option>
							</select>
						</div>
						<div className="avail-submit">
							<RichText
								tagName="span"
								className="wc-btn wc-btn-yellow"
								value={ attributes.primaryText }
								onChange={ ( v ) =>
									setAttributes( { primaryText: v } )
								}
								placeholder={ __(
									'Button…',
									'pediment-child'
								) }
							/>
						</div>
					</div>
					<div className="hero-secondary">
						<RichText
							tagName="span"
							value={ attributes.secondaryText }
							onChange={ ( v ) =>
								setAttributes( { secondaryText: v } )
							}
							placeholder={ __( 'Secondary…', 'pediment-child' ) }
						/>
					</div>
					<div { ...chipProps } />
				</div>
			</div>
		</section>
	);
}
