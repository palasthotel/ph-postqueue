import {useEffect, useState} from '@wordpress/element'
import {apiCreateQueue, apiDeleteQueue, apiReadPosts, apiReadQueue, apiReadQueues} from "../store/api";

export const useQueues = () => {

    const [isLoading, setIsLoading] = useState(false);
    const [items, setItems] = useState([]);

    useEffect(() => {
        setIsLoading(true);
        apiReadQueues().then(queues => {
            setIsLoading(false);
            setItems(queues);
        });
    }, []);

    return {
        queues: items,
        createQueue: (name) => {
            setIsLoading(true);
            apiCreateQueue(name).then(queue=>{
                setIsLoading(false);
                setItems([
                    queue,
                    ...items,
                ]);
            });
        },
        deleteQueue: (id)=>{
            setIsLoading(true);
            apiDeleteQueue(id).then(_=>{
                setIsLoading(false);
                setItems(items.filter(item=>item.id !== id));
            });
        },
        isLoading
    }
}

export const useQueueItems = (queueId)=>{

    const [isLoading, setIsLoading] = useState(false);
    const [items, setItems] = useState([]);

    useEffect(()=>{
        setIsLoading(true);
        apiReadQueue(queueId).then(items=>{
            setIsLoading(false);
            setItems(items);
        })
    }, [queueId]);

    return {
        items,
        isLoading,
    }
}

export const useQueryPosts = (query) => {

    const [isLoading, setIsLoading] = useState(false);
    const [posts, setPosts] = useState([]);

    useEffect(()=>{
        let abort = false;
        if(query === ""){
            setIsLoading(false);
            setPosts([]);
            return;
        }
        setIsLoading(true);
        apiReadPosts(query).then(response=>{
            if(abort){
                return;
            }
            setIsLoading(false);
            setPosts(response.posts);
        });
        return ()=>{
            abort = true;
        }
    }, [query]);

    return posts;
}