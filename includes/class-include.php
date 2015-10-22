<?php
/**
 * The core plugin class.
 */
class PH_Postqueue {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {

		$this->plugin_name = 'ph-postqueue';
		$this->version = '1.0.5';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_grid_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ph-postqueue-store.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-public.php';

		$this->loader = new PH_Postqueue_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 * 
	 */
	private function set_locale() {

		$plugin_i18n = new PH_Postqueue_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 * 
	 */
	private function define_admin_hooks() {

		$plugin_admin = new PH_Postqueue_Admin( $this->get_plugin_name(), $this->get_version() );

		/**
		 * settings page
		 */
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'tools_page' );
		/**
		 * Ajax endpoint for adding a new queue
		 */
		$this->loader->add_action( 'wp_ajax_ph_postqueue_create_queue', $plugin_admin, 'create_queue' );
		/**
		 * Ajax endpoint for loading a queue
		 */
		$this->loader->add_action( 'wp_ajax_ph_postqueue_delete_queue', $plugin_admin, 'delete_queue' );
		/**
		 * Ajax endpoint for loading a queue
		 */
		$this->loader->add_action( 'wp_ajax_ph_postqueue_load_queue', $plugin_admin, 'load_queue' );
		/**
		 * Ajax endpoint for loading a queue
		 */
		$this->loader->add_action( 'wp_ajax_ph_postqueue_save_post_items', $plugin_admin, 'save_post_items' );
		/**
		 * Ajax endpoint for loading a queue
		 */
		$this->loader->add_action( 'wp_ajax_ph_postqueue_delete_post', $plugin_admin, 'delete_post' );
		/**
		 * Ajax endpoint for loading a queue
		 */
		$this->loader->add_action( 'wp_ajax_ph_postqueue_search_posts', $plugin_admin, 'search_posts' );
		/**
		 * registers delete_post action that is triggert before post is deleted
		 */
		$this->loader->add_action( 'delete_post', $plugin_admin, 'on_post_delete' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 * 
	 */
	private function define_public_hooks() {

		$plugin_public = new PH_Postqueue_Public( $this->get_plugin_name(), $this->get_version() );

	}

	/**
	 * all grid functions
	 */
	private function define_grid_hooks(){
		$this->loader->add_action('grid_load_classes', $this, 'grid_load_classes');
	}
	public function grid_load_classes(){
		require dirname(__FILE__)."/../grid-boxes/grid-postqueue-box.php";
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 * 
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 * 
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 * 
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 * 
	 */
	public function get_version() {
		return $this->version;
	}

}
