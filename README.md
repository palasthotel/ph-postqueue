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

### Reordering

Two ways, deliberately. Dragging uses the browser's own drag events - no library - and
**Move up / Move down** buttons do the same thing for anyone working from the keyboard,
which dragging alone never covers. It is the pair the block editor offers for moving
blocks. `react-dnd` is gone, and with it `react-sortablejs` and `sortablejs`, which
nothing had imported for some time.

Changes to the order are held in the app until **Save**; leaving the page with unsaved
changes triggers the browser's own warning.

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

The older BlockX block is untouched and still requires the BlockX plugin.

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
