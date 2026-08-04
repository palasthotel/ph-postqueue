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
