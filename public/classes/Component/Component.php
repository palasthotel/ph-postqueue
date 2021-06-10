<?php


namespace Postqueue\Component;

/**
 * Class Component
 *
 * @property \Postqueue\Plugin plugin
 *
 * @version 0.1.1
 */
abstract class Component {

	public function __construct(\Postqueue\Plugin $plugin) {
		$this->plugin = $plugin;
		$this->onCreate();
	}

	/**
	 * overwrite this method in component implementations
	 */
	abstract function onCreate();
}