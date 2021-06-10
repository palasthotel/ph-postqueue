import apiFetch from "@wordpress/api-fetch";

const getApiPath = (path) => PostQueue.rest_namespace + path;
const getPostsApiPath = () => getApiPath("/posts");
const getQueuesApiPath = () => getApiPath("/queues");
const getQueueApiPath = (id) => `${getQueuesApiPath()}/${id}`;
const getQueueItemsApiPath = (id) => `${getQueueApiPath(id)}/items`;
const getQueueItemApiPath = (id, itemId) => `${getQueueItemsApiPath(id)}/${itemId}`;

export const apiReadPosts = (search) => {
    return apiFetch({path: getPostsApiPath()+`?search=${search}`});
};

export const apiReadQueues = (search = "") => {
    let path = getQueuesApiPath();
    if (search.length > 0) {
        path += `?search=${search}`;
    }
    return apiFetch({path});
};

export const apiCreateQueue = (name) => {
    return apiFetch({
        path: getQueuesApiPath(),
        method: "POST",
        data: {name}
    });
}

export const apiReadQueue = (id) => {
    return apiFetch({path: getQueueApiPath(id)});
}

export const apiDeleteQueue = (id) => {
    return apiFetch({path: getQueueApiPath(id), method: "DELETE"});
}

export const apiCreateQueueItems = (id, items) => {
    return apiFetch({
        path: getQueueItemsApiPath(id),
        method: "POST",
        data: {items},
    })
}

export const apiDeleteQueueItem = (id, postId) => {
    return apiFetch({path: getQueueItemApiPath(id, postId), method: "DELETE"});
}