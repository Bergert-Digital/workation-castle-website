import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';

type WorkationItem = {
	eyebrow?: string;
	title?: string;
	text?: string;
	role?: string;
	icon?: string;
	linkText?: string;
	linkUrl?: string;
	imageId?: number;
	imageUrl?: string;
	imageAlt?: string;
	variant?: string;
};

type WorkationAttrs = {
	eyebrow?: string;
	headline?: string;
	lead?: string;
	imageId?: number;
	imageUrl?: string;
	imageAlt?: string;
	primaryText?: string;
	primaryUrl?: string;
	secondaryText?: string;
	secondaryUrl?: string;
	linkText?: string;
	linkUrl?: string;
	items?: WorkationItem[];
};

type Props = {
	section: SectionKey;
	attributes: WorkationAttrs;
	setAttributes: ( attrs: Partial< WorkationAttrs > ) => void;
};

type SectionKey =
	| 'hero'
	| 'intro'
	| 'audience'
	| 'spaces'
	| 'location'
	| 'activities'
	| 'gallery'
	| 'reviews'
	| 'closing';

const defaults: Record< SectionKey, WorkationAttrs > = {
	hero: {
		eyebrow: 'Castello di Carlazzo · Northern Italy',
		headline:
			'An Italian castle to <span class="hl">work, gather</span> and unwind.',
		lead: "Centuries-old walls and bright, modern interiors on a hill between Lake Como and Lake Lugano — with a full co-working space built for teams who'd rather work somewhere extraordinary.",
		imageUrl:
			'https://workationcastle.com/wp-content/uploads/2024/01/Workation_Castle_Piano_Lake.jpg',
		imageAlt:
			'Aerial view of the castle hamlet above Lago di Piano with the mountains beyond',
		primaryText: 'Check availability',
		primaryUrl: '#book',
		secondaryText: 'Ask for a custom offer',
		secondaryUrl: '#book',
		items: [
			{ title: 'Up to 9 guests' },
			{ title: '2 vacation homes' },
			{ title: '5 bedrooms' },
			{ title: 'Co-working space' },
			{ title: 'By a nature reserve' },
			{ title: '~1h from Milan Malpensa' },
		],
	},
	intro: {
		eyebrow: 'Why here',
		headline: 'Centuries-old walls, bright modern interiors',
		lead: 'Set on a small hill beside the Lago di Piano nature reserve, Workation Castle pairs historic stone with light, comfortable rooms. Come to focus, to bring your team together, or simply to slow down — with lakes, mountains and quiet walks right outside the door.',
	},
	audience: {
		eyebrow: 'Three ways to stay',
		headline: 'One castle, made for the way you want to be together.',
		items: [
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
	},
	spaces: {
		eyebrow: 'The spaces',
		headline: 'Room to work, and room to stay.',
		lead: 'Original stone and vaulted ceilings, paired with modern, comfortable interiors — historic character without the heaviness.',
		items: [
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
	},
	location: {
		eyebrow: 'Location & getting here',
		headline: 'Between two lakes, near the Swiss border.',
		lead: 'In an Italian valley near the Swiss border, between Lake Lugano and Lake Como — with the little Lago di Piano a short walk away. Restaurants, a bakery and shops are within walking distance; most guests leave the car parked all week.',
		imageUrl:
			'https://workationcastle.com/wp-content/uploads/2022/12/Castello-Map-Screenshot.png',
		imageAlt:
			"Map showing the castle's location between Lake Lugano and Lake Como",
		items: [
			{ icon: 'car', title: 'By car', text: 'Free parking on site' },
			{ icon: 'train', title: 'By train', text: 'Via Lugano' },
			{ icon: 'plane', title: 'By plane', text: 'Via Milan Malpensa' },
		],
	},
	activities: {
		eyebrow: 'When laptops close',
		headline: 'Lakes, trails and mountain air are part of the stay.',
		lead: 'Swim before dinner, walk the nature reserve, chase waterfalls or head into the mountains — without turning the trip into logistics.',
		items: [
			{
				title: 'Swim in the lake',
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2022/09/IMG_0531-scaled.jpeg',
				imageAlt: 'Clear lake water near the castle',
			},
			{
				title: 'Forest trails',
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2023/09/IMG_5944.jpeg',
				imageAlt: 'Forest path near Lago di Piano',
			},
			{
				title: 'Waterfalls',
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2023/09/IMG_5947.jpeg',
				imageAlt: 'Waterfall near the castle',
			},
			{
				title: 'Mountain views',
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2023/05/IMG_1511.jpeg',
				imageAlt: 'Mountain view above Lake Como',
			},
		],
	},
	gallery: {
		eyebrow: 'Inside the castle',
		headline: 'Old stone, warm rooms and space to breathe.',
		primaryText: 'See more photos',
		primaryUrl: '#book',
		items: [
			{
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2019.jpeg',
				imageAlt: 'Terrace and garden view from the castle',
				variant: 'tall',
			},
			{
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2024/01/Workation_Castle_Lamp.jpeg',
				imageAlt: 'Warm interior detail with lamp',
				variant: 'wide',
			},
			{
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2152.jpeg',
				imageAlt: 'Bright bedroom inside the castle',
			},
			{
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2265.jpeg',
				imageAlt: 'Kitchen and dining area',
			},
			{
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2247.jpeg',
				imageAlt: 'Castle room with modern furnishings',
			},
			{
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2113.jpeg',
				imageAlt: 'Stone stairway inside the castle',
			},
			{
				imageUrl:
					'https://workationcastle.com/wp-content/uploads/2024/01/Workation_Castle_Roofs.jpeg',
				imageAlt: 'Castle roofs and surrounding hills',
				variant: 'wide',
			},
		],
	},
	reviews: {
		eyebrow: 'Guest reviews',
		headline: 'People come for focus, family time and the quiet.',
		items: [
			{
				title: 'Alexander M.',
				role: 'Workation guest',
				text: 'The location is perfect — right between Lake Como and Lake Lugano, with a bonus small lake five minutes from the location. The co-working space exceeds expectations.',
			},
			{
				title: 'Simone S.',
				role: 'Workation stay',
				text: 'Ein toller und sehr entspannter Ort zum Arbeiten oder Urlaub machen. Das Haus und die gemeinsamen Arbeitsräume sind super ausgestattet.',
			},
			{
				title: 'Manuelle B.',
				role: 'Group stay',
				text: 'Die Atmosphäre des alten Gemäuers, den Ausblick von der Terrasse. Tolles und durchdachtes Konzept, auch für größere Familien, Gruppen und zum Arbeiten.',
			},
			{
				title: 'Corinne O.',
				role: 'Castle guest',
				text: 'Castello di Carlazzo — wunderschönes Kleinod in ursprünglicher Substanz und sehr ansprechend renovierten Gebäudeteilen.',
			},
			{
				title: 'Kathrin K.',
				role: 'Workation guest',
				text: 'Das Castello ist wirklich der perfekte Ort für Workation. Die Büroräume sind großzügig und sehr geschmackvoll eingerichtet.',
			},
			{
				title: 'Philippe R.',
				role: 'Family stay',
				text: 'The terrace, quietness, view on the mountains and the animals on the plain below. The host was very accommodating.',
			},
		],
	},
	closing: {
		headline: 'Bring your next week of work somewhere memorable.',
		imageUrl:
			'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2019.jpeg',
		imageAlt: 'Garden terrace at Workation Castle',
		primaryText: 'Check availability',
		primaryUrl: '#book',
		secondaryText: 'Ask for a custom offer',
		secondaryUrl: '#book',
		linkText: 'Follow on Instagram',
		linkUrl: 'https://www.instagram.com/workationcastle/',
	},
};

function mergeDefaults( section: SectionKey, attributes: WorkationAttrs ) {
	const fallback = defaults[ section ];
	const merged = { ...fallback };

	Object.entries( attributes ).forEach( ( [ key, value ] ) => {
		if ( key === 'items' ) {
			return;
		}

		if ( value !== undefined && value !== '' ) {
			merged[ key ] = value;
		}
	} );

	return {
		...merged,
		items: attributes.items?.length
			? attributes.items
			: fallback.items || [],
	};
}

function createItem( section: SectionKey, index: number ): WorkationItem {
	if ( section === 'audience' ) {
		return {
			eyebrow: `${ String( index + 1 ).padStart( 2, '0' ) } — New card`,
			title: 'New card',
			text: 'Add a short description for this stay type.',
			linkText: 'Learn more',
			linkUrl: '#book',
			imageUrl:
				'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2186.jpeg',
			imageAlt: '',
		};
	}

	return {
		title: 'New pill',
	};
}

export default function WorkationSectionEdit( {
	section,
	attributes,
	setAttributes,
}: Props ) {
	const data = mergeDefaults( section, attributes );
	const blockProps = useBlockProps( {
		className: 'workation-editor-preview',
	} );

	const setItem = ( index: number, patch: Partial< WorkationItem > ) => {
		const next = [ ...( data.items || [] ) ];
		next[ index ] = { ...next[ index ], ...patch };
		setAttributes( { items: next } );
	};

	const addItem = () => {
		setAttributes( {
			items: [
				...( data.items || [] ),
				createItem( section, data.items?.length || 0 ),
			],
		} );
	};

	const removeItem = ( index: number ) => {
		setAttributes( {
			items: ( data.items || [] ).filter( ( _item, itemIndex ) => {
				return itemIndex !== index;
			} ),
		} );
	};

	const sectionHasMedia =
		!! data.imageUrl || section === 'location' || section === 'closing';
	const itemMediaSections: SectionKey[] = [
		'audience',
		'spaces',
		'activities',
		'gallery',
	];
	const sectionHasItemMedia = itemMediaSections.includes( section );

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Links and media', 'pediment-child' ) }>
					{ sectionHasMedia && (
						<MediaSelector
							label={ __( 'Section image', 'pediment-child' ) }
							imageId={ data.imageId }
							imageUrl={ data.imageUrl }
							imageAlt={ data.imageAlt }
							onSelect={ ( patch ) => setAttributes( patch ) }
						/>
					) }
					<TextControl
						label={ __( 'Primary URL', 'pediment-child' ) }
						value={ data.primaryUrl || '' }
						onChange={ ( value ) =>
							setAttributes( { primaryUrl: value } )
						}
					/>
					<TextControl
						label={ __( 'Secondary URL', 'pediment-child' ) }
						value={ data.secondaryUrl || '' }
						onChange={ ( value ) =>
							setAttributes( { secondaryUrl: value } )
						}
					/>
					<TextControl
						label={ __( 'Extra link URL', 'pediment-child' ) }
						value={ data.linkUrl || '' }
						onChange={ ( value ) =>
							setAttributes( { linkUrl: value } )
						}
					/>
				</PanelBody>
				{ sectionHasItemMedia && !! data.items?.length && (
					<PanelBody
						title={ __( 'Item media and links', 'pediment-child' ) }
						initialOpen={ false }
					>
						{ data.items.map( ( item, index ) => (
							<div
								className="workation-editor-preview__item-controls"
								key={ index }
							>
								<strong>
									{ item.title ||
										item.imageAlt ||
										`${ __( 'Item', 'pediment-child' ) } ${
											index + 1
										}` }
								</strong>
								<MediaSelector
									label={ __( 'Image', 'pediment-child' ) }
									imageId={ item.imageId }
									imageUrl={ item.imageUrl }
									imageAlt={ item.imageAlt }
									onSelect={ ( patch ) =>
										setItem( index, patch )
									}
								/>
								<TextControl
									label={ __( 'Link URL', 'pediment-child' ) }
									value={ item.linkUrl || '' }
									onChange={ ( value ) =>
										setItem( index, { linkUrl: value } )
									}
								/>
								<TextControl
									label={ __( 'Variant', 'pediment-child' ) }
									value={ item.variant || '' }
									onChange={ ( value ) =>
										setItem( index, { variant: value } )
									}
								/>
							</div>
						) ) }
					</PanelBody>
				) }
			</InspectorControls>
			{ renderSection(
				section,
				data,
				setAttributes,
				setItem,
				addItem,
				removeItem
			) }
		</div>
	);
}

function MediaSelector( {
	label,
	imageId,
	imageUrl,
	imageAlt,
	onSelect,
}: {
	label: string;
	imageId?: number;
	imageUrl?: string;
	imageAlt?: string;
	onSelect: ( patch: Partial< WorkationAttrs & WorkationItem > ) => void;
} ) {
	return (
		<div className="workation-editor-preview__media-control">
			<span className="components-base-control__label">{ label }</span>
			{ imageUrl && (
				<img
					className="workation-editor-preview__media-thumb"
					src={ imageUrl }
					alt=""
				/>
			) }
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ imageId }
					onSelect={ ( media ) => {
						onSelect( {
							imageId: media.id,
							imageUrl: media.url,
							imageAlt: media.alt || imageAlt || '',
						} );
					} }
					render={ ( { open } ) => (
						<Button variant="secondary" onClick={ open }>
							{ imageUrl
								? __( 'Replace image', 'pediment-child' )
								: __( 'Select image', 'pediment-child' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			{ imageUrl && (
				<Button
					variant="link"
					isDestructive
					onClick={ () =>
						onSelect( {
							imageId: undefined,
							imageUrl: '',
							imageAlt: '',
						} )
					}
				>
					{ __( 'Remove image', 'pediment-child' ) }
				</Button>
			) }
			<TextControl
				label={ __( 'Alt text', 'pediment-child' ) }
				value={ imageAlt || '' }
				onChange={ ( value ) => onSelect( { imageAlt: value } ) }
			/>
		</div>
	);
}

function Field( {
	tagName,
	className,
	value,
	onChange,
	placeholder,
}: {
	tagName: keyof JSX.IntrinsicElements;
	className?: string;
	value?: string;
	onChange: ( value: string ) => void;
	placeholder?: string;
} ) {
	return (
		<RichText
			tagName={ tagName }
			className={ className }
			value={ value || '' }
			onChange={ onChange }
			placeholder={ placeholder }
			allowedFormats={ [ 'core/bold', 'core/italic' ] }
		/>
	);
}

function renderSection(
	section: SectionKey,
	data: WorkationAttrs,
	setAttributes: ( attrs: Partial< WorkationAttrs > ) => void,
	setItem: ( index: number, patch: Partial< WorkationItem > ) => void,
	addItem: () => void,
	removeItem: ( index: number ) => void
) {
	if ( section === 'hero' ) {
		return (
			<section className="hero" id="book">
				<div className="hero-img">
					<img src={ data.imageUrl } alt={ data.imageAlt } />
				</div>
				<div className="hero-grad"></div>
				<div className="hero-content">
					<div className="wc-wrap">
						<Field
							tagName="span"
							className="eyebrow"
							value={ data.eyebrow }
							onChange={ ( value ) =>
								setAttributes( { eyebrow: value } )
							}
						/>
						<Field
							tagName="h1"
							value={ data.headline }
							onChange={ ( value ) =>
								setAttributes( { headline: value } )
							}
						/>
						<Field
							tagName="p"
							className="lede"
							value={ data.lead }
							onChange={ ( value ) =>
								setAttributes( { lead: value } )
							}
						/>
						<div className="avail">
							<div className="avail-field">
								<label htmlFor="workation-editor-arrival">
									Arrival
								</label>
								<input
									id="workation-editor-arrival"
									type="date"
									disabled
								/>
							</div>
							<div className="avail-field">
								<label htmlFor="workation-editor-departure">
									Departure
								</label>
								<input
									id="workation-editor-departure"
									type="date"
									disabled
								/>
							</div>
							<div className="avail-field select-wrap">
								<label htmlFor="workation-editor-guests">
									Guests
								</label>
								<select id="workation-editor-guests" disabled>
									<option>2 guests</option>
								</select>
							</div>
							<div className="avail-submit">
								<Field
									tagName="span"
									className="wc-btn wc-btn-yellow"
									value={ data.primaryText }
									onChange={ ( value ) =>
										setAttributes( { primaryText: value } )
									}
								/>
							</div>
						</div>
						<div className="hero-secondary">
							<Field
								tagName="span"
								value={ data.secondaryText }
								onChange={ ( value ) =>
									setAttributes( { secondaryText: value } )
								}
							/>
						</div>
						<div className="hero-chips">
							{ data.items?.map( ( item, index ) => (
								<span
									className="workation-editor-preview__chip-control"
									key={ index }
								>
									<Field
										tagName="span"
										className="chip"
										value={ item.title }
										onChange={ ( value ) =>
											setItem( index, { title: value } )
										}
									/>
									<Button
										className="workation-editor-preview__remove"
										label={ __(
											'Remove pill',
											'pediment-child'
										) }
										onClick={ () => removeItem( index ) }
										size="small"
										variant="tertiary"
									>
										×
									</Button>
								</span>
							) ) }
							<Button
								className="workation-editor-preview__add"
								onClick={ addItem }
								size="small"
								variant="secondary"
							>
								{ __( 'Add pill', 'pediment-child' ) }
							</Button>
						</div>
					</div>
				</div>
			</section>
		);
	}

	if ( section === 'intro' ) {
		return (
			<section className="band band-cream intro">
				<div className="wc-wrap">
					<Field
						tagName="span"
						className="wc-kicker"
						value={ data.eyebrow }
						onChange={ ( value ) =>
							setAttributes( { eyebrow: value } )
						}
					/>
					<Field
						tagName="h2"
						value={ data.headline }
						onChange={ ( value ) =>
							setAttributes( { headline: value } )
						}
					/>
					<Field
						tagName="p"
						value={ data.lead }
						onChange={ ( value ) =>
							setAttributes( { lead: value } )
						}
					/>
				</div>
			</section>
		);
	}

	if ( section === 'audience' ) {
		return (
			<section className="band band-deep" id="stay">
				<SectionHead data={ data } setAttributes={ setAttributes } />
				<div className="wc-wrap">
					<div className="ways-grid">
						{ data.items?.map( ( item, index ) => (
							<article className="way" key={ index }>
								<img
									src={ item.imageUrl }
									alt={ item.imageAlt }
								/>
								<div className="way-body">
									<ItemField
										item={ item }
										index={ index }
										itemKey="eyebrow"
										tagName="span"
										className="way-num"
										setItem={ setItem }
									/>
									<ItemField
										item={ item }
										index={ index }
										itemKey="title"
										tagName="h3"
										setItem={ setItem }
									/>
									<ItemField
										item={ item }
										index={ index }
										itemKey="text"
										tagName="p"
										setItem={ setItem }
									/>
									<ItemField
										item={ item }
										index={ index }
										itemKey="linkText"
										tagName="span"
										className="link"
										setItem={ setItem }
									/>
									<Button
										className="workation-editor-preview__card-remove"
										onClick={ () => removeItem( index ) }
										size="small"
										variant="secondary"
									>
										{ __(
											'Remove card',
											'pediment-child'
										) }
									</Button>
								</div>
							</article>
						) ) }
					</div>
					<div className="workation-editor-preview__inline-actions">
						<Button
							onClick={ addItem }
							size="small"
							variant="secondary"
						>
							{ __( 'Add card', 'pediment-child' ) }
						</Button>
					</div>
				</div>
			</section>
		);
	}

	if ( section === 'spaces' ) {
		return (
			<section className="band band-cream" id="spaces">
				<SectionHead
					data={ data }
					setAttributes={ setAttributes }
					lead
				/>
				<div className="wc-wrap">
					{ data.items?.map( ( item, index ) => (
						<div
							className={ `space-row${
								index === 1 ? ' reverse' : ''
							}` }
							key={ index }
						>
							<div className="space-photo">
								<img
									src={ item.imageUrl }
									alt={ item.imageAlt }
								/>
							</div>
							<div className="space-text">
								<ItemField
									item={ item }
									index={ index }
									itemKey="eyebrow"
									tagName="span"
									className="num"
									setItem={ setItem }
								/>
								<ItemField
									item={ item }
									index={ index }
									itemKey="title"
									tagName="h3"
									setItem={ setItem }
								/>
								<ItemField
									item={ item }
									index={ index }
									itemKey="text"
									tagName="p"
									setItem={ setItem }
								/>
								<ItemField
									item={ item }
									index={ index }
									itemKey="linkText"
									tagName="span"
									className="text-link"
									setItem={ setItem }
								/>
							</div>
						</div>
					) ) }
				</div>
			</section>
		);
	}

	if ( section === 'location' ) {
		return (
			<section className="band band-deep" id="location">
				<SectionHead data={ data } setAttributes={ setAttributes } />
				<div className="wc-wrap">
					<div className="loc-grid">
						<div className="loc-map">
							<img src={ data.imageUrl } alt={ data.imageAlt } />
						</div>
						<div className="loc-text">
							<Field
								tagName="p"
								value={ data.lead }
								onChange={ ( value ) =>
									setAttributes( { lead: value } )
								}
							/>
							<div className="modes">
								{ data.items?.map( ( item, index ) => (
									<div className="mode" key={ index }>
										<span className="mode-icon"></span>
										<div>
											<ItemField
												item={ item }
												index={ index }
												itemKey="title"
												tagName="b"
												setItem={ setItem }
											/>
											<ItemField
												item={ item }
												index={ index }
												itemKey="text"
												tagName="span"
												setItem={ setItem }
											/>
										</div>
									</div>
								) ) }
							</div>
						</div>
					</div>
				</div>
			</section>
		);
	}

	if ( section === 'activities' ) {
		return (
			<section className="band band-cream" id="activities">
				<SectionHead
					data={ data }
					setAttributes={ setAttributes }
					lead
				/>
				<div className="wc-wrap">
					<div className="act-grid">
						{ data.items?.map( ( item, index ) => (
							<article className="act" key={ index }>
								<img
									src={ item.imageUrl }
									alt={ item.imageAlt }
								/>
								<ItemField
									item={ item }
									index={ index }
									itemKey="title"
									tagName="b"
									setItem={ setItem }
								/>
							</article>
						) ) }
					</div>
				</div>
			</section>
		);
	}

	if ( section === 'gallery' ) {
		return (
			<section className="band band-deep" id="gallery">
				<SectionHead data={ data } setAttributes={ setAttributes } />
				<div className="wc-wrap">
					<div className="gallery">
						{ data.items?.map( ( item, index ) => (
							<span
								className={
									item.variant ? `g-${ item.variant }` : ''
								}
								key={ index }
							>
								<img
									src={ item.imageUrl }
									alt={ item.imageAlt }
								/>
							</span>
						) ) }
					</div>
					<div className="gallery-foot">
						<Field
							tagName="span"
							className="wc-btn wc-btn-ghost-dark"
							value={ data.primaryText }
							onChange={ ( value ) =>
								setAttributes( { primaryText: value } )
							}
						/>
					</div>
				</div>
			</section>
		);
	}

	if ( section === 'reviews' ) {
		return (
			<section className="band band-cream" id="reviews">
				<SectionHead data={ data } setAttributes={ setAttributes } />
				<div className="wc-wrap">
					<div className="reviews-grid">
						{ data.items?.map( ( item, index ) => (
							<article className="review" key={ index }>
								<div className="stars">★★★★★</div>
								<ItemField
									item={ item }
									index={ index }
									itemKey="text"
									tagName="p"
									setItem={ setItem }
								/>
								<div className="cite">
									<span className="dot">
										{ item.title?.charAt( 0 ) || '' }
									</span>
									<div>
										<ItemField
											item={ item }
											index={ index }
											itemKey="title"
											tagName="b"
											setItem={ setItem }
										/>
										<ItemField
											item={ item }
											index={ index }
											itemKey="role"
											tagName="span"
											setItem={ setItem }
										/>
									</div>
								</div>
							</article>
						) ) }
					</div>
				</div>
			</section>
		);
	}

	return (
		<section className="closing">
			<img className="bg" src={ data.imageUrl } alt={ data.imageAlt } />
			<div className="grad"></div>
			<div className="closing-inner">
				<Field
					tagName="h2"
					value={ data.headline }
					onChange={ ( value ) =>
						setAttributes( { headline: value } )
					}
				/>
				<div className="actions">
					<Field
						tagName="span"
						className="wc-btn wc-btn-yellow"
						value={ data.primaryText }
						onChange={ ( value ) =>
							setAttributes( { primaryText: value } )
						}
					/>
					<Field
						tagName="span"
						className="wc-btn wc-btn-ghost-light"
						value={ data.secondaryText }
						onChange={ ( value ) =>
							setAttributes( { secondaryText: value } )
						}
					/>
				</div>
				<Field
					tagName="span"
					className="insta"
					value={ data.linkText }
					onChange={ ( value ) =>
						setAttributes( { linkText: value } )
					}
				/>
			</div>
		</section>
	);
}

function SectionHead( {
	data,
	setAttributes,
	lead = false,
}: {
	data: WorkationAttrs;
	setAttributes: ( attrs: Partial< WorkationAttrs > ) => void;
	lead?: boolean;
} ) {
	return (
		<div className="sec-head">
			<Field
				tagName="span"
				className="wc-kicker"
				value={ data.eyebrow }
				onChange={ ( value ) => setAttributes( { eyebrow: value } ) }
			/>
			<Field
				tagName="h2"
				value={ data.headline }
				onChange={ ( value ) => setAttributes( { headline: value } ) }
			/>
			{ lead && (
				<Field
					tagName="p"
					value={ data.lead }
					onChange={ ( value ) => setAttributes( { lead: value } ) }
				/>
			) }
		</div>
	);
}

function ItemField( {
	item,
	index,
	itemKey,
	tagName,
	className,
	setItem,
}: {
	item: WorkationItem;
	index: number;
	itemKey: keyof WorkationItem;
	tagName: keyof JSX.IntrinsicElements;
	className?: string;
	setItem: ( index: number, patch: Partial< WorkationItem > ) => void;
} ) {
	return (
		<Field
			tagName={ tagName }
			className={ className }
			value={ String( item[ itemKey ] || '' ) }
			onChange={ ( value ) => setItem( index, { [ itemKey ]: value } ) }
		/>
	);
}
