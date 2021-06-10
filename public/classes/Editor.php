<?php

namespace Postqueue;

use Postqueue\Component\Component;

class Editor extends Component {

	public function onCreate() {
		/**
		 * settings page
		 */
		add_action( 'admin_menu', array( $this, 'tools_page' ) );
	}

	/**
	 * @return string
	 */
	public function getCapability() {
		return apply_filters( Plugin::FILTER_POSTQUEUE_EDIT_CAPABILITY, 'manage_options' );
	}

	/**
	 * Register the menu page for postqueue page
	 *
	 */
	public function tools_page() {
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
	public function render() {
		wp_enqueue_style( Plugin::HANDLE_EDITOR_CSS );
		wp_enqueue_script( Plugin::HANDLE_EDITOR_JS );
		$store = $this->plugin->store;
		require $this->plugin->path . 'partials/ph-postqueue-editor.tpl.php';
	}
}