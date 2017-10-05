# Postqueue

Be free to order posts as you wish. Plugin is available at [WordPress.org](https://wordpress.org/plugins/postqueue/)


## Templates

You can copy the default templates from plugins "templates" folder into "%theme%/plugin-parts/*".

### postqueue.tpl.php

Available variables in template:

_$queue_ ===> Array of queue objects

_$store_ ===> Postqueue\Store object

_$query_args_ ===> WP_Query arguments

_$viewmode_ ===> optional viewmode attribute

_$offset_ ===> number of posts to skip

_$limit_ ===> number of posts use.

---

## Actions

Available actions for postqueue plugin.

### Extend grid postqueue box

If you want to extend the grid-postqueue-box use this action to register your extending box. Also register all other grid boxes that depend on the postqueue plugin here.

```php
add_action( 'postqueue_grid_boxes', 'myplugin_postqueue_boxes');
function myplugin_postqueue_boxes(){
	require 'box-class-file1.php';
	require 'box-class-file2.php';
	...
}
```

## Filters

Available filters for postqueue plugin.

### Postqueue viewmodes

```php
add_filter( 'postqueue_viewmodes', 'myplugin_postqueue_viewmodes');
function myplugin_postqueue_viewmodes($viewmodes){
	$viewmodes[] = array(
		array('key' => 'viewmode_slug', 'text' => 'Viewmode label' ),
	);
	return $viewmodes;
}
```

**Parameters:**

_$viewmodes_ ==> Array of Assoc Arrays with key and text.

### Postqueue edit capabilities

```php
add_filter( 'postqueue_edit_capability', 'myplugin_postqueue_edit_capability');
function postqueue_edit_capability($capabilities){
	$capabilities = "edit_page";
	return $capabilities;
}
```

**Parameters:**

_$capabilities_ ==> [WordPress capabilities](https://codex.wordpress.org/Roles_and_Capabilities) string

