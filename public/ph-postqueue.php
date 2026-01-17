<?php

namespace Postqueue;

use Postqueue\Component\Templates;

/**
 * Plugin Name:       Postqueue
 * Description:       Create manually ordered postqueues
 * Version:           1.5.0
 * Requires at least: 5.0
 * Tested up to:      6.0.2
 * Requires PHP:      7.4
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

require_once dirname( __FILE__ ) . "/vendor/autoload.php";

class Plugin extends Component\Plugin {

    public Store $store;
    public Templates $templates;
    public Ajax $ajax;
    public Editor $editor;
    public Post $post;
    public Shortcode $shortcode;
    public MetaBox $metaBox;
    public Grid $grid;
    public Assets $assets;
    public REST $rest;
    public BlockX $blockx;
    public Headless $headless;

	/**
	 * Domain for translation
	 */
	const DOMAIN = "postqueue";

	const THEME_PATH = "plugin-parts";
	const TEMPLATE_NAME = "postqueue.tpl.php";

	const SHORTCODE = "postqueue";

	const HANDLE_EDITOR_CSS = "postqueue-css";
	const HANDLE_EDITOR_JS = "postqueue-js";

	/**
	 * filters
	 */
	const FILTER_ADD_TEMPALTE_PATHS = "postqueue_add_template_paths";
	const FILTER_VIEWMODES = "postqueue_viewmodes";
	const FILTER_ADD_POSITION = "postqueue_add_position";
	const FILTER_POSTQUEUE_EDIT_CAPABILITY = "postqueue_edit_capability";
	const FILTER_POSTQUEUE_SEARCH_ORDER = "postqueue_store_search_order";
	const FILTER_POSTQUEUE_LIMITER = "postqueue_limiter";
	/**
	 * actions
	 */
	const ACTION_POSTQUEUE_GRID_BOXES = "postqueue_grid_boxes";

	/**
	 * construct grid plugin
	 */
	function onCreate() {

		$this->loadTextdomain( Plugin::DOMAIN, "languages" );

		$this->store = new Store();

		$this->templates = new Templates( $this );
		$this->templates->useThemeDirectory( Plugin::THEME_PATH );
		$this->templates->useAddTemplatePathsFilter( Plugin::FILTER_ADD_TEMPALTE_PATHS );

		$this->assets = new Assets( $this );

		$this->ajax = new Ajax( $this );
		$this->rest = new REST( $this );

		$this->editor    = new Editor( $this );
		$this->blockx    = new BlockX( $this );
		$this->headless = new Headless($this);
		$this->post      = new Post( $this );
		$this->shortcode = new Shortcode( $this );
		$this->metaBox   = new MetaBox( $this );
		$this->grid      = new Grid( $this );

	}

	public function onSiteActivation() {
		parent::onSiteActivation();
		$this->store->createTables();
	}

	/**
	 * get view modes for box
	 * @return array
	 */
	public static function getViewmodes(): array {
		return apply_filters( Plugin::FILTER_VIEWMODES, [
			[ 'key' => '', 'text' => __( 'Default', 'postqueue' ) ],
		] );
	}

}

Plugin::instance();

require_once dirname( __FILE__ ) . "/public-functions.php";
require_once dirname( __FILE__ ) . "/deprecated.php";
