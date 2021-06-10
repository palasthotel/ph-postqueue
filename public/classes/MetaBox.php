<?php

namespace Postqueue;

class MetaBox extends Component\Component {

	
	private $screens;

	public function onCreate() {
		add_action( "init", array( $this, "init" ) );
	}

	/**
	 *
	 */
	function init() {
		
		$this->screens = get_post_types( array('public' => true) ); //@todo could get a setting page where to choose for which post_types postqueues should be available
		/**
		* registers add_meta_boxes action that adds metaboxes to post edit
		*/
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		
		/**
		* register ajax callbacks for this metabox
		*/
		add_action( 'wp_ajax_postqueue_add_post', array( $this, 'ajax_callback_add_post' ) );
		add_action( 'wp_ajax_postqueue_remove_post', array( $this, 'ajax_callback_remove_post' ) );
	}
	
	/**
	* Hooks into WordPress' add_meta_boxes function.
	* Goes through screens (post types) and adds the meta box.
	*/
	public function add_meta_boxes() {

		if(!current_user_can($this->plugin->editor->getCapability())) return;
		
		foreach ( $this->screens as $screen ) {
			add_meta_box(
				'postqueue',
				__( 'Postqueue', 'postqueue' ),
				array( $this, 'render' ),
				$screen,
				'advanced',
				'default'
			);
		}
	}
	
	/**
	* Generates the HTML for the meta box
	* 
	* @param object $post WordPress post object
	*/
	public function render( $post ) {
		/**
		* Add css and javascript
		*/
		wp_enqueue_style(
			'postqueue-metabox-css',
			$this->plugin->url . 'css/postqueue-metabox.css',
			array( ),
			1,
			'all'
		);
		wp_enqueue_script(
			'postqueue-metabox',
			$this->plugin->url . 'js/postqueue-metabox.js',
			array( 'jquery' ),
			1,
			false
		);
		wp_localize_script( 'postqueue-metabox', 'PostqueueMetaBoxL10n', array(
			'postremoved' => esc_html__( 'Post successfully removed from postqueue.', Plugin::DOMAIN ),
			'postadded' => esc_html__( 'Post successfully added to postqueue.', Plugin::DOMAIN ),
			'pleasechoose' => esc_html__( 'Please choose a postqueue!', Plugin::DOMAIN ),
			'erroroccured' => esc_html__( 'An error occured while sending the request. Please try again later.', Plugin::DOMAIN ),
			'removepostfromthispostqueue' => esc_html__( 'Remove post from this postqueue.', Plugin::DOMAIN ),
			'notstoredyet' => esc_html__( 'This post is not saved in any postqueue yet. You can add it to one below.', Plugin::DOMAIN )
		));
		$store = $this->plugin->store;
		require $this->plugin->path .'partials/postqueue-metabox.tpl.php';
	}
	
	/**
	* Callback function for the add post action
	*/
	function ajax_callback_add_post() {
		$post_id = intval( $_POST['postid'] );
		$queue_id = intval( $_POST['queueid'] );
		
		$position = \apply_filters(Plugin::FILTER_ADD_POSITION, null);
		
		if(in_array($position, ['first', 'last'])){
			$this->plugin->store->queue_add( $queue_id, $post_id, $position );
		}else{
			$this->plugin->store->add_post_to_queue( $post_id, $queue_id );
		}
		
		echo "Postqueue ID: " . $queue_id;
		wp_die(); // this is required to terminate immediately and return a proper response
	}
	
	/**
	* Callback function for the remove post action
	*/
	function ajax_callback_remove_post() {
		$post_id = intval( $_POST['postid'] );
		$queue_id = intval( $_POST['queueid'] );
		$this->plugin->store->remove_post_from_queue( $post_id, $queue_id );
		echo "Postqueue ID: " . $queue_id;
		wp_die(); // this is required to terminate immediately and return a proper response
	}
}
