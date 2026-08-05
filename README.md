# Postqueue (WordPress-Plugin)

Order posts by hand instead of by date: build named queues in the editor, then render
them anywhere with a shortcode, a block or a template.

- **WordPress.org:** https://wordpress.org/plugins/postqueue/
- **User documentation:** [public/readme.txt](public/readme.txt) (the text shown on WordPress.org)
- **Changelog:** [CHANGELOG.md](CHANGELOG.md) — release-please owns that file, so do not
  add notes to it by hand. Entries up to 1.5.1 are in the `== Changelog ==` section of
  [public/readme.txt](public/readme.txt).


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

### Queue created / queue deleting

Fired from the REST routes a queue is created and deleted through (`classes/REST.php`).

```php
add_action( 'ph_postqueue_created', function ( $queue ) {
	// $queue->id, $queue->slug
} );
add_action( 'ph_postqueue_deleting', function ( $queue_id ) {
	// about to be deleted, not deleted yet
} );
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

### Postqueue length limit

Caps every queue at a fixed length; adding past it drops the oldest items. `-1` (the
default) means unlimited.

```php
add_filter( 'postqueue_limiter', function ( $limit ) {
	return 20;
} );
```

### Postqueue search order

The `ORDER BY` clause used when searching queues by name in the Tools screen and the
classic meta box's picker.

```php
add_filter( 'postqueue_store_search_order', function ( $order ) {
	return 'name ASC'; // default: 'id ASC'
} );
```

### Add-to-queue position

Where the classic editor's meta box puts a post it adds to a queue: `'first'`, `'last'`,
or anything else for the store's own default.

```php
add_filter( 'postqueue_add_position', function ( $position ) {
	return 'first';
} );
```

### Template lookup paths

Lets another plugin add a directory `get_template_path()` searches, alongside the theme
and the plugin's own `templates/` folder.

```php
add_filter( 'postqueue_add_template_paths', function ( $paths ) {
	$paths[] = __DIR__ . '/my-postqueue-templates';
	return $paths;
} );
```

---

## Public functions

```php
postqueue_plugin(); // -> Postqueue\Plugin::instance()
postqueue_store();  // -> the Postqueue\Store instance
```

The replacement for the deprecated `postqueue_get()`/`global $postqueue` (see the
changelog).

## Shortcode

```
[postqueue slug="my-queue" viewmode="" offset="0" limit="-1"]
```

_slug_ ==> the queue's slug (required)

_viewmode_ ==> a key registered through the `postqueue_viewmodes` filter, or empty for
the default

_offset_ ==> number of posts to skip

_limit_ ==> number of posts to show, `-1` for all

---

## The Postqueues screen

Under **Tools → Postqueues**, in two views:

- **the overview** is a `WP_List_Table` - core's own table class, the one behind Posts,
  Pages and Users. That is where the search box, the sortable columns, the row actions,
  bulk delete and pagination come from; none of it is ours. Core marks the class
  private, but reimplementing it means reimplementing all of that and still not matching
  it. Creating happens through an ordinary form next to the table, the layout the
  taxonomy screens use.
- **a single queue** (`&queue=<id>`) is a small React app, because the order is edited
  live. Its items render as a `wp-list-table` too, so it looks like the rest of the
  admin.

Creating and deleting are form posts handled on `load-{$hook}` and answered with a
redirect, so a reload does not repeat them.

### Adding a column to the overview

Because the overview is a plain `WP_List_Table`, a column is added with the same two
hooks a column is added to any core list table with. The screen id is
`tools_page_` plus `\Postqueue\Editor::PAGE_SLUG`:

```php
add_filter( 'manage_tools_page_tools-postqueue_columns', function ( $columns ) {
	$columns['my_column'] = __( 'Mine', 'my-plugin' );
	return $columns;
} );

// $item is the row: id, name, slug, items
add_filter( 'manage_tools_page_tools-postqueue_custom_column', function ( $content, $column, $item ) {
	return 'my_column' === $column ? esc_html( $item['slug'] ) : $content;
}, 10, 3 );
```

Sortable columns go through `manage_tools_page_tools-postqueue_sortable_columns`, and
WordPress offers the column under **Screen Options** by itself. The
[Postqueue Feeds](https://wordpress.org/plugins/postqueue-feeds/) plugin uses this to
show each queue's feed address; Postqueue knows nothing about feeds.

### Reordering

Two ways, deliberately. Dragging uses the browser's own drag events - no library - and
**Move up / Move down** buttons do the same thing for anyone working from the keyboard,
which dragging alone never covers. It is the pair the block editor offers for moving
blocks. `react-dnd` is gone, and with it `react-sortablejs` and `sortablejs`, which
nothing had imported for some time.

Changes to the order are held in the app until **Save**; leaving the page with unsaved
changes triggers the browser's own warning.

## REST API

Namespace `postqueue/v1` (`classes/REST.php`), used by the Postqueues screen and the
block editor - not documented as a stable public API, but there if you need it:

| Method | Route | |
|---|---|---|
| GET, POST | `/queues` | list (optional `?search=`), or create (`name`) |
| GET, DELETE | `/queues/{id}` | a queue's items, or delete the queue |
| POST | `/queues/{id}/items` | replace a queue's items (`items`: post ids, in order) |
| DELETE | `/queues/{id}/items/{pid}` | remove one post from a queue |
| GET | `/posts` | post search for the "add a post" pickers (`?search=`) |

All of them require `postqueue_edit_capability`. Also see the `postqueues` REST field on
every public post type, and `ph_postqueue_created`/`ph_postqueue_deleting` above.

## Block editor

**Adding a post to a queue** happens in the document sidebar, through a
`PluginDocumentSettingPanel` - not in a meta box. The classic meta box is skipped when
`get_current_screen()->is_block_editor()` is true, so exactly one of the two is shown.

The panel is modelled on the core category panel, in behaviour as well as in markup:

- The queues live in a `postqueues` REST field on the post. The panel edits it with
  `editPost()`, so the post becomes dirty and the assignment is written when the post is
  saved — nothing is sent on click.
- Creating a queue *is* immediate, exactly as adding a category is. Only the assignment
  waits.
- The markup mirrors `HierarchicalTermSelector`, which WordPress does **not** export: a
  `Flex direction="column" gap="4"` for the spacing, and core's own class names on the
  list, the choices, the add button and the name field. That inherits core's styling
  including the list scrolling at `max-height: 14em`. If core renames those classes the
  panel loses its polish but keeps working.
- A search field appears once there are more than
  `postqueue_panel_search_threshold` queues (default 8, which is the value WordPress's
  own term selector uses).

**Outputting a queue** is a variation of the core Query Loop, registered as
`postqueue/queue`. The queue slug is stored in the block's own `query` attribute under
`postqueue`, and two filters turn that into a real query:

| Where | Hook |
|---|---|
| Front end | `query_loop_block_query_vars` |
| Editor preview | `rest_{$post_type}_query` — the Post Template block spreads query keys it does not know into its REST request, so the same slug arrives there |

Both end in `post__in` plus `orderby => post__in`, which is what preserves the queue's
order. An empty queue becomes `post__in => [0]`: an empty `post__in` is ignored by
`WP_Query` and would return every post, which is the opposite of what an empty queue
means.

The older BlockX block is untouched and still requires the BlockX plugin. If
[ph-headless](https://github.com/palasthotel/ph-headless) is active, `classes/Headless.php`
registers a block-preparation extension so that block still resolves correctly in
headless REST output.

---

## Repository layout

`public/` is exactly what ships to WordPress.org. Everything outside it is
repository-only.

| Path | Description |
|---|---|
| `public/ph-postqueue.php` | plugin header and bootstrap |
| `public/classes/` | the plugin's PHP |
| `public/templates/`, `public/partials/`, `public/grid-*/` | overridable output templates and grid boxes |
| `public/dist/` | compiled editor assets — **generated**, not in the repository |
| `public/css/`, `public/js/` | hand-written TinyMCE assets, not generated |
| `public/languages/` | translations |
| `public/vendor/` | generated composer autoloader, no third-party code |
| `src/` | JavaScript sources for the editor and the meta box |
| `assets/` | media for the WordPress.org plugin page — not part of the download |
| `ph-postqueue.php` | DEV wrapper, loads `public/ph-postqueue.php` when the repository is checked out into `wp-content/plugins/` |
| `bin/` | release helper scripts |
| `.github/workflows/` | CI/CD — see [.github/WORKFLOWS.md](.github/WORKFLOWS.md) |

## Development

```sh
npm ci
npm run build      # → public/dist/
npx wp-env start   # http://localhost:8888, admin / password
bash bin/pack.sh   # → postqueue.zip
```

`public/dist/` is generated and gitignored — the release pipeline builds it. Run
`npm run build` before `wp-env start` or `bin/pack.sh`; the pack script refuses to
package an unbuilt payload, and the PHP reads the built files with `filemtime()`, so an
unbuilt checkout produces warnings in the editor.

## Releasing

Releases are automated with [release-please](https://github.com/googleapis/release-please)
and deployed to the WordPress.org SVN repository. Commit with
[conventional commits](https://www.conventionalcommits.org/) and merge the release PR:

```
fix: …   → patch    feat: …  → minor    feat!: … → major
```

Details in [.github/WORKFLOWS.md](.github/WORKFLOWS.md), commit conventions in
[CONTRIBUTING.md](CONTRIBUTING.md).

## License

GNU General Public License v3.0 or later — see [LICENSE](LICENSE).
