import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import metadata from './block.json';

function Edit() {
	const blockProps = useBlockProps( { className: 'check-in-form-editor' } );
	return (
		<div { ...blockProps }>
			<strong>{ __( 'Check-in form', 'pediment-child' ) }</strong>
			<p>
				{ __(
					'Renders the multi-step guest check-in wizard on the front end.',
					'pediment-child'
				) }
			</p>
		</div>
	);
}

registerBlockType( metadata.name, { edit: Edit } );
