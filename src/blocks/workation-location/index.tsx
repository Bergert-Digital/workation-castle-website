import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import WorkationSectionEdit from '../workation-sections';

function Edit( props ) {
	return <WorkationSectionEdit section="location" { ...props } />;
}

registerBlockType( metadata.name, { edit: Edit } );
