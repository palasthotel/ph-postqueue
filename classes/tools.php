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
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		$this->store = $plugin->store;
		
		/**
		 * settings page
		 */
		add_action( 'admin_menu', array( $this, 'tools_page' ) );
	}

	/**
	 * @return string
	 */
	public function getCapability(){
		return apply_filters( Plugin::FILTER_POSTQUEUE_EDIT_CAPABILITY, 'manage_options' );
	}

	/**
	 * Register the menu page for postqueue page
	 *
	 */
	public function tools_page()
	{
		add_submenu_page(
			'tools.php',
			'Postqueues',
			'Postqueues',
			$this->getCapability(),
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
			filemtime($this->plugin->dir."/css/ph-postqueue-editor.css"),
			'all'
		);
		wp_enqueue_script(
			'postqueue',
			$this->plugin->url . 'js/ph-postqueue-editor.js',
			array( 'jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ),
			filemtime($this->plugin->dir.'/js/ph-postqueue-editor.js'),
			false
		);
		wp_localize_script( 'postqueue', 'objectL10n', array(
    	'edit' => esc_html__( 'Edit', Plugin::DOMAIN ),
    	'delete' => esc_html__( 'Delete', Plugin::DOMAIN ),
    	'add_post' => esc_html__( 'Add post', Plugin::DOMAIN ),
    	'cancel' => esc_html__( 'Cancel', Plugin::DOMAIN ),
    	'post_title_or_id' => esc_html__( 'Post title or ID', Plugin::DOMAIN ),
    ) );
		$store = $this->store;
		require $this->plugin->dir .'partials/ph-postqueue-editor.tpl.php';
	}
}