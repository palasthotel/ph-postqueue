import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import Editor from "./components/Editor.jsx";
import { HTML5Backend } from "react-dnd-html5-backend";
import { DndProvider } from 'react-dnd';

domReady(() => {
    const container = document.getElementById("post-queue-editor");
    if (!container) {
        return;
    }

    // createRoot, not ReactDOM.render: WordPress ships React 18, where the legacy
    // entry point warns and falls back to React 17 behaviour. createRoot comes from
    // @wordpress/element so it resolves to the wp-element core script rather than
    // bundling a second React.
    createRoot(container).render(
        <DndProvider backend={HTML5Backend}>
            <Editor />
        </DndProvider>
    );
});
