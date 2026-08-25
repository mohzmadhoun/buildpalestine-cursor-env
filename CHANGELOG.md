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

## PR #3 — Stop YouTube from loading on first paint

_2026-08-25_

### Summary

Added `bp-youtube-lite` so the homepage hero no longer autoplays YouTube via the
IFrame API, and remaining YouTube embeds render as a thumbnail facade until the
visitor clicks Play. The muted hero iframe is cover-scaled to fill the hero
without black side bars, and the Play popup is sized to the 16:9 video and
centered (no 70vh grey header).

### Added

- `plugins/bp-youtube-lite/`: strips `youtube.com/iframe_api` / `onYouTubeIframeAPIReady`
  from the hero HTML block, replaces YouTube iframes with a click-to-play poster,
  and injects `youtube.com/embed` only after first paint (muted hero) or Play (popup).
- `exported-plugins/bp-youtube-lite-0.1.4.zip`: installable copy of the plugin (runtime
  files only; tests and phpunit config are omitted).

### Fixed

- Hero background used a stretched iframe, so YouTube pillarboxed with black bars.
  The iframe is now kept 16:9 and sized to cover the hero box (crop top/bottom or sides).
- Essential Blocks popup was `90% × 70vh` with a WordPress embed `::before` spacer, which
  left a dark `--contrast` block above the player and shifted the modal down.

### Notes

- The hero already has a CSS background image (`OrangeTruck.webp`), so removing the
  muted autoplay player does not leave a blank hero.
- The Root Fellows video lives in an Essential Blocks popup. The hero autoplays a
  muted looping copy after first paint. Clicking Play opens that popup with sound
  and controls. The background player pauses while the popup is open.
- LiteSpeed UCSS/combine strips first-paint-unused iframe rules, so this plugin's
  CSS and JS are excluded from LiteSpeed optimize.

### Verification

- `composer check` passed (10 `bp-youtube-lite` tests, 14 `hello-cursor` tests).
- Logged-out homepage HTML has no `iframe_api`, no `youtube.com/embed`; the only iframe is GTM.
- Mobile Lighthouse: YouTube dropped from ~1.1 MB / many player requests to one 11 KB poster;
  page weight 2527 KiB → 1412 KiB, requests 89 → 53, TTI 10.0 s → 7.7 s.
- Headless Chrome at 1440×900: hero iframe 1440×810 covering the 1440×675 hero;
  popup 1296×729, vertical `centerOffset` 0, no gap above the player.
- Browser hard-refresh: muted hero fills the hero width with no side bars; Play opens
  a centered 16:9 popup with no grey header; Escape restores the hero.

### Commits

- `75a73e2` Keep YouTube off first paint with a click-to-play facade
- `d7468c3` Play the hero video in the hero, not a popup
- `d63c3b8` Autoplay the muted hero video and restore the Play popup
- `432d091` Cover-scale the hero video and center the Play popup
- `04bbad8` Keep YouTube lite CSS out of LiteSpeed UCSS combine
- `09568fc` Size the hero iframe to the actual hero box

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
