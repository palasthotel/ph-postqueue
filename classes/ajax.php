<?php

namespace Postqueue;


class Ajax {
	/**
	 * @var Store
	 */
	public $store;
	
	/**
	 * Ajax constructor.
	 *
	 * @param Plugin $plugin plugin object
	 */
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		$this->store = $plugin->store;
		/**
		 * Ajax endpoint for adding a new queue
		 */
		add_action( 'wp_ajax_ph_postqueue_create_queue', array($this, 'create_queue') );
		/**
		 * Ajax endpoint for loading a queue
		 */
		add_action( 'wp_ajax_ph_postqueue_delete_queue', array($this, 'delete_queue') );
		/**
		 * Ajax endpoint for loading a queue
		 */
		add_action( 'wp_ajax_ph_postqueue_load_queue', array($this, 'load_queue') );
		/**
		 * Ajax endpoint for loading a queue
		 */
		add_action( 'wp_ajax_ph_postqueue_save_post_items', array($this, 'save_post_items') );
		/**
		 * Ajax endpoint for loading a queue
		 */
		add_action( 'wp_ajax_ph_postqueue_delete_post', array($this, 'delete_post') );
		/**
		 * Ajax endpoint for loading a queue
		 */
		add_action( 'wp_ajax_ph_postqueue_search_posts', array($this, 'search_posts') );
		
	}
	
	/**
	 * creates a new queue
	 */
	public function create_queue()
	{

		if(!current_user_can($this->plugin->tools->getCapability())) return;

		$name = sanitize_text_field($_POST["queue_name"]);
		
		$result = $this->store->create($name);
		
		/**
		 * action queue is created
		 */
		do_action("ph_postqueue_created", (object)array( "id" => $result->id, "slug" => $result->slug) );
		
		$this->return_ajax($result);
	}
	
	/**
	 * loads a single queues posts
	 */
	public function load_queue()
	{

		if(!current_user_can($this->plugin->tools->getCapability())) return;

		$queue_id = intval($_POST["queue_id"]);
		
		$result = $this->store->get_queue_by_id($queue_id);
		
		$this->return_ajax($result);
	}
	/**
	 * delete ajax function for queues
	 */
	public function delete_queue()
	{
		if(!current_user_can($this->plugin->tools->getCapability())) return;

		$result = (object)array();
		$queue_id = intval($_POST["queue_id"]);
		
		/**
		 * action before queue is deleted
		 */
		do_action("ph_postqueue_deleting", $queue_id);
		
		$this->store->delete_queue($queue_id);
		
	}
	
	public function save_post_items()
	{

		if(!current_user_can($this->plugin->tools->getCapability())) return;

		$result = (object)array();
		$result->queue_id = intval($_POST["queue_id"]);
		$result->items = $_POST["items"];
		
		$store = $this->store;
		$store->queue_clear($result->queue_id);
		
		$store->queue_add_all($result->queue_id, $result->items);
		
		$this->return_ajax($result);
	}
	
	/**
	 * delete ajax function
	 */
	public function delete_post()
	{

		if(!current_user_can($this->plugin->tools->getCapability())) return;

		$result = (object)array();
		$queue_id = intval($_POST["queue_id"]);
		$post_id = intval($_POST["post_id"]);
		$this->store->delete_queue_post($queue_id, $post_id);
	}
	
	public function search_posts()
	{

		if(!current_user_can($this->plugin->tools->getCapability())) return;

		$result = (object)array();
		$result->search = sanitize_text_field($_POST["search"]);
		
		global $wpdb;
		$results = $wpdb->get_results(
			"SELECT ID, post_title FROM ".$wpdb->prefix."posts".
			" WHERE".
			" (post_title LIKE '%".$result->search."%'".
			" AND (post_status = 'publish' OR post_status = 'future' ) )".
			" OR ID = '".$result->search."'".
			" ORDER BY ID DESC LIMIT 10"
		);
		
		$result->posts = array();
		foreach ($results as $index => $post) {
			$p = (object)array();
			$p->post_id = $post->ID;
			$p->post_title = $post->post_title;
			$result->posts[] = $p;
		}
		
		$this->return_ajax($result);
	}
	
	
	
	/**
	 * returns json for ajax calls
	 */
	private function return_ajax($result)
	{
		wp_send_json(array( 'result' => $result ));
	}
	
}