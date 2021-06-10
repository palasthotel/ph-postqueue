const SearchOrCreate = ({name, onChangeName, onCreate}) => {
    const handleKeyPress = (e) => {
        if (e.keyCode === 13 && name !== "") {
            onCreate();
        }
    }
    return <>
        <div className="queue-name">
            <input
                className="ph-postqueue-name"
                type="text"
                placeholder="Queue suchen / erstellen"
                value={name}
                onChange={e => onChangeName(e.target.value)}
                onKeyDown={handleKeyPress}
            />
        </div>
        {name && <div className="ph-new-queue" onClick={() => {
            onCreate(name);
        }}>
            <p>Create »<span className="queue-name">{name}</span>«</p>
        </div>}

    </>
}

export default SearchOrCreate;