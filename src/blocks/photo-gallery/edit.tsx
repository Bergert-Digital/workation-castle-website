import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';

type Attrs = {
	eyebrow: string;
	headline: string;
};

export default function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const blockProps = useBlockProps( { className: 'photos' } );
	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Photo gallery', 'workation' ) }>
					<p>
						{ __(
							'Displays every published Photo, ordered by the Order field. Manage photos under Photos in the admin menu.',
							'workation'
						) }
					</p>
				</PanelBody>
			</InspectorControls>
			<div className="sec-head">
				<RichText
					tagName="span"
					className="wc-kicker"
					value={ attributes.eyebrow }
					onChange={ ( eyebrow: string ) =>
						setAttributes( { eyebrow } )
					}
					placeholder={ __( 'Eyebrow…', 'workation' ) }
				/>
				<RichText
					tagName="h2"
					value={ attributes.headline }
					onChange={ ( headline: string ) =>
						setAttributes( { headline } )
					}
					placeholder={ __( 'Headline…', 'workation' ) }
				/>
			</div>
			<p className="photo-grid-placeholder">
				{ __(
					'Filterable photo grid renders here on the front end.',
					'workation'
				) }
			</p>
		</div>
	);
}
