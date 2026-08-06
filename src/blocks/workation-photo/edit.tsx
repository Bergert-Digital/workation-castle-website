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
				<PanelBody title={ __( 'Photo', 'workation' ) }>
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
										? __( 'Replace image', 'workation' )
										: __( 'Select image', 'workation' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					<TextControl
						label={ __( 'Alt text', 'workation' ) }
						value={ attributes.imageAlt }
						onChange={ ( v ) => setAttributes( { imageAlt: v } ) }
					/>
					<SelectControl
						label={ __( 'Size', 'workation' ) }
						value={ attributes.variant }
						options={ [
							{
								label: __( 'Normal', 'workation' ),
								value: '',
							},
							{
								label: __( 'Tall', 'workation' ),
								value: 'tall',
							},
							{
								label: __( 'Wide', 'workation' ),
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
						{ __( 'Select an image…', 'workation' ) }
					</span>
				) }
			</span>
		</>
	);
}
