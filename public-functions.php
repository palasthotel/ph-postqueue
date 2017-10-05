<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 05.10.17
 * Time: 11:12
 */

/**
 * @return \Postqueue\Plugin
 */
function postqueue_get(){
	return \Postqueue\Plugin::instance();
}


/**
 * @deprecated use \Postqueue\Store instead
 */
class PH_Postqueue_Store extends \Postqueue\Store {}

/**
 * @deprecated use \Postqueue\Plugin instead
 */
class Postqueue extends \Postqueue\Plugin{}

global $postqueue;
/**
 * @var $postqueue \Postqueue\Plugin
 * @deprecated use postqueue_get() instead
 */
$postqueue = postqueue_get();