import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

type Attrs = { title: string; text: string; icon: string };

export default function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const blockProps = useBlockProps( { className: 'mode' } );
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Travel mode', 'pediment-child' ) }>
					<SelectControl
						label={ __( 'Icon', 'pediment-child' ) }
						value={ attributes.icon }
						options={ [
							{
								label: __( 'Car', 'pediment-child' ),
								value: 'car',
							},
							{
								label: __( 'Train', 'pediment-child' ),
								value: 'train',
							},
							{
								label: __( 'Plane', 'pediment-child' ),
								value: 'plane',
							},
						] }
						onChange={ ( v ) => setAttributes( { icon: v } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<span className="mode-icon" aria-hidden="true"></span>
				<div>
					<RichText
						tagName="b"
						value={ attributes.title }
						onChange={ ( v ) => setAttributes( { title: v } ) }
						placeholder={ __( 'Mode…', 'pediment-child' ) }
					/>
					<RichText
						tagName="span"
						value={ attributes.text }
						onChange={ ( v ) => setAttributes( { text: v } ) }
						placeholder={ __( 'Detail…', 'pediment-child' ) }
					/>
				</div>
			</div>
		</>
	);
}
