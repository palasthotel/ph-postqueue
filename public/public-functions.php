<?php

use Postqueue\Plugin;
use Postqueue\Store;

/**
 * @return Plugin
 */
function postqueue_plugin(){
	return Plugin::instance();
}

/**
 * @return Store
 */
function postqueue_store(){
	return postqueue_plugin()->store;
}