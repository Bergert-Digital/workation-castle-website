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
	lead: string;
	primaryText: string;
	primaryUrl: string;
};

const ALLOWED = [ 'pediment-child/workation-tile' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[
		'pediment-child/workation-tile',
		{
			title: 'Swim in the lake',
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2022/09/IMG_0531-scaled.jpeg',
			imageAlt: 'Clear lake water near the castle',
		},
	],
	[
		'pediment-child/workation-tile',
		{
			title: 'Forest trails',
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/09/IMG_5944.jpeg',
			imageAlt: 'Forest path near Lago di Piano',
		},
	],
	[
		'pediment-child/workation-tile',
		{
			title: 'Waterfalls',
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/09/IMG_5947.jpeg',
			imageAlt: 'Waterfall near the castle',
		},
	],
	[
		'pediment-child/workation-tile',
		{
			title: 'Mountain views',
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/05/IMG_1511.jpeg',
			imageAlt: 'Mountain view above Lake Como',
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
	const blockProps = useBlockProps( { className: 'band band-cream' } );
	const innerProps = useInnerBlocksProps(
		{ className: 'act-grid' },
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
				<PanelBody
					title={ __( 'Activities button', 'pediment-child' ) }
				>
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
				<RichText
					tagName="p"
					value={ attributes.lead }
					onChange={ ( v ) => setAttributes( { lead: v } ) }
					placeholder={ __( 'Lead…', 'pediment-child' ) }
				/>
			</div>
			<div className="wc-wrap">
				<div { ...innerProps } />
				<div className="act-foot">
					<RichText
						tagName="span"
						className="text-link"
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
