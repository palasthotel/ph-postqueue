=== Postqueue ===
Contributors: palasthotel, edwardbock, janame
Donate link: http://palasthotel.de/
Tags: loop, order posts, queue
Requires at least: 5.0
Tested up to: 6.0.2
Stable tag: 1.5.0
Requires PHP: 7.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl

Allows you to create you very own loop order of posts

== Description ==

Sometimes you cannot use any parameter to order your desired post order but have to do this order by hand. Postqueue allows you to do that.

This Plugin provides a new Box for [Grid](http://wordpress.org/plugins/grid/ "Grid Landingpage Editor").

== Installation ==

1. Upload `postqueue-wordpress.zip` to the `/wp-content/plugins/` directory
1. Extract the Plugin to a `postqueue` Folder
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==

= 1.5.0 =
* Feature: Added blockx block
* Feature: Added headless preparation for blockx block

= 1.4.2 =
 * Bugfix: Query only for public post types in builder search

= 1.4.1 =
 * Bugfix: meta box did not appear on custom post types sometimes

= 1.4.0 =
 * Visual update: Date and status information in post queue editor

= 1.3.1 =
 * Bugfix: PHP7.3 compatibility issue fixed

= 1.3.0 =
 * Post queue editor UI with React

= 1.2.4 =
 * Feature - Limit postqueue length with limiter. Default is -1 no limitation.

= 1.2.3 =
 * Optimization: JS and CSS versions via file time.

= 1.2.2 =
 * Optimization: Grid box with link to postqueue tools
 * Bugfix: Changed ajax methods from GET to POST to avoid request limits

= 1.2.1 =
 * Feature: new filter to change the postqueue search order
 * added some security conditions to ajax functions
 * new filter for default position if adding post to postqueue via post meta box

= 1.2.0 =
 * Optimization: New filter for default add to postqueue behavior of post meta box.
 * Security fix: Check capabilities within ajax calls.

= 1.2.0 =
 * New metabox that displays postqueues related to and lets you add post to postqueues

= 1.1.6 =
 * Modify postqueue rights filter
 * PHP namespacing changes
 * Postqueue class deprecated and is not \Postqueue\Plugin
 * Use public function postqueue_get() instead of global $postqueue

= 1.1.5 =
 * Empty queue fix
 * Deleted queue ends in infinity loop fix

= 1.1.4 =
 * Theme template moved to theme/plugin-parts/*

= 1.1.3 =
 * Extend grid postqueue box action

= 1.1.2 =
 * Tinymce preview optimized
 * template file

= 1.1.1 =
 * Tested up to 4.6.1

= 1.1.0 =
 * UI optimization
 * Shortcode support

= 1.0.7 =
 * Fixed empty queues bug

= 1.0.6 =
 * Post id zero in postqueue fix

= 1.0.5 =
 * Future posts can be added to queues

= 1.0.4 =
 * Title overwrite option

= 1.0.3 =
 * Grid box

= 1.0.2 =
 * Copy past bugfix

= 1.0.1 =
 * Table prefix bugfix

= 1.0 =
 * First release

== Upgrade Notice ==

Move theme template from theme/postqueue.php to theme/plugin-parts/postqueue.tpl.php

== Arbitrary section ==



