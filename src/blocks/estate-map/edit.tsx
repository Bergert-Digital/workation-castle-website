import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'estate-map-editor' } );
	return (
		<div { ...blockProps }>
			{ __(
				'🗺 Estate map — the illustrated map and legend render on the front end.',
				'workation'
			) }
		</div>
	);
}
