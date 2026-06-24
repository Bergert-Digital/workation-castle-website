import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	useInnerBlocksProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

type Attrs = {
	eyebrow: string;
	headline: string;
	primaryText: string;
	primaryUrl: string;
};

const ALLOWED = [ 'pediment-child/workation-photo' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[
		'pediment-child/workation-photo',
		{
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2019.jpeg',
			imageAlt: 'Terrace and garden view from the castle',
			variant: 'tall',
		},
	],
	[
		'pediment-child/workation-photo',
		{
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2024/01/Workation_Castle_Lamp.jpeg',
			imageAlt: 'Warm interior detail with lamp',
			variant: 'wide',
		},
	],
	[
		'pediment-child/workation-photo',
		{
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2152.jpeg',
			imageAlt: 'Bright bedroom inside the castle',
		},
	],
	[
		'pediment-child/workation-photo',
		{
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2265.jpeg',
			imageAlt: 'Kitchen and dining area',
		},
	],
	[
		'pediment-child/workation-photo',
		{
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2247.jpeg',
			imageAlt: 'Castle room with modern furnishings',
		},
	],
	[
		'pediment-child/workation-photo',
		{
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2113.jpeg',
			imageAlt: 'Stone stairway inside the castle',
		},
	],
	[
		'pediment-child/workation-photo',
		{
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2024/01/Workation_Castle_Roofs.jpeg',
			imageAlt: 'Castle roofs and surrounding hills',
			variant: 'wide',
		},
	],
];

export default function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const blockProps = useBlockProps( { className: 'band band-deep' } );
	const innerProps = useInnerBlocksProps(
		{ className: 'gallery' },
		{ allowedBlocks: ALLOWED, template: TEMPLATE, templateLock: false }
	);
	return (
		<section { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Gallery button', 'pediment-child' ) }>
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
				<div { ...innerProps } />
				<div className="gallery-foot">
					<RichText
						tagName="span"
						className="wc-btn wc-btn-ghost-dark"
						value={ attributes.primaryText }
						onChange={ ( v ) =>
							setAttributes( { primaryText: v } )
						}
						placeholder={ __( 'Button…', 'pediment-child' ) }
					/>
				</div>
			</div>
		</section>
	);
}
