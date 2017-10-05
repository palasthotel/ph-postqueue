<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 12.08.16
 * Time: 16:55
 */

namespace Postqueue;


class Tools {
	/**
	 * Ajax constructor.
	 *
	 * @param Plugin $plugin plugin object
	 */
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		$this->store = $plugin->store;
		
		/**
		 * settings page
		 */
		add_action( 'admin_menu', array($this, 'tools_page') );
	}
	
	/**
	 * Register the menu page for gallery sharing
	 *
	 */
	public function tools_page()
	{
		add_submenu_page(
			'tools.php',
			'Postqueues',
			'Postqueues',
			apply_filters(Plugin::FILTER_POSTQUEUE_EDIT_CAPABILITY, 'manage_options'),
			'tools-postqueue',
			array( $this, 'render' )
		);
	}
	
	/**
	 *  renders tools page
	 */
	public function render()
	{
		/**
		 * Add css and javascript
		 */
		wp_enqueue_style(
			'postqueue-css',
			$this->plugin->url . 'css/ph-postqueue-editor.css',
			array( ),
			1,
			'all'
		);
		wp_enqueue_script(
			'postqueue',
			$this->plugin->url . 'js/ph-postqueue-editor.js',
			array( 'jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ),
			1,
			false
		);
		$store = $this->store;
		require $this->plugin->dir .'partials/ph-postqueue-editor.tpl.php';
	}
}