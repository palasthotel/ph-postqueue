import {useState} from "@wordpress/element";
import {useQueues} from "../hooks/use-queues";
import SearchOrCreate from "./SearchOrCreate.jsx";
import QueueList from "./QueueList.jsx";
import QueueEditor from "./QueueEditor.jsx";

const Editor = () => {

    const {
        queues,
        createQueue,
        deleteQueue,
        isLoading,
    } = useQueues();

    const [name, setName] = useState("");
    const [deleteQueueId, setDeleteQueueId] = useState("");
    const [selectedQueueId, setSelectedQueueId] = useState("");

    const handleCreateQueue = () => {
        if(isLoading) return;
        createQueue(name);
        setName("");
    }

    const selectedQueue = queues.find(q => q.id === selectedQueueId);

    if (selectedQueue) {
        return <QueueEditor
            id={selectedQueueId}
            onCancel={()=>setSelectedQueueId("")}
        />
    }

    const deleteQueueItem = queues.find(q=>q.id === deleteQueueId);

    return <>
        <h2>Postqueues</h2>
        <div className="ph-postqueues-widget">
            <SearchOrCreate
                queues={queues}
                name={name}
                onChangeName={setName}
                onCreate={handleCreateQueue}
            />
            {deleteQueueItem && <>
                <p>Are you sure you want to delete <strong>{deleteQueueItem.name}</strong>?</p>
                <button onClick={()=>deleteQueue(deleteQueueItem.id)}>Yes</button>
                <button onClick={()=> setDeleteQueueId("")}>No!</button>
            </>}
            <QueueList
                items={queues.filter(q=> name === "" || q.name.toLowerCase().includes(name.toLowerCase()))}
                onEdit={setSelectedQueueId}
                onDelete={setDeleteQueueId}
            />
        </div>
    </>
}

export default Editor;