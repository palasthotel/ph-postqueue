<?php


namespace Postqueue\Component;

abstract class Component {

    public Plugin $plugin;

	public function __construct(\Postqueue\Plugin $plugin) {
		$this->plugin = $plugin;
		$this->onCreate();
	}

	/**
	 * overwrite this method in component implementations
	 */
	abstract function onCreate();
}
