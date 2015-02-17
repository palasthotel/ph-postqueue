<?php
/**
 *	Postqueue Store class
 */
class PH_Postqueue_Store
{

	/**
	 * postqueues
	 */
	private $queues;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		$this->queues = null;
	}

	/**
	 * creates a new queue
	 */
	public function create($name)
	{
		global $wpdb;
		$result = (object) array();
		$result->name = $name;
		$result->slug = sanitize_title($result->name);
		$result->success = $wpdb->insert(
			$wpdb->prefix.'ph_postqueues',
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
	 * Adds a single relation
	 * @return  queues array
	 */
	public function get_queues()
	{
		if($this->queues == null)
		{
			$this->queues = $this->search();
		}
		return $this->queues;
	}

	/**
	 * returns queue by slug
	 */
	public function get_queue_by_id($qid)
	{
		global $wpdb;
		$query = "";
		$query.= "SELECT * FROM ".$wpdb->prefix."ph_postqueue_contents";
		$query.=" WHERE queue_id = ".$qid;
		$query.=" ORDER BY position ASC";
		$results = $wpdb->get_results($query);
		for($i = 0; $i < count($results); $i++) {
			$pid = $results[$i]->post_id;
			$results[$i]->post_title = get_the_title($pid);
		}
		return $results;
	}

	public function queue_clear($queue_id)
	{
		global $wpdb;
		$wpdb->delete(
			$wpdb->prefix."ph_postqueue_contents",
			array( "queue_id" => $queue_id ),
			array( "%d" )
		);
	}

	public function queue_add_all($qid, $post_ids)
	{
		foreach ($post_ids as $position => $post_id) {
			$this->queue_add($qid, $post_id, $position);
		}
	}

	public function queue_add($queue_id, $post_id, $position)
	{
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix."ph_postqueue_contents",
			array(
				'queue_id' => $queue_id,
				'post_id' => $post_id,
				'position' => $position,
			),
			array(
				"%d",
				"%d",
				"%d",
			)
		);
	}

	public function delete_queue($queue_id)
	{
		global $wpdb;
		$wpdb->delete(
			$wpdb->prefix."ph_postqueue_contents",
			array(
				"queue_id" => $queue_id,
			),
			array(
				"%d",
			)
		);
		$wpdb->delete(
			$wpdb->prefix."ph_postqueues",
			array(
				"id" => $queue_id,
			),
			array(
				"%d",
			)
		);
	}

	public function delete_queue_post($queue_id, $post_id)
	{
		global $wpdb;
		$wpdb->delete(
			$wpdb->prefix."ph_postqueue_contents",
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
	 * delets all queue contents of the deleted post id
	 */
	public function clear_for_post_id($post_id)
	{
		global $wpdb;
		$wpdb->delete(
			$wpdb->prefix."ph_postqueue_contents",
			array(
				"post_id" => $post_id,
			),
			array(
				"%d",
			)
		);
	}

	/**
	 * serach queue
	 */
	public function search($name = ""){
		global $wpdb;
		$query = "";
		$query.= "SELECT * FROM ".$wpdb->prefix."ph_postqueues";
		$query.=" WHERE name LIKE '%".$name."%'";
		$query.=" ORDER BY id ASC";
		$result = $wpdb->get_results($query);
		return $result;
	}
}
?>