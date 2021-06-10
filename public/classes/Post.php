<?php

namespace Postqueue;


use Postqueue\Component\Component;

class Post extends Component {

	function onCreate() {
		/**
		 * registers delete_post action that is triggert before post is deleted
		 */
		add_action( 'delete_post', array( $this, 'on_post_delete' ) );
	}

	/**
	 * triggered when a post is deleted
	 *
	 * @param $post_id int of post
	 */
	public function on_post_delete( $post_id ) {
		$this->plugin->store->clear_for_post_id( $post_id );
	}
}