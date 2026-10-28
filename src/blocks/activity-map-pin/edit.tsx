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
	const blockProps = useBlockProps( {
		className: 'activity-map-pin-editor',
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Pin', 'workation' ) }>
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
				{ `📍 ${
					attributes.title ||
					__( 'Pin — set it in the sidebar', 'workation' )
				}` }
			</div>
		</>
	);
}
