import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';

type Attrs = {
	title: string;
	imageUrl: string;
	imageAlt: string;
	imageId: number;
};

export default function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const blockProps = useBlockProps( { className: 'act' } );
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Activity image', 'pediment-child' ) }>
					{ attributes.imageUrl && (
						<img
							src={ attributes.imageUrl }
							alt=""
							style={ { width: '100%', marginBottom: 8 } }
						/>
					) }
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
				</PanelBody>
			</InspectorControls>
			<article { ...blockProps }>
				{ attributes.imageUrl && (
					<img
						src={ attributes.imageUrl }
						alt={ attributes.imageAlt }
					/>
				) }
				<RichText
					tagName="b"
					value={ attributes.title }
					onChange={ ( v ) => setAttributes( { title: v } ) }
					placeholder={ __( 'Activity…', 'pediment-child' ) }
				/>
			</article>
		</>
	);
}
