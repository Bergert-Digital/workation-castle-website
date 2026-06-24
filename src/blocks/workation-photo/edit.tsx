import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	Button,
	TextControl,
	SelectControl,
} from '@wordpress/components';

type Attrs = {
	imageUrl: string;
	imageAlt: string;
	imageId: number;
	variant: string;
};

export default function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const cls = attributes.variant ? `g-${ attributes.variant }` : '';
	const blockProps = useBlockProps( { className: cls } );
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Photo', 'pediment-child' ) }>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							value={ attributes.imageId }
							onSelect={ ( m: any ) =>
								setAttributes( {
									imageId: m.id,
									imageUrl: m.url,
									imageAlt: m.alt || '',
								} )
							}
							render={ ( { open }: { open: () => void } ) => (
								<Button variant="secondary" onClick={ open }>
									{ attributes.imageUrl
										? __(
												'Replace image',
												'pediment-child'
										  )
										: __(
												'Select image',
												'pediment-child'
										  ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<TextControl
						label={ __( 'Alt text', 'pediment-child' ) }
						value={ attributes.imageAlt }
						onChange={ ( v ) => setAttributes( { imageAlt: v } ) }
					/>
					<SelectControl
						label={ __( 'Size', 'pediment-child' ) }
						value={ attributes.variant }
						options={ [
							{
								label: __( 'Normal', 'pediment-child' ),
								value: '',
							},
							{
								label: __( 'Tall', 'pediment-child' ),
								value: 'tall',
							},
							{
								label: __( 'Wide', 'pediment-child' ),
								value: 'wide',
							},
						] }
						onChange={ ( v ) => setAttributes( { variant: v } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<span { ...blockProps }>
				{ attributes.imageUrl ? (
					<img
						src={ attributes.imageUrl }
						alt={ attributes.imageAlt }
					/>
				) : (
					<span className="components-placeholder__label">
						{ __( 'Select an image…', 'pediment-child' ) }
					</span>
				) }
			</span>
		</>
	);
}
