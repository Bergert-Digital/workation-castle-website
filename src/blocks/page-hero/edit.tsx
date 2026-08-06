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
	eyebrow: string;
	headline: string;
	lead: string;
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
	const blockProps = useBlockProps( { className: 'page-hero' } );
	// The live render falls back to the bundled theme hero image when no image
	// is set; the editor can't resolve that theme URL, so it previews empty.
	const imageUrl = attributes.imageUrl;

	return (
		<section { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Hero image', 'workation' ) }>
					<p>
						{ __(
							'Defaults to the homepage image. Choose another to override it on this page.',
							'workation'
						) }
					</p>
					{ imageUrl && (
						<img
							src={ imageUrl }
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
									{ __( 'Replace image', 'workation' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ attributes.imageUrl && (
						<Button
							variant="link"
							isDestructive
							onClick={ () =>
								setAttributes( {
									imageId: 0,
									imageUrl: '',
									imageAlt: '',
								} )
							}
							style={ { display: 'block', marginTop: 8 } }
						>
							{ __( 'Reset to homepage image', 'workation' ) }
						</Button>
					) }
					<TextControl
						label={ __( 'Alt text', 'workation' ) }
						value={ attributes.imageAlt }
						onChange={ ( v ) => setAttributes( { imageAlt: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div className="page-hero-img">
				{ imageUrl && (
					<img src={ imageUrl } alt={ attributes.imageAlt } />
				) }
			</div>
			<div className="page-hero-grad"></div>
			<div className="page-hero-inner wc-wrap">
				<RichText
					tagName="span"
					className="eyebrow"
					value={ attributes.eyebrow }
					onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
					placeholder={ __( 'Eyebrow…', 'workation' ) }
				/>
				<RichText
					tagName="h1"
					value={ attributes.headline }
					onChange={ ( v ) => setAttributes( { headline: v } ) }
					placeholder={ __( 'Headline…', 'workation' ) }
				/>
				<RichText
					tagName="p"
					className="page-hero-lede"
					value={ attributes.lead }
					onChange={ ( v ) => setAttributes( { lead: v } ) }
					placeholder={ __( 'Lead…', 'workation' ) }
				/>
			</div>
		</section>
	);
}
