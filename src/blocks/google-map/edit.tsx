import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type Attributes = { url: string; title: string };

type Props = {
	attributes: Attributes;
	setAttributes: ( attrs: Partial< Attributes > ) => void;
};

export default function Edit( { attributes, setAttributes }: Props ) {
	const { url, title } = attributes;
	const blockProps = useBlockProps();
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Map', 'workation' ) }>
					<TextControl
						label={ __( 'Embed URL', 'workation' ) }
						help={ __(
							'A Google Maps embed link (maps.google.com/maps?…&output=embed or google.com/maps/d/embed?mid=…).',
							'workation'
						) }
						value={ url }
						onChange={ ( value ) =>
							setAttributes( { url: value } )
						}
						__nextHasNoMarginBottom
					/>
					<TextControl
						label={ __( 'Title', 'workation' ) }
						help={ __(
							'Describes the map for screen readers.',
							'workation'
						) }
						value={ title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ url
					? `🗺 ${ title || __( 'Google Map', 'workation' ) }`
					: __(
							'🗺 Google Map — set the embed URL in the sidebar.',
							'workation'
					  ) }
			</div>
		</>
	);
}
