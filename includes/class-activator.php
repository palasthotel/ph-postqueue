<?php

/**
 * Fired during plugin activation.
 *
 */
class Ph_Postqueue_Activator {

	/**
	 * activate plugin
	 */
	public static function activate() {
		/**
		 * wpdb object for prefix
		 */
		global $wpdb;
		/**
		 * require upgrade.php for dbDelta function
		 */
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		/**
		 * Create queues table
		 */
		dbDelta('CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix."ph_postqueues` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `name` varchar(30) NOT NULL DEFAULT '',
				  `slug` varchar(30) NOT NULL DEFAULT '',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `slug` (`slug`),
				  KEY `name` (`name`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

		/**
		 * create queues_content table
		 */
		dbDelta( 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'ph_postqueue_contents` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `queue_id` int(11) unsigned NOT NULL,
				  `post_id` int(11) unsigned NOT NULL,
				  `position` int(11) unsigned NOT NULL DEFAULT 0,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `post_in_queue` (`post_id`, `queue_id`),
				  UNIQUE KEY `position_in_queue` (`position`, `queue_id`),
				  KEY `queue_id` (`queue_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
	}

}
