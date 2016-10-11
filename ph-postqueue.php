<?php

/**
 * Create manually ordered postqueues
 *
 *
 * @wordpress-plugin
 * Plugin Name:       Postqueue
 * Description:       Create manually ordered postqueues
 * Version:           1.1.3
 * Author:            PALASHOTEL by Edward Bock
 * Author URI:        http://palasthotel.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       postqueue
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Postqueue\Activator;

class Postqueue{
	/**
	 * filters
	 */
	const FILTER_VIEWMODES = "postqueue_viewmodes";
	
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
	 * construct grid plugin
	 */
	function __construct(){
		/**
		 * base paths
		 */
		$this->dir = plugin_dir_path(__FILE__);
		$this->url = plugin_dir_url(__FILE__);
		
		/**
		 * load translations
		 */
		load_plugin_textdomain( 'postqueue', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
		
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
		$this->store = new \Postqueue\Store($this);
		
		require_once $this->dir . 'classes/ajax.php';
		new \Postqueue\Ajax($this);
		
		require_once $this->dir . 'classes/tools.php';
		new \Postqueue\Tools($this);
		
		require_once $this->dir . 'classes/post.php';
		new \Postqueue\Post($this);
		
		require_once $this->dir . 'classes/shortcode.php';
		new \Postqueue\Shortcode($this);
		
		add_action('grid_load_classes', array($this, 'grid_load_classes') );
		add_filter("grid_templates_paths", array($this,"template_paths") );
	}
	
	/**
	 * add box classes
	 */
	public function grid_load_classes(){
		require $this->dir."grid-boxes/grid-postqueue-box.php";
		/**
		 * add boxes that extend from postqueue box
		 */
		do_action(self::ACTION_POSTQUEUE_GRID_BOXES);
	}
	
	/**
	 * add grid templates suggestion path
	 * @param $paths
	 * @return array
	 */
	public function template_paths($paths){
		$paths[] = dirname(__FILE__)."/templates";
		return $paths;
	}
	
	/**
	 * get viewmodes for box
	 * @return array
	 */
	public static function getViewmodes(){
		$viewmodes = array(
			array('key' => '', 'text' => t('Default') ),
		);
		return apply_filters(Postqueue::FILTER_VIEWMODES,$viewmodes);
	}
}

global $postqueue;
$postqueue = new Postqueue();

/**
 * for backward compatibility
 */
class PH_Postqueue_Store extends \Postqueue\Store {
}