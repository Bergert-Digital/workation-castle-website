import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

type Attrs = {
	lat: string;
	lng: string;
	title: string;
};

export default function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const blockProps = useBlockProps( { className: 'activity-map-editor' } );
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
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ attributes.lat && attributes.lng
					? `📍 ${
							attributes.title || __( 'Destination', 'workation' )
					  } — ${ __(
							'the map renders on the front end.',
							'workation'
					  ) }`
					: __(
							'📍 Activity map — set the destination in the sidebar.',
							'workation'
					  ) }
			</div>
		</>
	);
}
