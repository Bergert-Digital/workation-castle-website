import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';

const DEFAULT_IMAGE =
	'https://workationcastle.com/wp-content/uploads/2024/01/Workation_Castle_Piano_Lake.jpg';

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
	const imageUrl = attributes.imageUrl || DEFAULT_IMAGE;

	return (
		<section { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Hero image', 'pediment-child' ) }>
					<p>
						{ __(
							'Defaults to the homepage image. Choose another to override it on this page.',
							'pediment-child'
						) }
					</p>
					<img
						src={ imageUrl }
						alt=""
						style={ { width: '100%', marginBottom: 8 } }
					/>
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
									{ __( 'Replace image', 'pediment-child' ) }
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
							{ __(
								'Reset to homepage image',
								'pediment-child'
							) }
						</Button>
					) }
					<TextControl
						label={ __( 'Alt text', 'pediment-child' ) }
						value={ attributes.imageAlt }
						onChange={ ( v ) => setAttributes( { imageAlt: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div className="page-hero-img">
				<img src={ imageUrl } alt={ attributes.imageAlt } />
			</div>
			<div className="page-hero-grad"></div>
			<div className="page-hero-inner wc-wrap">
				<RichText
					tagName="span"
					className="eyebrow"
					value={ attributes.eyebrow }
					onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
					placeholder={ __( 'Eyebrow…', 'pediment-child' ) }
				/>
				<RichText
					tagName="h1"
					value={ attributes.headline }
					onChange={ ( v ) => setAttributes( { headline: v } ) }
					placeholder={ __( 'Headline…', 'pediment-child' ) }
				/>
				<RichText
					tagName="p"
					className="page-hero-lede"
					value={ attributes.lead }
					onChange={ ( v ) => setAttributes( { lead: v } ) }
					placeholder={ __( 'Lead…', 'pediment-child' ) }
				/>
			</div>
		</section>
	);
}
