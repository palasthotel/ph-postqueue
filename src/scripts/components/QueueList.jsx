
const QueueList = (
    {
        items,
        onEdit,
        onDelete,
    }
)=> {
    const {i18n} = PostQueue;
    return <ul className="queues-list">
        {items.map(({id, name, slug}) => <li
            key={id}
            className="queue"
        >
            <div className="queue-name">{name}</div>
            <div className="queue-controls">
                [{slug}]
                |
                <a href="#" onClick={() => onEdit(id)}>{i18n.edit}</a>
                |
                <a href="#" onClick={() => onDelete(id)}>{i18n.delete}</a>
            </div>
        </li>)}
    </ul>;
}

export default QueueList;