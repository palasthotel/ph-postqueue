<?php

class grid_postqueue_box extends grid_list_box
{
	public function __construct()
	{
		parent::__construct();
		$this->content->postqueue = "";
		$this->content->viewmode = "";
		$this->content->offset = 0;
		$this->content->limit = -1;
	}

	public function type()
	{
		return 'postqueue';
	}

	public function build($editmode) {

		if($editmode) 
		{

			if($this->grid){
				return t("Postqueue: ".$this->content->postqueue.
				         "<br/> Offset: ".$this->content->offset." - Limit:".$this->content->limit);
			} else {
				return t("Postqueue");
			}


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
		
		$viewmodes = Postqueue::getViewmodes();
		if(count($viewmodes) > 0){
			$cs[] = array(
				'key' => 'viewmode',
				'label' => t("Viewmode"),
				'type' => "select",
				'selections' => $viewmodes,
			);
		}
		
		$cs[] = array(
			'key' => 'offset',
			'label' => 'Offset',
			'type' => 'number',
		);
		$cs[] = array(
			'key' => 'limit',
			'label' => 'Limit	',
			'type' => 'number',
		);

		return $cs;
	}
}
