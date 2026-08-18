import { __ } from '@wordpress/i18n';
import {
	ButtonBlockAppender,
	useBlockProps,
	useInnerBlocksProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import UrlField from '../shared/url-field';

type Attrs = {
	eyebrow: string;
	headline: string;
	lead: string;
	primaryText: string;
	primaryUrl: string;
};

const ALLOWED = [ 'workation/workation-tile' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[
		'workation/workation-tile',
		{
			title: 'Swim in the lake',
			imageUrl: '',
			imageAlt: 'Clear lake water near the castle',
		},
	],
	[
		'workation/workation-tile',
		{
			title: 'Forest trails',
			imageUrl: '',
			imageAlt: 'Forest path near Lago di Piano',
		},
	],
	[
		'workation/workation-tile',
		{
			title: 'Waterfalls',
			imageUrl: '',
			imageAlt: 'Waterfall near the castle',
		},
	],
	[
		'workation/workation-tile',
		{
			title: 'Mountain views',
			imageUrl: '',
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
				<PanelBody title={ __( 'Activities button', 'workation' ) }>
					<UrlField
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
				<RichText
					tagName="p"
					value={ attributes.lead }
					onChange={ ( v ) => setAttributes( { lead: v } ) }
					placeholder={ __( 'Lead…', 'workation' ) }
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
						placeholder={ __( 'Button…', 'workation' ) }
					/>
				</div>
			</div>
		</section>
	);
}
