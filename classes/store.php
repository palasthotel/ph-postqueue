<?php

namespace Postqueue;
/**
 *	Postqueue Store class
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
	 * @return  array queues
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
	 * returns queue by id
	 */
	public function get_queue_by_id($qid)
	{
		return $this->get_queue('queue_id',$qid);
	}

	/**
	 * returns queue by slug
	 */
	public function get_queue_by_slug($slug)
	{
		return $this->get_queue('slug', $slug);

	}
	private function get_queue($key, $value){

		global $wpdb;
		$query = "";
		$query.= "SELECT name, slug, contents.id as cid, queue_id, post_id, position, title_overwrite as title FROM";
		$query.=" ".$wpdb->prefix."ph_postqueues queue LEFT JOIN ".$wpdb->prefix."ph_postqueue_contents contents";
		$query.= " ON (queue.id = contents.queue_id)";
		$query.=" WHERE $key = '$value'";
		$query.=" ORDER BY position ASC";

		$results = $wpdb->get_results($query);
		for($i = 0; $i < count($results); $i++) {
			if ( FALSE === get_post_status( $results[$i]->post_id )  || $results[$i]->post_id == null ) {
				unset($results[$i]);
				continue;
			}
			$pid = $results[$i]->post_id;
			if($results[$i]->title != ""){
				$results[$i]->post_title = $results[$i]->title;
			} else{
				$results[$i]->post_title = get_the_title($pid);
			}
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

	public function queue_add_all_with_title($qid, $post_ids, $titles)
	{

		for ($i=0; $i < count($post_ids) ; $i++) {
			$this->queue_add($qid, $post_ids[$i], $i, $titles[$i]);
		}
	}

	public function queue_add($queue_id, $post_id, $position, $title = "")
	{
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix."ph_postqueue_contents",
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