import { __ } from '@wordpress/i18n';
import {
	ButtonBlockAppender,
	useBlockProps,
	useInnerBlocksProps,
	RichText,
} from '@wordpress/block-editor';

type Attrs = { eyebrow: string; headline: string };

const ALLOWED = [ 'pediment-child/workation-review' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[
		'pediment-child/workation-review',
		{
			text: 'The location is perfect — right between Lake Como and Lake Lugano, with a bonus small lake five minutes from the location. The co-working space exceeds expectations.',
			title: 'Alexander M.',
			role: 'Workation guest',
		},
	],
	[
		'pediment-child/workation-review',
		{
			text: 'Ein toller und sehr entspannter Ort zum Arbeiten oder Urlaub machen. Das Haus und die gemeinsamen Arbeitsräume sind super ausgestattet.',
			title: 'Simone S.',
			role: 'Workation stay',
		},
	],
	[
		'pediment-child/workation-review',
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
			</div>
		</section>
	);
}
