import { __ } from '@wordpress/i18n';
import {
	ButtonBlockAppender,
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl } from '@wordpress/components';

type Attrs = {
	lat: string;
	lng: string;
	title: string;
	maxZoom: number;
};

const ALLOWED = [ 'workation/activity-map-pin' ];

export default function Edit( {
	attributes,
	setAttributes,
	clientId,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
	clientId: string;
} ) {
	const blockProps = useBlockProps( { className: 'activity-map-editor' } );
	const innerProps = useInnerBlocksProps(
		{},
		{
			allowedBlocks: ALLOWED,
			templateLock: false,
			renderAppender: () => (
				<ButtonBlockAppender rootClientId={ clientId } />
			),
		}
	);
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Destination', 'workation' ) }>
					<TextControl
						label={ __( 'Name', 'workation' ) }
						value={ attributes.title }
						onChange={ ( v ) => setAttributes( { title: v } ) }
						__nextHasNoMarginBottom
					/>
					<TextControl
						label={ __( 'Latitude', 'workation' ) }
						value={ attributes.lat }
						onChange={ ( v ) => setAttributes( { lat: v } ) }
						__nextHasNoMarginBottom
					/>
					<TextControl
						label={ __( 'Longitude', 'workation' ) }
						value={ attributes.lng }
						onChange={ ( v ) => setAttributes( { lng: v } ) }
						__nextHasNoMarginBottom
					/>
					<RangeControl
						label={ __( 'Maximum zoom', 'workation' ) }
						help={ __(
							'Raise it when all points are close together.',
							'workation'
						) }
						value={ attributes.maxZoom }
						onChange={ ( v ) =>
							setAttributes( { maxZoom: v ?? 13 } )
						}
						min={ 10 }
						max={ 18 }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<p>
					{ attributes.lat && attributes.lng
						? `📍 ${
								attributes.title ||
								__( 'Destination', 'workation' )
						  } — ${ __(
								'the map renders on the front end.',
								'workation'
						  ) }`
						: __(
								'📍 Activity map — set the destination in the sidebar.',
								'workation'
						  ) }
				</p>
				<div { ...innerProps } />
			</div>
		</>
	);
}
