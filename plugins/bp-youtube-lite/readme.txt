=== BuildPalestine YouTube Lite ===
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Keeps YouTube off first paint on BuildPalestine.

== Description ==

Strips the homepage hero YouTube IFrame API (the muted autoplay background)
and replaces YouTube embeds with a thumbnail-and-play facade. The YouTube
player is injected only after the visitor clicks Play.

== Changelog ==

= 0.1.4 =
* Size the muted hero iframe to cover the hero box from JavaScript.
* Mark the Play popup so layout CSS still applies if :has() is skipped.

= 0.1.3 =
* Cover-scale the muted hero iframe so it fills the hero without black bars.
* Size and center the Play popup on the 16:9 video (no grey header).

= 0.1.2 =
* Hero autoplays muted; Play opens the original popup.

= 0.1.1 =
* Play loads YouTube in the hero section instead of the popup.

= 0.1.0 =
* Initial release.
