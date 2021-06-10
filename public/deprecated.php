<?php

/**
 * @return \Postqueue\Plugin
 * @deprecated use postqueue_plugin() instead
 */
function postqueue_get(){
	return postqueue_plugin();
}

/**
 * @deprecated use postqueue_store() instead
 */
class PH_Postqueue_Store extends \Postqueue\Store {}

/**
 * @deprecated use \Postqueue\Plugin instead
 */
class Postqueue extends \Postqueue\Plugin{}

global $postqueue;
/**
 * @var $postqueue \Postqueue\Plugin
 * @deprecated use postqueue_plugin() instead
 */
$postqueue = postqueue_get();