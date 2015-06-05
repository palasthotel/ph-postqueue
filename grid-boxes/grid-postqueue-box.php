<?php

class grid_postqueue_box extends grid_list_box
{
	public function __construct()
	{
		$this->content=new StdClass();
		$this->content->postqueue = "";
		// $this->content->items = 0;
		$this->content->viewmode = "";
	}

	public function type()
	{
		return 'postqueue';
	}

	public function build($editmode) {

		if($editmode) 
		{
			
			return t("Postqueue: ".$this->content->postqueue); 
		}
		else
		{	
			/**
			 * array that will be rendered
			 */
			
			$store = new PH_Postqueue_Store();
			$queues = $store->get_queue_by_slug($this->content->postqueue);
			// $output = $this->content->viewmode." ";
			$pids = array();
			foreach ($queues as $value) {
			// 	$output.= ",".$value->post_id;
				$pids[]=$value->post_id;
			}
			
			$query_args = array (
			  'post__in'      => $pids,
			  'post_status'   => 'publish',
			  'orderby'       => 'post__in',
			  'post_type'     => 'any',
			);

			// The Query
			$query = new WP_Query( $query_args );

			$output = "";
			ob_start();
			while ( $query->have_posts() ) {
				$query->the_post();
				get_template_part("teaser", $this->content->viewmode);
			}
			$output = ob_get_contents();
			ob_end_clean();
			wp_reset_postdata();

			return $this->wrap_list($output, $this->content->viewmode);
			
		}
	}

	private function wrap_list($output, $viewmode){
		$mosaic_class = "";
		if($viewmode == "big" || $viewmode == "small"){
			$mosaic_class = " ph-teaser-mosaic-list";
		}
		return '<ul class="ph-teaser-'.$viewmode.'-list'.$mosaic_class.'">'.$output.'</ul>';

	}

	public function contentStructure() {

		/**
		 * viewmodes from theme dir
		 */
		$viewmodes = array();
		$viewmodes[] = array("key"=> "pure", "text" => "Pure");
		$viewmodes[] = array("key"=> "big", "text" => "Big");
		$viewmodes[] = array("key"=> "illustrated", "text" => "Illustrated");
		$viewmodes[] = array("key"=> "mega", "text" => "Mega");
		$viewmodes[] = array("key"=> "detailed", "text" => "Detailed");
		$viewmodes[] = array("key"=> "small", "text" => "Small");

		$store = new PH_Postqueue_Store();
		$queues = $store->get_queues();

		$qs = array();
		foreach ($queues as $queue) {
			$qs[] = array("key"=>$queue->slug, "text"=>$queue->name."[".$queue->slug."]");
		}

		return array(
			array(
				'key' => 'postqueue',
				'label' => t('Postqueue'),
				'type' => 'select',
				'selections' => $qs,
			),
			array(
				'key' => 'viewmode',
				'label' => t("Viewmode"),
				'type' => "select",
				'selections' => $viewmodes,
			),
			// array(
			// 	'key' => 'items',
			// 	'label' => t('Number of items (0 will render all items of queue)'),
			// 	'type' => 'number',
			// ),
		);
	}
}
