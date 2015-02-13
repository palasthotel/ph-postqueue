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
		
		?>
		<div class="wrap">
			<h2>Postqueues</h2>
			
		</div>
		<?php
	}

}
