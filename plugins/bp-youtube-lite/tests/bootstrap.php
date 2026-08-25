<?php
/**
 * PHPUnit bootstrap: loads the WordPress test suite with this plugin active.
 *
 * @package BP_Youtube_Lite
 */

$bp_youtube_lite_repo_dir = dirname( __DIR__, 3 );

if ( file_exists( $bp_youtube_lite_repo_dir . '/vendor/autoload.php' ) ) {
	require_once $bp_youtube_lite_repo_dir . '/vendor/autoload.php';
}

$bp_youtube_lite_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $bp_youtube_lite_tests_dir ) {
	$bp_youtube_lite_tests_dir = '/var/www/wp-tests-lib';
}

$bp_youtube_lite_tests_dir = rtrim( $bp_youtube_lite_tests_dir, '/\\' );

if ( ! file_exists( $bp_youtube_lite_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find the WordPress test suite in {$bp_youtube_lite_tests_dir}." . PHP_EOL;
	exit( 1 );
}

require_once $bp_youtube_lite_tests_dir . '/includes/functions.php';

/**
 * Loads the plugin before WordPress finishes booting.
 */
function bp_youtube_lite_manually_load_plugin() {
	require dirname( __DIR__ ) . '/bp-youtube-lite.php';
}

tests_add_filter( 'muplugins_loaded', 'bp_youtube_lite_manually_load_plugin' );

require $bp_youtube_lite_tests_dir . '/includes/bootstrap.php';
