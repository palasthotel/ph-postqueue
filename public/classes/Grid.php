<?php


namespace Postqueue;


class Grid extends Component\Component {

	function onCreate() {
		add_action( 'grid_load_classes', array( $this, 'grid_load_classes' ) );
		add_filter( 'grid_templates_paths', array( $this, 'template_paths' ) );
	}

	/**
	 * add box classes
	 */
	public function grid_load_classes() {
		require $this->plugin->path . "/grid-boxes/grid-postqueue-box.php";
		/**
		 * add boxes that extend from postqueue box
		 */
		do_action( Plugin::ACTION_POSTQUEUE_GRID_BOXES );
	}

	/**
	 * add grid templates suggestion path
	 *
	 * @param $paths
	 *
	 * @return array
	 */
	public function template_paths( $paths ) {
		$paths[] = $this->plugin->path . "/grid-templates";

		return $paths;
	}
}