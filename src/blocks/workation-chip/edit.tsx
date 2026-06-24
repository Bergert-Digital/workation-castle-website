import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';

type Attrs = { title: string };

export default function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const blockProps = useBlockProps( { className: 'chip' } );
	return (
		<RichText
			{ ...blockProps }
			tagName="span"
			value={ attributes.title }
			onChange={ ( v ) => setAttributes( { title: v } ) }
			placeholder={ __( 'Pill…', 'pediment-child' ) }
			allowedFormats={ [] }
		/>
	);
}
