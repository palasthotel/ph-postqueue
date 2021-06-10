<?php
/**
 * Plugin Name:       Postqueue - DEV
 * Description:       Dev inc file
 * Version:           X.X.X
 * Requires at least: X.X
 * Tested up to:      X.X.X
 * Author:            PALASTHOTEL by Edward and Julia
 * Author URI:        http://www.palasthotel.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       postqueue
 * Domain Path:       /plugin/languages
 */

include dirname(__FILE__)."/public/ph-postqueue.php";

register_activation_hook(__FILE__, function($multisite){
	postqueue_plugin()->onActivation($multisite);
});

register_deactivation_hook(__FILE__, function($multisite){
	postqueue_plugin()->onDeactivation($multisite);
});