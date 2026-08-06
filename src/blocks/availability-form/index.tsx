import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import metadata from './block.json';

type Attrs = {
	actionUrl: string;
};

function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const blockProps = useBlockProps( {
		className: 'availability-form-editor',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Availability form', 'workation' ) }>
					<TextControl
						label={ __( 'Action URL', 'workation' ) }
						value={ attributes.actionUrl }
						onChange={ ( actionUrl ) =>
							setAttributes( { actionUrl } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<strong>{ __( 'Availability form', 'workation' ) }</strong>
				<p>
					{ __(
						'Renders arrival, departure, guests, and check availability controls on the front end.',
						'workation'
					) }
				</p>
			</div>
		</>
	);
}

registerBlockType( metadata.name, { edit: Edit } );
