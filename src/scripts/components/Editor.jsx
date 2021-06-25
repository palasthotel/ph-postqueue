import {useState} from "@wordpress/element";
import {useQueues} from "../hooks/use-queues";
import SearchOrCreate from "./SearchOrCreate.jsx";
import QueueList from "./QueueList.jsx";
import QueueEditor from "./QueueEditor.jsx";
import LoadingLine from "./LoadingLine.jsx";

const Editor = () => {
    const {i18n} = PostQueue;
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
            queueName={selectedQueue.name}
            onGoBack={()=>setSelectedQueueId("")}
        />
    }

    const deleteQueueItem = queues.find(q=>q.id === deleteQueueId);

    return <>
        <h3>Postqueues</h3>
        <div className="ph-postqueues-widget">
            <SearchOrCreate
                queues={queues}
                name={name}
                onChangeName={setName}
                onCreate={handleCreateQueue}
            />

            {isLoading && <LoadingLine />}

            {!isLoading && deleteQueueItem && <div className="delete-control">
                <p>{i18n.confirm_delete}<br/><strong>{deleteQueueItem.name}</strong></p>
                <button
                    className="button-delete button button-secondary"
                    onClick={()=>deleteQueue(deleteQueueItem.id)}
                >
                    {i18n.confirm_delete_yes}
                </button>
                <button
                    className="button button-secondary"
                    onClick={()=> setDeleteQueueId("")}
                >
                    {i18n.confirm_delete_no}
                </button>
            </div>}
            <QueueList
                items={queues.filter(q=> name === "" || q.name.toLowerCase().includes(name.toLowerCase()))}
                onEdit={setSelectedQueueId}
                onDelete={setDeleteQueueId}
            />
        </div>
    </>
}

export default Editor;