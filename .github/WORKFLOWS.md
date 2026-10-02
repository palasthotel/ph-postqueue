# CI/CD Workflows

The four workflows in `.github/workflows/` call the shared ones in
[palasthotel/github-workflows](https://github.com/palasthotel/github-workflows). How
they work, every input and what to do when a deploy fails is described there, in
[docs/wp-plugin.md](https://github.com/palasthotel/github-workflows/blob/main/docs/wp-plugin.md).

What is specific to this plugin:

| | |
|---|---|
| wordpress.org slug | `postqueue` |
| version file | `package.json` (`release-type: node`) - keep it, release-please and the scripts read the version there |
| build step | `npm ci && npm run build` - compiles `src/` into `public/dist/`, which is not in the repository |
| composer | `public/composer.json` only defines the PSR-4 autoloader. The shared `pack.sh` runs `composer install --no-dev` and `dump-autoload --optimize` in the staged payload and drops `composer.json`; `public/vendor/` stays in the repository so the development wrapper works without composer |
| required files | the build output in `public/dist/`, `vendor/autoload.php`, the TinyMCE script and styles, the German translation - see `pr.yml` |
| development wrapper | `ph-postqueue.php` in the root, `Plugin Name: Postqueue - DEV`; never shipped |
| SVN | `assets/` (the plugin page icons) is in this repository and mirrored to SVN `assets/` on every deploy |

Versions are never edited by hand: release-please bumps `package.json` and `CHANGELOG.md`
in the release PR, and the sync workflow writes the same version into the header of
`public/ph-postqueue.php` and the `Stable tag:` of `public/readme.txt`.
