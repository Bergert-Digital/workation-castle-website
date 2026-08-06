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
	ctaText: string;
	ctaUrl: string;
};

const ALLOWED = [ 'workation/workation-review' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[
		'workation/workation-review',
		{
			text: 'The location is perfect — right between Lake Como and Lake Lugano, with a bonus small lake five minutes from the location. The co-working space exceeds expectations.',
			title: 'Alexander M.',
			role: 'Workation guest',
		},
	],
	[
		'workation/workation-review',
		{
			text: 'Ein toller und sehr entspannter Ort zum Arbeiten oder Urlaub machen. Das Haus und die gemeinsamen Arbeitsräume sind super ausgestattet.',
			title: 'Simone S.',
			role: 'Workation stay',
		},
	],
	[
		'workation/workation-review',
		{
			text: 'Die Atmosphäre des alten Gemäuers, den Ausblick von der Terrasse. Tolles und durchdachtes Konzept, auch für größere Familien, Gruppen und zum Arbeiten.',
			title: 'Manuelle B.',
			role: 'Group stay',
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
		{ className: 'reviews-grid' },
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
				<PanelBody title={ __( 'Call to action', 'workation' ) }>
					<TextControl
						label={ __( 'Button label', 'workation' ) }
						value={ attributes.ctaText }
						onChange={ ( v ) => setAttributes( { ctaText: v } ) }
					/>
					<TextControl
						label={ __( 'Button URL', 'workation' ) }
						value={ attributes.ctaUrl }
						onChange={ ( v ) => setAttributes( { ctaUrl: v } ) }
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
			</div>
			{ attributes.ctaText && (
				<div className="reviews-cta">
					<span className="wc-btn wc-btn-yellow">
						{ attributes.ctaText } <span className="arr">→</span>
					</span>
				</div>
			) }
		</section>
	);
}
