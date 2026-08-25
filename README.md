# buildpalestine-cursor-env

A WordPress plugin development workspace for Cursor Cloud Agents, initialized
from [mohzmadhoun/cursor-testing-01](https://github.com/mohzmadhoun/cursor-testing-01).
Every agent that starts on this repository gets a running WordPress site, WP-CLI,
the WordPress PHPUnit test suite, and the WordPress coding standards, so you can
ask it to build and verify a plugin end to end.

## What the environment gives you

| Piece | Details |
| --- | --- |
| Site | <http://localhost:8080> (port 8080 is forwarded, so it is also reachable from the agent's browser preview) |
| Admin | <http://localhost:8080/wp-admin> — `admin` / `admin`, local-only throwaway credentials |
| WordPress | Latest release, installed at `/var/www/wordpress` with `WP_DEBUG` and pretty permalinks on |
| Backups | UpdraftPlus with the 2026-08-23 BuildPalestine archives in `/var/www/wordpress/wp-content/updraft` |
| Stack | Apache 2.4 + PHP 8.3 (mod_php) + MariaDB 10.11 |
| Tools | WP-CLI, Composer, PHPUnit + WordPress test suite, PHPCS + WordPress Coding Standards, PHPStan, WP Reset, Query Monitor |

WordPress core lives outside the repository so it is never committed and survives
branch switches. Only your own code is versioned: everything in `plugins/` (and
`themes/`, if you add it) is symlinked into `wp-content/` on every boot.

## Repository layout

```
CHANGELOG.md               Running record of the work done, one entry per pull request
.cursor/environment.json   Cloud Agent environment definition
.cursor/install.sh         Builds the stack: packages, database, WordPress, WP Reset, UpdraftPlus, backups
.cursor/start.sh           Per-boot startup: MariaDB, Apache, plugin symlinks
plugins/hello-cursor/      Reference plugin: settings page, shortcode, REST route, tests
data/sample-products.csv   Sample catalogue (kept for optional store work)
bin/new-plugin.sh          Scaffolds a new plugin
bin/test.sh                Runs the PHPUnit suites
bin/import-products.php    Imports a WooCommerce product CSV
bin/wp-reset.sh            Rebuilds the site and the store from scratch
phpcs.xml.dist             WordPress coding standards ruleset
phpstan.neon.dist          Static analysis configuration
wp-cli.yml                 Points `wp` at the site, so no --path is needed
```

## Asking an agent to build a plugin

Start a Cloud Agent on this repository and describe the plugin. The environment is
already running, so the agent can write code, activate it, and check the result in
the same session. Useful things to include in the request:

- what the plugin should do, and where it appears (admin screen, block, shortcode, REST route, cron job);
- that it should live in `plugins/<slug>/`, generated with `bin/new-plugin.sh <slug>`;
- that it must pass `composer check` (coding standards, static analysis, tests);
- any behaviour worth an integration test, so the agent adds one to `plugins/<slug>/tests/`;
- that it should add an entry to [CHANGELOG.md](CHANGELOG.md), which records what each pull request changed and how it was verified.

For example: *"Create a plugin `event-countdown` that renders a shortcode counting
down to a date set on a settings page. Cover the date parsing and the shortcode
output with tests, and make `composer check` pass."*

Agents can browse the site themselves, so you can also ask for a screenshot of the
plugin's admin screen or front-end output as proof it works.

## Everyday commands

```bash
bin/new-plugin.sh my-plugin "My Plugin"   # scaffold, symlink and activate a plugin
bin/test.sh                               # PHPUnit for every plugin
bin/test.sh my-plugin --filter test_name  # PHPUnit for one plugin or one test
composer lint                             # WordPress coding standards
composer lint:fix                         # fix what can be fixed automatically
composer analyse                          # PHPStan
composer check                            # lint + analyse + test
bin/wp-reset.sh                           # rebuild the site, keeping your code
```

WP-CLI works from the repository root without extra flags:

```bash
wp plugin list
wp plugin activate my-plugin
wp post create --post_type=page --post_title=Demo --post_status=publish --post_content='[hello_cursor]'
wp eval 'var_dump( get_option( "hello_cursor_settings" ) );'
wp db query 'SELECT option_name FROM wp_options LIMIT 5;'
```
