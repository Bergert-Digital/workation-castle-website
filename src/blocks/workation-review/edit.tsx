import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';

type Attrs = { text: string; title: string; role: string };

export default function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const blockProps = useBlockProps( { className: 'review' } );
	return (
		<article { ...blockProps }>
			<div className="stars">★★★★★</div>
			<RichText
				tagName="p"
				value={ attributes.text }
				onChange={ ( v ) => setAttributes( { text: v } ) }
				placeholder={ __( 'Review…', 'workation' ) }
				allowedFormats={ [ 'core/bold', 'core/italic' ] }
			/>
			<div className="cite">
				<span className="dot">
					{ attributes.title?.charAt( 0 ) || '' }
				</span>
				<div>
					<RichText
						tagName="b"
						value={ attributes.title }
						onChange={ ( v ) => setAttributes( { title: v } ) }
						placeholder={ __( 'Name…', 'workation' ) }
						allowedFormats={ [] }
					/>
					<RichText
						tagName="span"
						value={ attributes.role }
						onChange={ ( v ) => setAttributes( { role: v } ) }
						placeholder={ __( 'Role…', 'workation' ) }
						allowedFormats={ [] }
					/>
				</div>
			</div>
		</article>
	);
}
