import { __ } from '@wordpress/i18n';
import {
	ButtonBlockAppender,
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

const ALLOWED = [ 'workation/workation-photo' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[
		'workation/workation-photo',
		{
			imageUrl: '',
			imageAlt: 'Terrace and garden view from the castle',
			variant: 'tall',
		},
	],
	[
		'workation/workation-photo',
		{
			imageUrl: '',
			imageAlt: 'Warm interior detail with lamp',
			variant: 'wide',
		},
	],
	[
		'workation/workation-photo',
		{
			imageUrl: '',
			imageAlt: 'Bright bedroom inside the castle',
		},
	],
	[
		'workation/workation-photo',
		{
			imageUrl: '',
			imageAlt: 'Kitchen and dining area',
		},
	],
	[
		'workation/workation-photo',
		{
			imageUrl: '',
			imageAlt: 'Castle room with modern furnishings',
		},
	],
	[
		'workation/workation-photo',
		{
			imageUrl: '',
			imageAlt: 'Stone stairway inside the castle',
		},
	],
	[
		'workation/workation-photo',
		{
			imageUrl: '',
			imageAlt: 'Castle roofs and surrounding hills',
			variant: 'wide',
		},
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
		{ className: 'gallery' },
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
				<PanelBody title={ __( 'Gallery button', 'workation' ) }>
					<TextControl
						label={ __( 'Button URL', 'workation' ) }
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
					placeholder={ __( 'Eyebrow…', 'workation' ) }
				/>
				<RichText
					tagName="h2"
					value={ attributes.headline }
					onChange={ ( v ) => setAttributes( { headline: v } ) }
					placeholder={ __( 'Headline…', 'workation' ) }
				/>
			</div>
			<div className="wc-wrap">
				<div { ...innerProps } />
				<div className="gallery-foot">
					<RichText
						tagName="span"
						className="text-link"
						value={ attributes.primaryText }
						onChange={ ( v ) =>
							setAttributes( { primaryText: v } )
						}
						placeholder={ __( 'Button…', 'workation' ) }
					/>
				</div>
			</div>
		</section>
	);
}
