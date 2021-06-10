import {useDrag, useDrop} from 'react-dnd';

import {useQueryPosts, useQueueItems} from "../hooks/use-queues";
import {useCallback, useEffect, useState} from "@wordpress/element";

export const TYPE = "dnditem";

const ListItem = ({index, post_id, post_title, moveItem, findItem}) => {

    const originalIndex = findItem(post_id).index;
    const [{isDragging}, drag, preview] = useDrag(() => ({
        type: TYPE,
        item: {post_id, originalIndex},
        collect: (monitor) => ({
            isDragging: monitor.isDragging(),
        }),
        end: (item, monitor) => {
            const {post_id: droppedId, originalIndex} = item;
            const didDrop = monitor.didDrop();
            if (!didDrop) {
                moveItem(droppedId, originalIndex);
            }
        },
    }), [post_id, originalIndex, moveItem]);
    const [, drop] = useDrop(() => ({
        accept: TYPE,
        canDrop: () => false,
        hover({post_id: draggedId}) {
            if (draggedId !== post_id) {
                const {index: overIndex} = findItem(post_id);
                moveItem(draggedId, overIndex);
            }
        },
    }), [findItem, moveItem]);

    return <li
        ref={(node) => preview(drop(node))}
        className={`queue-item queue-item-set ${isDragging ? "is-dragging" : ""}`}
    >
        <div ref={drag} className="drag-handle ui-sortable-handle"/>
        <span>{post_title}</span>
        <div className="delete-post">Delete</div>
    </li>
}

const NewItem = ({onCreate})=>{
    const [query, setQuery] = useState("");
    const posts = useQueryPosts(query);
    return <div>
        <input type="text" value={query} onChange={e=>setQuery(e.target.value)} />
        <ul>
            {posts.map(p=><li key={p.post_id} onClick={()=>onCreate(p)}>{p.post_title}</li>)}
        </ul>
    </div>
}

const QueueEditor = ({id, onCancel}) => {

    const {
        items,
        isLoading,
    } = useQueueItems(id);

    const [addItem, setAddItem] = useState(false);
    const [tmpItems, setTmpItems] = useState([]);

    useEffect(() => {
        setTmpItems([...items]);
    }, [items, id]);

    console.debug("tmpItem", tmpItems);

    const findItem = useCallback((id) => {
        const item = tmpItems.find(i => i.post_id === id);
        console.debug("findItem", id, item);
        return {
            item,
            index: tmpItems.indexOf(item),
        }
    }, [tmpItems]);
    const moveItem = useCallback((id, atIndex) => {
        console.debug("moveItem", id, atIndex);
        const {index, item} = findItem(id);
        const moved = [...tmpItems];
        moved.splice(index, 1);
        moved.splice(atIndex, 0, item);
        setTmpItems(moved);
    }, [findItem, tmpItems, setTmpItems]);

    const handleCreateItem = (post)=>{
        setTmpItems([
            post,
            ...tmpItems,
        ]);
    }

    const canSave = !isLoading && (
        items.length !== tmpItems.length ||
        items.filter((item, index) => {
            const itemAtIndex = tmpItems[index];
            return typeof itemAtIndex === "undefined" || item.post_id !== tmpItems[index].post_id;
        }).length > 0
    );

    const [, drop] = useDrop(() => ({accept: TYPE}));

    return <>
        <button
            className="cancel-queue button button-secondary"
            onClick={onCancel}
        >
            Cancel
        </button>
        <button
            disabled={!canSave}
        >
            Save
        </button>

        <button
            onClick={()=>setAddItem(true)}
        >
            Add
        </button>

        {addItem && <NewItem onCreate={handleCreateItem} />}

        <ul ref={drop} className="the-queue">
            {tmpItems.map((item, index) => <ListItem
                key={item.post_id}
                {...item}
                index={index}
                moveItem={moveItem}
                findItem={findItem}
            />)}
        </ul>
    </>
}

export default QueueEditor;