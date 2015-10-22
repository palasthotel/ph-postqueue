<?php


/**
 * The dashboard-specific functionality of the plugin.
 */
class PH_Postqueue_Admin {

	/**
	 * The ID of this plugin.
	 *
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 */
	public function __construct( $plugin_name, $version )
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * creates a new queue
	 */
	public function create_queue()
	{
		$name = sanitize_text_field($_GET["queue_name"]);

		$store = new PH_Postqueue_Store();
		$result = $store->create($name);

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
		$queue_id = intval($_GET["queue_id"]);

		$store = new PH_Postqueue_Store();
		$result = $store->get_queue_by_id($queue_id);

		$this->return_ajax($result);
	}
	/**
	 * delete ajax function for queues
	 */
	public function delete_queue()
	{
		$result = (object)array();
		$queue_id = intval($_GET["queue_id"]);

		/**
		 * action before queue is deleted
		 */
		do_action("ph_postqueue_deleting", $queue_id);

		$store = new PH_Postqueue_Store();
		$store->delete_queue($queue_id);

	}

	public function save_post_items()
	{
		$result = (object)array();
		$result->queue_id = intval($_GET["queue_id"]);
		$result->items = $_GET["items"];

		$store = new PH_Postqueue_Store();
		$store->queue_clear($result->queue_id);

		$store->queue_add_all($result->queue_id, $result->items);

		$this->return_ajax($result);
	}

	/**
	 * delete ajax function
	 */
	public function delete_post()
	{
		$result = (object)array();
		$queue_id = intval($_GET["queue_id"]);
		$post_id = intval($_GET["post_id"]);
		$store = new PH_Postqueue_Store();
		$store->delete_queue_post($queue_id, $post_id);
	}

	public function search_posts()
	{
		$result = (object)array();
		$result->search = sanitize_text_field($_GET["search"]);

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
	 * triggered when a post is deleted
	 */
	public function on_post_delete($post_id)
	{
		$store = new PH_Postqueue_Store();
		$store->clear_for_post_id($post_id);
	}

	/**
	 * returns json for ajax calls
	 */
	private function return_ajax($result)
	{
		print json_encode( array( 'result' => $result ) );
		die();
	}

	/**
	 * Register the menu page for gallery sharing
	 *
	 */
	public function tools_page()
	{
		add_submenu_page( 'tools.php', 'Postqueues', 'Postqueues', 'manage_options', 'tools-'.$this->plugin_name, array( $this, 'render_tools' ) );
	}

	/**
	 *  renders tools page
	 */
	public function render_tools()
	{
		$store = new PH_Postqueue_Store();
		/**
		 * Add css and javascript
		 */
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/ph-postqueue-editor.css',
			array( ),
			$this->version,
			'all'
		);
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/ph-postqueue-editor.js',
			array( 'jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ),
			$this->version,
			false
		);
		require plugin_dir_path( __FILE__ ) .'partials/ph-postqueue-editor.tpl.php';
	}

}
