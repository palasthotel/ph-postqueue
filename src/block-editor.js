import './scripts/block-editor/query-loop-variation.jsx';
import { registerPlugin } from '@wordpress/plugins';
import PostqueuePanel from './scripts/block-editor/PostqueuePanel.jsx';

registerPlugin( 'postqueue', {
	render: PostqueuePanel,
} );
