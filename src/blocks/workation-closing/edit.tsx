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
	headline: string;
	imageUrl: string;
	imageAlt: string;
	imageId: number;
	primaryText: string;
	primaryUrl: string;
	secondaryText: string;
	secondaryUrl: string;
	linkText: string;
	linkUrl: string;
};

export default function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const blockProps = useBlockProps( { className: 'closing' } );
	return (
		<section { ...blockProps }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Background image & links', 'workation' ) }
				>
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
						label={ __( 'Image alt text', 'workation' ) }
						value={ attributes.imageAlt }
						onChange={ ( v ) => setAttributes( { imageAlt: v } ) }
					/>
					<UrlField
						label={ __( 'Primary URL', 'workation' ) }
						value={ attributes.primaryUrl }
						onChange={ ( v ) => setAttributes( { primaryUrl: v } ) }
					/>
					<UrlField
						label={ __( 'Secondary URL', 'workation' ) }
						value={ attributes.secondaryUrl }
						onChange={ ( v ) =>
							setAttributes( { secondaryUrl: v } )
						}
					/>
					<TextControl
						label={ __( 'Instagram URL', 'workation' ) }
						value={ attributes.linkUrl }
						onChange={ ( v ) => setAttributes( { linkUrl: v } ) }
					/>
				</PanelBody>
			</InspectorControls>
			{ attributes.imageUrl && (
				<img
					className="bg"
					src={ attributes.imageUrl }
					alt={ attributes.imageAlt }
				/>
			) }
			<div className="grad"></div>
			<div className="closing-inner">
				<RichText
					tagName="h2"
					value={ attributes.headline }
					onChange={ ( v ) => setAttributes( { headline: v } ) }
					placeholder={ __( 'Headline…', 'workation' ) }
				/>
				<div className="actions">
					<RichText
						tagName="span"
						className="wc-btn wc-btn-yellow"
						value={ attributes.primaryText }
						onChange={ ( v ) =>
							setAttributes( { primaryText: v } )
						}
						placeholder={ __( 'Primary button…', 'workation' ) }
					/>
					<RichText
						tagName="span"
						className="wc-btn wc-btn-ghost-light"
						value={ attributes.secondaryText }
						onChange={ ( v ) =>
							setAttributes( { secondaryText: v } )
						}
						placeholder={ __( 'Secondary button…', 'workation' ) }
					/>
				</div>
				<RichText
					tagName="span"
					className="text-link insta"
					value={ attributes.linkText }
					onChange={ ( v ) => setAttributes( { linkText: v } ) }
					placeholder={ __( 'Instagram link text…', 'workation' ) }
				/>
			</div>
		</section>
	);
}
