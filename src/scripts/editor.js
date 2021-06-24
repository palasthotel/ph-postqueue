import React from 'react';
import {render} from 'react-dom';
import domReady from '@wordpress/dom-ready';
import Editor from "./components/Editor.jsx";
import {HTML5Backend} from "react-dnd-html5-backend";
import { DndProvider } from 'react-dnd';

domReady(()=>{
    render(
        <DndProvider backend={HTML5Backend}>
            <Editor />
        </DndProvider>,
        document.getElementById("post-queue-editor")
    );
});