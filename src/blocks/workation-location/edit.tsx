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
	primaryUrl: string;
};

const ALLOWED = [ 'pediment-child/workation-mode' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[
		'pediment-child/workation-mode',
		{ icon: 'car', title: 'By car', text: 'Free parking on site' },
	],
	[
		'pediment-child/workation-mode',
		{ icon: 'train', title: 'By train', text: 'Via Lugano' },
	],
	[
		'pediment-child/workation-mode',
		{ icon: 'plane', title: 'By plane', text: 'Via Milan Malpensa' },
	],
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
	const blockProps = useBlockProps( { className: 'band band-deep' } );
	const innerProps = useInnerBlocksProps(
		{ className: 'modes' },
		{
			allowedBlocks: ALLOWED,
			template: TEMPLATE,
			templateLock: false,
			renderAppender: () => (
				<ButtonBlockAppender rootClientId={ clientId } />
			),
		}
	);
	return (
		<section { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Map image', 'pediment-child' ) }>
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
										? __( 'Replace map', 'pediment-child' )
										: __( 'Select map', 'pediment-child' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<TextControl
						label={ __( 'Alt text', 'pediment-child' ) }
						value={ attributes.imageAlt }
						onChange={ ( v ) => setAttributes( { imageAlt: v } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Arrival button', 'pediment-child' ) }>
					<TextControl
						label={ __( 'Button URL', 'pediment-child' ) }
						value={ attributes.primaryUrl }
						onChange={ ( v ) => setAttributes( { primaryUrl: v } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div className="sec-head">
				<RichText
					tagName="span"
					className="wc-kicker"
					value={ attributes.eyebrow }
					onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
					placeholder={ __( 'Eyebrow…', 'pediment-child' ) }
				/>
				<RichText
					tagName="h2"
					value={ attributes.headline }
					onChange={ ( v ) => setAttributes( { headline: v } ) }
					placeholder={ __( 'Headline…', 'pediment-child' ) }
				/>
			</div>
			<div className="wc-wrap">
				<div className="loc-grid">
					<div className="loc-map">
						{ attributes.imageUrl && (
							<img
								src={ attributes.imageUrl }
								alt={ attributes.imageAlt }
							/>
						) }
					</div>
					<div className="loc-text">
						<RichText
							tagName="p"
							value={ attributes.lead }
							onChange={ ( v ) => setAttributes( { lead: v } ) }
							placeholder={ __( 'Lead…', 'pediment-child' ) }
						/>
						<div { ...innerProps } />
						<RichText
							tagName="span"
							className="text-link loc-cta"
							value={ attributes.primaryText }
							onChange={ ( v ) =>
								setAttributes( { primaryText: v } )
							}
							placeholder={ __( 'Button…', 'pediment-child' ) }
						/>
					</div>
				</div>
			</div>
		</section>
	);
}
