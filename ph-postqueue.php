<?php

namespace Postqueue;

/**
 * Create manually ordered postqueues
 *
 *
 * @wordpress-plugin
 * Plugin Name:       Postqueue
 * Description:       Create manually ordered postqueues
 * Version:           1.2.1
 * Author:            Palasthotel <rezeption@palasthotel.de> (Edward Bock, Jana Marie Eggebrecht)
 * Author URI:        https://palasthotel.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       postqueue
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Plugin {
  
  /**
	 * Domain for translation
	 */
	const DOMAIN = "postqueue";

	/**
	 * filters
	 */
	const FILTER_VIEWMODES = "postqueue_viewmodes";
	const FILTER_ADD_POSITION = "postqueue_add_position";
	const FILTER_POSTQUEUE_EDIT_CAPABILITY = "postqueue_edit_capability";
	const FILTER_POSTQUEUE_SEARCH_ORDER = "postqueue_store_search_order";
	
	/**
	 * actions
	 */
	const ACTION_POSTQUEUE_GRID_BOXES = "postqueue_grid_boxes";
	
	/**
	 * plugin path strings
	 */
	public $dir;
	public $url;
	/**
	 * @var \Postqueue\Store
	 */
	public $store;

	/**
	 * @var Plugin
	 */
	private static $instance;
	/**
	 * @return Plugin
	 */
	public static function instance() {
		if ( self::$instance == null ) {
			self::$instance = new Plugin();
		}
		return self::$instance;
	}
	
	/**
	 * construct grid plugin
	 */
	function __construct() {
		/**
		 * base paths
		 */
		$this->dir = plugin_dir_path( __FILE__ );
		$this->url = plugin_dir_url( __FILE__ );
		
		/**
		 * load translations
		 */
		load_plugin_textdomain( self::DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
		
		/**
		 * The code that runs during plugin activation.
		 */
		require_once plugin_dir_path( __FILE__ ) . 'classes/activator.php';
		$activator = new Activator();
		register_activation_hook( __FILE__, array( $activator, 'activate' ) );
		
		/**
		 * init parts of plugin
		 */
		require_once $this->dir . 'classes/store.php';
		$this->store = new Store();
		
		require_once $this->dir . 'classes/ajax.php';
		new Ajax( $this );
		
		require_once $this->dir . 'classes/tools.php';
		$this->tools = new Tools( $this );
		
		require_once $this->dir . 'classes/post.php';
		new Post( $this );
		
		require_once $this->dir . 'classes/shortcode.php';
		new Shortcode( $this );
		
		require_once $this->dir . 'classes/metabox.php';
		new MetaBox( $this );
		
		add_action( 'grid_load_classes', array( $this, 'grid_load_classes' ) );
		add_filter( 'grid_templates_paths', array( $this,'template_paths' ) );
	}
	
	/**
	 * add box classes
	 */
	public function grid_load_classes() {
		require $this->dir . "grid-boxes/grid-postqueue-box.php";
		/**
		 * add boxes that extend from postqueue box
		 */
		do_action( self::ACTION_POSTQUEUE_GRID_BOXES );
	}
	
	/**
	 * add grid templates suggestion path
	 * @param $paths
	 * @return array
	 */
	public function template_paths( $paths ) {
		$paths[] = dirname(__FILE__) . "/grid-templates";
		return $paths;
	}
	
	/**
	 * get viewmodes for box
	 * @return array
	 */
	public static function getViewmodes(){
		$viewmodes = array(
			array('key' => '', 'text' => __( 'Default', 'postqueue' ) ),
		);
		return apply_filters( Plugin::FILTER_VIEWMODES, $viewmodes );
	}
}
Plugin::instance();

require_once dirname(__FILE__) . "/public-functions.php";