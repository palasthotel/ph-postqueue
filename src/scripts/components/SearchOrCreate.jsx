const SearchOrCreate = ({name = "", onChangeName, onCreate}) => {
    const {i18n} = PostQueue;
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
                placeholder={i18n.search_or_create}
                value={name}
                onChange={e => onChangeName(e.target.value)}
                onKeyDown={handleKeyPress}
            />
        </div>
        {name !== "" && <div className="ph-new-queue" onClick={() => {
            onCreate(name);
        }}>
            <p>{i18n.create} »<span className="queue-name">{name}</span>«</p>
        </div>}

    </>
}

export default SearchOrCreate;