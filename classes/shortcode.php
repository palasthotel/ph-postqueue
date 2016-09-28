<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 28.09.16
 * Time: 08:37
 */

namespace Postqueue;

class Shortcode {
	
	const TEMPLATE_NAME = "postqueue.php";
	
	/**
	 * @var \Postqueue
	 */
	public $plugin;
	
	/**
	 * Shortcode constructor.
	 *
	 * @param \Postqueue $plugin
	 */
	public function __construct(\Postqueue $plugin) {
		$this->plugin = $plugin;
		add_shortcode( 'postqueue', array($this, "do_shortcode") );
	}
	
	public function do_shortcode($atts){
		$result = "";
		
		if ( isset( $atts['slug'] ) ) {
			$slug = $atts['slug'];
			$viewmode = (!empty($atts['viewmode']))? $atts["viewmode"] : "" ;
			
			$store = $this->plugin->store;
			$queue = $store->get_queue_by_slug($slug);
			
			$pids = array();
			foreach ($queue as $item) {
				$pids[]=$item->post_id;
			}
			
			/**
			 * build query args for loop
			 */
			$query_args = array (
				'post__in'      => $pids,
				'post_status'   => 'publish',
				'orderby'       => 'post__in',
				'post_type'     => 'any',
			);
			
			/**
			 * get content from template
			 */
			ob_start();
			$template = locate_template(self::TEMPLATE_NAME);
			if('' != $template){
				include $template;
			} else {
				include $this->plugin->dir."/templates/".self::TEMPLATE_NAME;
			}
			$result = ob_get_contents();
			ob_end_clean();
			wp_reset_postdata();
			
		}
		return $result;
	}
}