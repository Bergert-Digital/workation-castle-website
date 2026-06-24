import { __ } from '@wordpress/i18n';
import {
	InnerBlocks,
	useBlockProps,
	useInnerBlocksProps,
	RichText,
} from '@wordpress/block-editor';

type Attrs = { eyebrow: string; headline: string; lead: string };

const ALLOWED = [ 'pediment-child/workation-space' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[
		'pediment-child/workation-space',
		{
			eyebrow: '01',
			title: 'The workspace',
			text: 'Two rooms for focused work, a large meeting room, a phone booth, a small lounge and a community kitchen. Versatile enough for a coaching retreat — or for finishing your thesis in the quiet.',
			linkText: 'See the workspace',
			linkUrl: '#book',
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_1758.jpeg',
			imageAlt:
				'The co-working space — a vaulted room lit with warm string lights',
		},
	],
	[
		'pediment-child/workation-space',
		{
			eyebrow: '02',
			title: 'Two vacation homes',
			text: 'Two separately bookable houses with five bedrooms for up to nine guests, each with access to a garden. Modern comfort tucked inside centuries-old walls.',
			linkText: 'Explore the homes',
			linkUrl: '#book',
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2263.jpeg',
			imageAlt:
				'Living room of one of the vacation homes with a bright yellow armchair',
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
	const blockProps = useBlockProps( { className: 'band band-cream' } );
	const innerProps = useInnerBlocksProps(
		{ className: 'wc-wrap' },
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
				<RichText
					tagName="p"
					value={ attributes.lead }
					onChange={ ( v ) => setAttributes( { lead: v } ) }
					placeholder={ __( 'Lead…', 'pediment-child' ) }
				/>
			</div>
			<div { ...innerProps } />
		</section>
	);
}
