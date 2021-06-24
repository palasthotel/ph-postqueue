
const QueueList = (
    {
        items,
        onEdit,
        onDelete,
    }
)=> <ul className="queues-list">
    {items.map(({id, name, slug})=> <li
        key={id}
        className="queue"
    >
        <div className="queue-name">{name}</div>
        <div className="queue-controls">
            [{slug}]
            |
            <a href="#" onClick={()=>onEdit(id)}>Edit</a>
            |
            <a href="#" onClick={()=>onDelete(id)}>Delete</a>
        </div>
    </li>)}
</ul>;

export default QueueList;