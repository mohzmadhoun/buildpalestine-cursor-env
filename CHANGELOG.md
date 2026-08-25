# Changelog

A running record of the work done on this repository, newest first. Every pull
request gets an entry describing what changed, why, how it was verified, and the
commits it contains.

## How to add an entry

Add a new `##` section at the top for the pull request you are working on, using
the heading `## PR #<number> — <title>` followed by the date. Inside it, keep the
subsections that apply and drop the ones that do not:

- **Summary** — what the change accomplishes, in a sentence or two.
- **Added / Changed / Fixed / Removed** — the substance of the change.
- **Verification** — the checks that were run and their results, so a later
  reader can tell what was actually proven rather than assumed.
- **Notes** — decisions, trade-offs, and anything surprising that a future
  contributor would otherwise have to rediscover.
- **Commits** — each commit hash with its subject line.

Write for someone returning to this repository months from now with no memory of
the session. Prefer a sentence that explains a decision over a bullet that only
restates the diff.

---

## PR — Reset the site and stage BuildPalestine UpdraftPlus backups

_2026-08-25_

### Summary

Reset the local WordPress database with WP Reset, removed WooCommerce, AI
Provider for OpenAI, and `mzm-current-year`, installed UpdraftPlus, and staged
the 2026-08-23 BuildPalestine backup archives under
`/var/www/wordpress/wp-content/updraft`.

### Added

- WP Reset and UpdraftPlus as part of environment install.
- Idempotent download of the five UpdraftPlus backup archives.

### Removed

- WooCommerce and AI Provider for OpenAI from the default install path.
- `plugins/mzm-current-year/` from the repository.

### Notes

- Direct `curl` of `buildpalestine.com/temp/` is blocked by a Cloudflare
  challenge. The archives were downloaded in a real browser and are kept
  outside Git so a snapshot can carry them.
- WP Reset runs once, gated by `wp-content/.cursor-wp-reset-done`, so later
  install runs do not wipe a restored site.

---

## PR #1 — Initialize the Cloud Agent environment from cursor-testing-01

_2026-08-25_

### Summary

Copied the WordPress plugin development Cloud Agent stack from
[mohzmadhoun/cursor-testing-01](https://github.com/mohzmadhoun/cursor-testing-01)
into this empty repository so BuildPalestine agents boot with WordPress,
WooCommerce, WP-CLI, and the coding-standards/test toolchain.

### Added

- `.cursor/install.sh`, `.cursor/start.sh`, `.cursor/lib.sh`, and
  `.cursor/environment.json` for the Apache + PHP 8.3 + MariaDB stack.
- Reference plugins `hello-cursor` and `mzm-current-year` with PHPUnit coverage.
- Composer tooling (`phpcs`, PHPStan, PHPUnit), `bin/` helpers, WooCommerce
  sample catalogue, and workspace docs.

### Notes

- ChatHearth was left out of this import: it is a product project in the source
  repository, not part of the reusable environment.
- Site title defaults to `BuildPalestine`. Local admin credentials remain the
  throwaway `admin` / `admin` pair from the source environment.
- `install_test_suite` now probes both `x.y` and `x.y.0` wordpress-develop tags.
  WordPress 7.1 reports `7.1` from WP-CLI while the develop archive is tagged
  `7.1.0`, which made the original download 404.
