import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import QueueEditor from './components/QueueEditor.jsx';

domReady( () => {
	const container = document.getElementById( 'post-queue-editor' );
	if ( ! container ) {
		return;
	}

	// createRoot, not ReactDOM.render: WordPress ships React 18, where the legacy entry
	// point warns and falls back to React 17 behaviour.
	createRoot( container ).render( <QueueEditor /> );
} );
