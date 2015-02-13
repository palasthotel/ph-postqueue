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
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * creates a new queue
	 */
	public function create_queue(){
		$name = sanitize_text_field($_GET["queue_name"]);

		$store = new PH_Postqueue_Store();
		$result = $store->create($name);

		$this->return_ajax($result);
	}

	/**
	 * loads a single queues posts
	 */
	public function load_queue(){
		$queue_id = intval($_GET["queue_id"]);

		$store = new PH_Postqueue_Store();
		$result = $store->get_queue_by_id($queue_id);

		$this->return_ajax($result);
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
			array( 'jquery', 'jquery-ui-autocomplete' ),
			$this->version,
			false
		);
		require plugin_dir_path( __FILE__ ) .'partials/ph-postqueue-editor.tpl.php';		
	}

}
