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

	/**
	 * serach queue
	 */
	public function search($name = ""){
		global $wpdb;
		$query = "";
		$query.= "SELECT * FROM ".$wpdb->prefix."ph_postqueues";
		$query.=" WHERE name LIKE '%".$name."%'";
		$result = $wpdb->get_results($query);
		return $result;
	}
}
?>