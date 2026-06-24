import { __ } from '@wordpress/i18n';
import {
	InnerBlocks,
	useBlockProps,
	useInnerBlocksProps,
	RichText,
} from '@wordpress/block-editor';

type Attrs = { eyebrow: string; headline: string };

const ALLOWED = [ 'pediment-child/workation-card' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[
		'pediment-child/workation-card',
		{
			eyebrow: '01 — Team retreats',
			title: 'Team retreats',
			text: 'Meeting rooms, focus spaces and beds for the whole team — work, eat and stay together in one place.',
			linkText: 'Plan a retreat',
			linkUrl: '#book',
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2186.jpeg',
			imageAlt: 'Meeting room with a view over the landscape',
		},
	],
	[
		'pediment-child/workation-card',
		{
			eyebrow: '02 — Workations',
			title: 'Workations',
			text: 'Fast Wi-Fi, calm rooms and a view that makes a Monday feel completely different.',
			linkText: 'See the workspace',
			linkUrl: '#spaces',
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_1758.jpeg',
			imageAlt: 'Vaulted co-working room lit with warm string lights',
		},
	],
	[
		'pediment-child/workation-card',
		{
			eyebrow: '03 — Family & groups',
			title: 'Family & group stays',
			text: 'Two homes, five bedrooms, gardens and a swimmable lake within walking distance.',
			linkText: 'Explore the homes',
			linkUrl: '#book',
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2263.jpeg',
			imageAlt: 'Living room with a bright yellow armchair',
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
		{ className: 'ways-grid' },
		{
			allowedBlocks: ALLOWED,
			template: TEMPLATE,
			templateLock: false,
			renderAppender: InnerBlocks.ButtonBlockAppender,
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
