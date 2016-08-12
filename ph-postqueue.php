<?php

/**
 * Create manually ordered postqueues
 *
 *
 * @wordpress-plugin
 * Plugin Name:       Postqueue
 * Description:       Create manually ordered postqueues
 * Version:           1.0.7
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

class Postqueue{
	public $dir;
	public $url;
	/**
	 * @var \PH_Postqueue_Store
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
		 * init parts of plugin
		 */
		require_once $this->dir . 'classes/postqueue-store.php';
		$this->store = new PH_Postqueue_Store($this);
		
		require_once $this->dir . 'classes/ajax.php';
		new \Postqueue\Ajax($this);
		
		require_once $this->dir . 'classes/post.php';
		new \Postqueue\Post($this);
		
		add_action('grid_load_classes', $this, 'grid_load_classes');
	}
	
	public function grid_load_classes(){
		require $this->dir."grid-boxes/grid-postqueue-box.php";
	}
}


/**
 * The code that runs during plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'classes/activator.php';

/**
 * The code that runs during plugin deactivation.
 */
require_once plugin_dir_path( __FILE__ ) . 'classes/deactivator.php';

/** This action is documented in includes/class-activator.php */
register_activation_hook( __FILE__, array( 'PH_Postqueue_Activator', 'activate' ) );

/** This action is documented in includes/class-deactivator.php */
register_deactivation_hook( __FILE__, array( 'PH_Postqueue_Deactivator', 'deactivate' ) );



/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-include.php';

/**
 * Begins execution of the plugin.
 */
function run_ph_postqueue() {

	$plugin = new PH_Postqueue();
	$plugin->run();

}
run_ph_postqueue();