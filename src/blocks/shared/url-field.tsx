import { URLInput, store as blockEditorStore } from '@wordpress/block-editor';
import { BaseControl } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';

type Props = {
	label: string;
	value: string;
	onChange: ( url: string ) => void;
};

type Suggestion = { type?: string; [ key: string ]: unknown };
type FetchSuggestions = (
	search: string,
	args: Record< string, unknown >
) => Promise< Suggestion[] >;

// A site-aware URL field: a labelled text input that suggests existing pages,
// posts and custom post types as you type. Drop-in replacement for a plain
// <TextControl> that edits a URL attribute.
export default function UrlField( { label, value, onChange }: Props ) {
	const id = useInstanceId( UrlField, 'wc-url-field' ) as string;

	// Reuse the editor's own link-suggestion search (so pages, posts and CPTs
	// still resolve), then drop media attachments — we link to content, not
	// image files.
	const fetchLinkSuggestions = useSelect(
		( select ) =>
			(
				select( blockEditorStore ).getSettings() as {
					__experimentalFetchLinkSuggestions?: FetchSuggestions;
				}
			 ).__experimentalFetchLinkSuggestions,
		[]
	) as FetchSuggestions | undefined;

	const fetchWithoutMedia = useCallback< FetchSuggestions >(
		async ( search, args ) => {
			if ( ! fetchLinkSuggestions ) {
				return [];
			}
			const results = await fetchLinkSuggestions( search, args );
			return results.filter( ( r ) => r.type !== 'attachment' );
		},
		[ fetchLinkSuggestions ]
	);

	return (
		<BaseControl
			__nextHasNoMarginBottom
			id={ id }
			label={ label }
			className="wc-url-field"
		>
			<URLInput
				id={ id }
				value={ value }
				onChange={ ( url: string ) => onChange( url ) }
				__experimentalFetchLinkSuggestions={
					fetchLinkSuggestions ? fetchWithoutMedia : undefined
				}
			/>
		</BaseControl>
	);
}
