<?php

namespace Postqueue;
/**
 *    Postqueue Store class
 */
class Store
{

    /**
     * @var null|array
     */
    private $queues;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct()
    {
        $this->queues = null;
    }

    /**
     * creates a new queue
     *
     * @param String $name
     */
    public function create($name)
    {
        global $wpdb;
        $result = (object)array();
        $result->name = $name;
        $result->slug = sanitize_title($result->name);
        $result->success = $wpdb->insert(
            $wpdb->prefix . 'ph_postqueues',
            array(
                'name' => $result->name,
                'slug' => $result->slug,
            ),
            array(
                '%s',
                '%s',
            )
        );
        $result->id = $wpdb->insert_id;
        return $result;

    }

    /**
     * returns all postqueues
     *
     * @return  array queues
     */
    public function get_queues()
    {
        if ($this->queues == null) {
            $this->queues = $this->search();
        }
        return $this->queues;
    }

    /**
     * returns queue by id
     *
     * @return object queue
     */
    public function get_queue_by_id($qid)
    {
        return $this->get_queue('queue_id', $qid);
    }

    /**
     * returns queue by slug
     *
     * @return object queue
     */
    public function get_queue_by_slug($slug)
    {
        return $this->get_queue('slug', $slug);
    }

    /**
     * helper function for returning a queue
     *
     * @return object queue
     */
    private function get_queue($key, $value)
    {
        global $wpdb;
        $query = "";
        $query .= "SELECT name, slug, contents.id as cid, queue_id, post_id, position, title_overwrite as title FROM";
        $query .= " " . $wpdb->prefix . "ph_postqueues queue LEFT JOIN " . $wpdb->prefix . "ph_postqueue_contents contents";
        $query .= " ON (queue.id = contents.queue_id)";
        $query .= " WHERE $key = '$value'";
        $query .= " ORDER BY position ASC";

        $results = $wpdb->get_results($query);
        for ($i = 0; $i < count($results); $i++) {
            if (FALSE === get_post_status($results[$i]->post_id) || $results[$i]->post_id == null) {
                unset($results[$i]);
                continue;
            }
            $pid = $results[$i]->post_id;
            if ($results[$i]->title != "") {
                $results[$i]->post_title = $results[$i]->title;
            } else {
                $results[$i]->post_title = get_the_title($pid);
            }
        }
        return $results;
    }

    /**
     * clears all contents of a queue
     *
     * @param int $queue_id
     * @return void
     */
    public function queue_clear($queue_id)
    {
        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix . "ph_postqueue_contents",
            array("queue_id" => $queue_id),
            array("%d")
        );
    }

    public function queue_add_all($qid, $post_ids)
    {
        foreach ($post_ids as $position => $post_id) {
            $this->queue_add($qid, $post_id, $position);
        }
    }

    public function queue_add_all_with_title($qid, $post_ids, $titles)
    {
        for ($i = 0; $i < count($post_ids); $i++) {
            $this->queue_add($qid, $post_ids[$i], $i, $titles[$i]);
        }
    }

    public function queue_add($queue_id, $post_id, $position = 'last', $title = "")
    {

        // add posts to queue without knowing the exact position
        global $wpdb;

        if ('last' === $position) {
            $position = $this->get_last_position_of_queue($queue_id);
            $position++;
        } elseif ('first' === $position) {
            $position = 0;

            // increase the position of every other item in the queue by 1
            $table = $wpdb->prefix . "ph_postqueue_contents";

            $sql = $wpdb->prepare("UPDATE $table SET position=position+1 WHERE queue_id=%d ORDER BY position desc",
                $queue_id
            );

            $wpdb->query($sql);
        }

        $wpdb->insert(
            $wpdb->prefix . "ph_postqueue_contents",
            array(
                'queue_id' => $queue_id,
                'post_id' => $post_id,
                'position' => $position,
                'title_overwrite' => $title,
            ),
            array(
                "%d",
                "%d",
                "%d",
                '%s',
            )
        );

        // limit the length of the queue
        $table = $wpdb->prefix . "ph_postqueue_contents";
        $global_limit_for_postqueue = apply_filters(Plugin::FILTER_POSTQUEUE_LIMITER, -1);

        if ($global_limit_for_postqueue > 0) {
            $sql = $wpdb->prepare("DELETE FROM $table  WHERE position>%d AND queue_id=%d",
                $global_limit_for_postqueue - 1, $queue_id
            );

            $wpdb->query($sql);
        }

    }

    /**
     * deletes all contents of a queue and the queue itself
     *
     * @param int $queue_id
     * @return void
     */
    public function delete_queue($queue_id)
    {
        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix . "ph_postqueue_contents",
            array(
                "queue_id" => $queue_id,
            ),
            array(
                "%d",
            )
        );
        $wpdb->delete(
            $wpdb->prefix . "ph_postqueues",
            array(
                "id" => $queue_id,
            ),
            array(
                "%d",
            )
        );
    }

    /**
     * removes all contents of given post_id in queue
     *
     * @param int $queue_id
     * @param int $post_id
     * @return void
     */
    public function delete_queue_post($queue_id, $post_id)
    {
        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix . "ph_postqueue_contents",
            array(
                "post_id" => $post_id,
                "queue_id" => $queue_id,
            ),
            array(
                "%d",
                "%d",
            )
        );
    }

    /**
     * deletes all queue contents of the deleted post id
     */
    public function clear_for_post_id($post_id)
    {
        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix . "ph_postqueue_contents",
            array(
                "post_id" => $post_id,
            ),
            array(
                "%d",
            )
        );
    }

    /**
     * search queue
     *
     * @param string $name
     *
     * @return array
     */
    public function search($name = "")
    {
        global $wpdb;
        $query = "";
        $query .= "SELECT * FROM " . $wpdb->prefix . "ph_postqueues";
        $query .= " WHERE name LIKE '%" . $name . "%'";

        $order = apply_filters(Plugin::FILTER_POSTQUEUE_SEARCH_ORDER, 'id ASC');
        $query .= " ORDER BY $order";

        $result = $wpdb->get_results($query);
        return $result;
    }

    /**
     * adds a post to a postqueue
     *
     * @return true|false
     */
    public function add_post_to_queue($post_id, $queue_id)
    {
        $this->queue_add($queue_id, $post_id);
    }

    /**
     * removes a post to a postqueue
     *
     * @return true|false
     */
    public function remove_post_from_queue($post_id, $queue_id)
    {
        // @todo not sure if this is enough, because what about position?
        $this->delete_queue_post($queue_id, $post_id);
    }

    /**
     * returns list of postqueues a given post is in
     *
     * @return array
     */
    public function get_queues_for_post($post_id)
    {
        global $wpdb;
        $query = "";
        $query .= "SELECT queue_id FROM " . $wpdb->prefix . "ph_postqueue_contents";
        $query .= " WHERE post_id = '" . $post_id . "'";
        $result = $wpdb->get_results($query);
        $postqueues = array();
        foreach ($result as $row) {
            $postqueues[] = $this->get_queue_by_id($row->queue_id);
        }
        return $postqueues;
    }

    /**
     * checks if post is in a given postqueue
     */
    public function is_post_in_queue($post_id, $queue_id)
    {
        global $wpdb;
        $query = "";
        $query .= "SELECT * FROM " . $wpdb->prefix . "ph_postqueue_contents";
        $query .= " WHERE post_id = '" . $post_id . "'";
        $query .= " AND queue_id = '" . $queue_id . "'";
        $result = $wpdb->get_results($query);
        if (count($result) > 0) {
            return true;
        }
        return false;
    }

    /**
     * return the last free position in a queue
     *
     * @param int $queue_id
     * @return int $position
     */
    public function get_last_position_of_queue($queue_id)
    {
        global $wpdb;
        $query = "";
        $query .= "SELECT MAX(position) FROM " . $wpdb->prefix . "ph_postqueue_contents";
        $query .= " WHERE queue_id = '" . $queue_id . "'";
        return $wpdb->get_var($query);
    }
}

?>