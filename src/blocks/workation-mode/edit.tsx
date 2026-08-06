import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

type Attrs = { title: string; text: string; icon: string };

const ICONS: Record< string, JSX.Element > = {
	car: (
		<>
			<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2" />
			<circle cx="7" cy="17" r="2" />
			<path d="M9 17h6" />
			<circle cx="17" cy="17" r="2" />
		</>
	),
	train: (
		<>
			<rect width="16" height="16" x="4" y="3" rx="2" />
			<path d="M4 11h16" />
			<path d="M12 3v8" />
			<path d="m8 19-2 3" />
			<path d="m18 22-2-3" />
			<path d="M8 15h.01" />
			<path d="M16 15h.01" />
		</>
	),
	plane: (
		<>
			<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z" />
		</>
	),
};

export default function Edit( {
	attributes,
	setAttributes,
}: {
	attributes: Attrs;
	setAttributes: ( a: Partial< Attrs > ) => void;
} ) {
	const blockProps = useBlockProps( { className: 'mode' } );
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Travel mode', 'workation' ) }>
					<SelectControl
						label={ __( 'Icon', 'workation' ) }
						value={ attributes.icon }
						options={ [
							{
								label: __( 'Car', 'workation' ),
								value: 'car',
							},
							{
								label: __( 'Train', 'workation' ),
								value: 'train',
							},
							{
								label: __( 'Plane', 'workation' ),
								value: 'plane',
							},
						] }
						onChange={ ( v ) => setAttributes( { icon: v } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<span className="mode-icon" aria-hidden="true">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 24 24"
						fill="none"
						stroke="currentColor"
						strokeWidth={ 2 }
						strokeLinecap="round"
						strokeLinejoin="round"
					>
						{ ICONS[ attributes.icon ] ?? ICONS.car }
					</svg>
				</span>
				<div>
					<RichText
						tagName="b"
						value={ attributes.title }
						onChange={ ( v ) => setAttributes( { title: v } ) }
						placeholder={ __( 'Mode…', 'workation' ) }
					/>
					<RichText
						tagName="span"
						value={ attributes.text }
						onChange={ ( v ) => setAttributes( { text: v } ) }
						placeholder={ __( 'Detail…', 'workation' ) }
					/>
				</div>
			</div>
		</>
	);
}
