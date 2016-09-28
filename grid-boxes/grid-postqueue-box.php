<?php

class grid_postqueue_box extends grid_list_box
{
	public function __construct()
	{
		parent::__construct();
		$this->content->postqueue = "";
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
			return $this->content;
		}
	}

	public function contentStructure() {
		$cs = parent::contentStructure();

		$store = new \Postqueue\Store();
		$queues = $store->get_queues();

		$qs = array();
		foreach ($queues as $queue) {
			$qs[] = array("key"=>$queue->slug, "text"=>$queue->name);
		}
		
		$cs[] = array(
			'key' => 'postqueue',
			'label' => t('Postqueue'),
			'type' => 'select',
			'selections' => $qs,
		);
		
		$viewmodes = $this->getViewmodes();
		if(count($viewmodes) > 0){
			$cs[] = array(
				'key' => 'viewmode',
				'label' => t("Viewmode"),
				'type' => "select",
				'selections' => $viewmodes,
			);
		}

		return $cs;
	}
	
	/**
	 * get available viewmodes
	 * @return array viewmodes
	 */
	public function getViewmodes(){
		$viewmodes = array(
			//array('key' => 'excerpt', 'text' => t('Excerpt') ),
		);
		return apply_filters(Postqueue::FILTER_VIEWMODES,$viewmodes);
	}
}
