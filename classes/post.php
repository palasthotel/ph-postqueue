<?php

namespace Postqueue;


class Post {
	/**
	 * @var Store
	 */
	public $store;
	
	/**
	 * Post constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct(Plugin $plugin) {
		$this->store = $plugin->store;
		/**
		 * registers delete_post action that is triggert before post is deleted
		 */
		add_action( 'delete_post', array($this, 'on_post_delete') );
	}
	
	/**
	 * triggered when a post is deleted
	 *
	 * @param $post_id int of post
	 */
	public function on_post_delete($post_id)
	{
		$this->store->clear_for_post_id($post_id);
	}
}