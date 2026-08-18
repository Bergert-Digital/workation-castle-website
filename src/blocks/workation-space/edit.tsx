import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';
import UrlField from '../shared/url-field';

type Attrs = {
	eyebrow: string;
	title: string;
	text: string;
	linkText: string;
	linkUrl: string;
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
	const blockProps = useBlockProps( { className: 'space-row' } );
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Space image & link', 'workation' ) }>
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
					<UrlField
						label={ __( 'Link URL', 'workation' ) }
						value={ attributes.linkUrl }
						onChange={ ( v ) => setAttributes( { linkUrl: v } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<div className="space-photo">
					{ attributes.imageUrl && (
						<img
							src={ attributes.imageUrl }
							alt={ attributes.imageAlt }
						/>
					) }
				</div>
				<div className="space-text">
					<RichText
						tagName="span"
						className="num"
						value={ attributes.eyebrow }
						onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
						placeholder={ __( 'Number…', 'workation' ) }
					/>
					<RichText
						tagName="h3"
						value={ attributes.title }
						onChange={ ( v ) => setAttributes( { title: v } ) }
						placeholder={ __( 'Title…', 'workation' ) }
					/>
					<RichText
						tagName="p"
						value={ attributes.text }
						onChange={ ( v ) => setAttributes( { text: v } ) }
						placeholder={ __( 'Text…', 'workation' ) }
					/>
					<RichText
						tagName="span"
						className="text-link"
						value={ attributes.linkText }
						onChange={ ( v ) => setAttributes( { linkText: v } ) }
						placeholder={ __( 'Link text…', 'workation' ) }
					/>
				</div>
			</div>
		</>
	);
}
