<?php
/**
 * Plugin Name:       BuildPalestine YouTube Lite
 * Description:       Keeps YouTube off first paint: strips the homepage hero iframe API and replaces embeds with a click-to-play facade.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bp-youtube-lite
 *
 * @package BP_Youtube_Lite
 */

defined( 'ABSPATH' ) || exit;

define( 'BP_YOUTUBE_LITE_VERSION', '0.1.0' );
define( 'BP_YOUTUBE_LITE_FILE', __FILE__ );
define( 'BP_YOUTUBE_LITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'BP_YOUTUBE_LITE_URL', plugin_dir_url( __FILE__ ) );

require_once BP_YOUTUBE_LITE_PATH . 'includes/class-bp-youtube-lite-transformer.php';
require_once BP_YOUTUBE_LITE_PATH . 'includes/class-bp-youtube-lite-plugin.php';

/**
 * Returns the shared plugin instance.
 *
 * @return BP_Youtube_Lite_Plugin
 */
function bp_youtube_lite() {
	return BP_Youtube_Lite_Plugin::instance();
}

bp_youtube_lite()->register();
