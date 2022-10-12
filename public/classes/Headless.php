<?php

namespace Postqueue;


use Palasthotel\WordPress\Headless\Model\BlockPreparations;
use Postqueue\Headless\PostqueueBlockPreparation;

class Headless extends Component\Component {

	public function onCreate() {
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
	}

	public function plugins_loaded() {
		if ( ! class_exists( 'Palasthotel\WordPress\Headless\Plugin' ) ) {
			return;
		}
		add_action(
			\Palasthotel\WordPress\Headless\Plugin::ACTION_REGISTER_BLOCK_PREPARATION_EXTENSIONS,
			[ $this, 'block_preparation_extensions' ]
		);
	}

	public function block_preparation_extensions( BlockPreparations $extensions ) {
		$extensions->add(new PostqueueBlockPreparation());
	}
}
