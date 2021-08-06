import {useDrag, useDrop} from 'react-dnd';

import {useQueryPosts, useQueueItems} from "../hooks/use-queues";
import {useCallback, useEffect, useState} from "@wordpress/element";
import LoadingLine from "./LoadingLine.jsx";

export const TYPE = "dnditem";

const PostStatus = ({status})=>{
    const icon = (status === "future") ? "⏱" : "✅";
    return <span className="post-item__status">{icon}</span>
}

const ListItem = (
    {
        index,
        post_id,
        post_title,
        post_status,
        post_date,
        edit_post_link,
        moveItem,
        findItem,
        onDelete
    }
) => {
    const {i18n} = PostQueue;
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
        <a href={edit_post_link}>{post_title}</a>
        <br/>
        <PostStatus status={post_status} /><span className="queue-item__date">{post_date}</span>
        <div className="delete-post" onClick={onDelete}>{i18n.remove}</div>
    </li>
}

const Controls = (
    {
        canGoBack,
        onGoBack,
        canSave,
        onSave,
        canRestore,
        onRestore,
        onAddItem,
        postIdsInQueue = []
    }
)=>{
    const {i18n} = PostQueue;
    return  <div className="post-queue-editor__controls">
        <button
            className="cancel-queue queue-control-button button button-secondary"
            onClick={onGoBack}
            disabled={!canGoBack}
        >
            ‹ {i18n.back}
        </button>

        <button
            className="cancel-queue queue-control-button button button-secondary"
            disabled={!canSave}
            onClick={onSave}
        >
            {i18n.save}
        </button>

        <button
            className="restore-queue queue-control-button button button-secondary"
            disabled={!canRestore}
            onClick={onRestore}
        >
            {i18n.reset}
        </button>

        <NewItem onCreate={onAddItem} postIdsInQueue={postIdsInQueue} />
    </div>
}

const NewItem = ({postIdsInQueue,onCreate})=>{
    const {i18n} = PostQueue;
    const [query, setQuery] = useState("");
    const [posts, isLoading] = useQueryPosts(query);
    return <div className="post-queue__search">
        {isLoading && <span className="spinner is-active"/>}
        {!isLoading && query !== "" && <span className="clear-query" onClick={()=>setQuery("")}>×</span>}
        <input type="text" value={query} onChange={e=>setQuery(e.target.value)} placeholder={i18n.search_post_placeholder} />
        <div className="post-queue__search--suggestions">
            <ul>
                {posts.filter(p=>!postIdsInQueue.includes(p.post_id)).map(p=>{
                    return <li key={p.post_id} onClick={()=> {
                        onCreate(p);
                        setQuery("");
                    }}>{p.post_title}</li>;
                })}
            </ul>
        </div>
    </div>
}

const QueueEditor = ({id, queueName, onGoBack}) => {
    const {
        items,
        saveItems,
        isLoading,
    } = useQueueItems(id);

    const [tmpItems, setTmpItems] = useState([]);

    useEffect(() => {
        setTmpItems([...items]);
    }, [items, id]);

    const findItem = useCallback((id) => {
        const item = tmpItems.find(i => i.post_id === id);
        return {
            item,
            index: tmpItems.indexOf(item),
        }
    }, [tmpItems]);
    const moveItem = useCallback((id, atIndex) => {
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

    const handleSave = ()=>{
        saveItems(tmpItems.map(i=>i.post_id));
    }
    const handleDelete = (item) => {
        setTmpItems(tmpItems.filter(i=>i.post_id !== item.post_id));
    }
    const handleRestore = ()=>{
        setTmpItems([...items]);
    }

    const canSave = !isLoading && (
        items.length !== tmpItems.length ||
        items.filter((item, index) => {
            const itemAtIndex = tmpItems[index];
            return typeof itemAtIndex === "undefined" || item.post_id !== tmpItems[index].post_id;
        }).length > 0
    );

    const canGoBack = !isLoading && !canSave;

    const [, drop] = useDrop(() => ({accept: TYPE}));

    return <>
        <h3>Postqueues › {queueName}</h3>
        <Controls
            canGoBack={canGoBack}
            onGoBack={onGoBack}
            canSave={canSave}
            onSave={handleSave}
            canRestore={canSave}
            onRestore={handleRestore}
            onAddItem={handleCreateItem}
            postIdsInQueue={tmpItems.map(i=>i.post_id)}
        />



        {isLoading && <LoadingLine />}


        <ul ref={drop} className="the-queue">
            {tmpItems.map((item, index) => <ListItem
                key={item.post_id}
                {...item}
                index={index}
                moveItem={moveItem}
                findItem={findItem}
                onDelete={()=>{
                    handleDelete(item);
                }}
            />)}
        </ul>
    </>
}

export default QueueEditor;