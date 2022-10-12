<?php

namespace Postqueue;

use Palasthotel\WordPress\BlockX\Gutenberg;
use Postqueue\Blocks\Postqueue;

class BlockX extends Component\Component {

	function onCreate() {
		add_filter( 'blockx_add_templates_paths', function ( $paths ) {
			$paths[] = $this->plugin->path. "/templates/";
			return $paths;
		} );
		add_action( 'blockx_collect', function ( Gutenberg $gutenberg ) {
			$gutenberg->addBlockType( new Postqueue() );
		} );
	}
}
