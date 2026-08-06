import { __ } from '@wordpress/i18n';
import {
	ButtonBlockAppender,
	useBlockProps,
	useInnerBlocksProps,
	RichText,
} from '@wordpress/block-editor';

type Attrs = { eyebrow: string; headline: string };

const ALLOWED = [ 'workation/workation-card' ];
const TEMPLATE: [ string, Record< string, unknown > ][] = [
	[
		'workation/workation-card',
		{
			eyebrow: '01 — Team retreats',
			title: 'Team retreats',
			text: 'Meeting rooms, focus spaces and beds for the whole team — work, eat and stay together in one place.',
			linkText: 'Plan a retreat',
			linkUrl: '#book',
			imageUrl: '',
			imageAlt: 'Meeting room with a view over the landscape',
		},
	],
	[
		'workation/workation-card',
		{
			eyebrow: '02 — Workations',
			title: 'Workations',
			text: 'Fast Wi-Fi, calm rooms and a view that makes a Monday feel completely different.',
			linkText: 'See the workspace',
			linkUrl: '#spaces',
			imageUrl: '',
			imageAlt: 'Vaulted co-working room lit with warm string lights',
		},
	],
	[
		'workation/workation-card',
		{
			eyebrow: '03 — Family & groups',
			title: 'Family & group stays',
			text: 'Two homes, five bedrooms, gardens and a swimmable lake within walking distance.',
			linkText: 'Explore the homes',
			linkUrl: '#book',
			imageUrl: '',
			imageAlt: 'Living room with a bright yellow armchair',
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
		{ className: 'ways-grid' },
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
		</section>
	);
}
