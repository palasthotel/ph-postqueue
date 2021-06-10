<?php

namespace Postqueue;

class Shortcode extends Component\Component {

	public function onCreate() {
		add_shortcode( Plugin::SHORTCODE, array($this, "do_shortcode") );
		
		add_action( 'admin_print_footer_scripts', array($this, 'add_text_editor_button'));
		
		add_filter( 'mce_buttons_2', array($this, 'add_tinymce_button') );
		add_filter( 'mce_external_plugins', array($this, 'add_tinymce_plugin') );
		
		add_filter('mce_css', array($this, 'tiny_mce_config') );
		
		add_action('wp_ajax_postqueue_data_script', array($this, "postqueue_data_script"));
		
	}

	/**
	 * inject editor css
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function tiny_mce_config($url): string {
		if ( !empty($url) ) $url .= ',';
		$url .= $this->plugin->url.'/css/tinymce-plugin.css';
		return $url;
	}
	
	public function do_shortcode($atts){
		
		if ( ! isset( $atts['slug'] ) ) return '';
		
		$slug = $atts['slug'];
		$viewmode = (!empty($atts['viewmode']))? $atts["viewmode"] : "" ;
		$offset = (!empty($atts['offset']))? $atts['offset']: 0;
		$limit = (!empty($atts['limit']))? $atts['limit']: -1;
		
		$store = $this->plugin->store;
		$queue = $store->get_queue_by_slug($slug);
		
		$pids = array();
		foreach ($queue as $item) {
			$pids[]=$item->post_id;
		}
		
		/**
		 * if no id in array return
		 * otherwise wp_query will render all posts
		 */
		if( count($pids) < 1 ) return '';
		
		/**
		 * build query args for loop
		 */
		$query_args = array (
			'post__in'      => $pids,
			'post_status'   => 'publish',
			'orderby'       => 'post__in',
			'post_type'     => 'any',
			'offset'        => $offset,
			'posts_per_page'=> $limit,
		);
		
		/**
		 * get content from template
		 */
		ob_start();
		include $this->plugin->templates->get_template_path(Plugin::TEMPLATE_NAME);
		$result = ob_get_contents();
		ob_end_clean();
		wp_reset_postdata();
		
		return $result;
	}
	
	/**
	 * adds a button to plaintext editor
	 */
	public function add_text_editor_button(){
		?>
		<script type="text/javascript">
			if(typeof QTags != "undefined")
			{
				QTags.addButton( 'postqueues', 'Postqueues', '[<?php echo Plugin::SHORTCODE; ?> slug=""]');
			}
		</script>
		<?php
	}
	
	/**
	 * add button to tinymce
	 */
	public function add_tinymce_button($buttons){
		wp_enqueue_style( 'dashicons' );
		if($buttons[count($buttons)-1] == "wp_help"){
			array_splice($buttons, count($buttons)-1,0,"postqueue");
		} else {
			array_push( $buttons, 'postqueue' );
		}
		return $buttons;
	}
	
	/**
	 * add tinymce plugin js
	 */
	public function add_tinymce_plugin($plugins_array){
		/**
		 * needed for dialog
		 */
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		
		/**
		 * style for dialog
		 */
		wp_enqueue_script( "postqueue_data", '/wp-admin/admin-ajax.php?action=postqueue_data_script', array(), 1, 'all' );
		wp_enqueue_style( "postqueue", $this->plugin->url . '/css/tinymce.css', array("postqueue_data"), 2, 'all' );
		
		/**
		 * add plugin js
		 */
		$plugins_array['postqueue'] = $this->plugin->url.'/js/tinymce.js';
		return $plugins_array;
	}
	
	/**
	 * javascript data for postqueue tinymce plugin
	 */
	public function postqueue_data_script(){
		header('Content-Type: application/javascript');
		$queues = $this->plugin->store->get_queues();
		$viewmodes = \Postqueue::getViewmodes();
		
		$items = array();
		foreach ($queues as $queue){
			$items[] = array(
				"text" => $queue->name." [{$queue->slug}]",
				"value" => $queue->slug,
			);
		}
		
		$viewmode_items = array();
		foreach ($viewmodes as $viewmode){
			$viewmode_items[] = array(
				"text" => $viewmode["text"],
				'value' => $viewmode["key"],
			);
		}
		
		?>
		window.postqueue_items = <?php echo \json_encode($items); ?>;
		window.postqueue = {
			queues: <?php echo \json_encode($items); ?>,
			viewmodes: <?php echo \json_encode($viewmode_items); ?>,
		};
		<?php
		die();
	}
	
}