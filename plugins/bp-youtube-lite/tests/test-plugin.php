<?php
/**
 * Plugin tests.
 *
 * @package BP_Youtube_Lite
 */

class Test_BP_Youtube_Lite_Plugin extends WP_UnitTestCase {

	public function test_plugin_is_loaded() {
		$this->assertTrue( class_exists( 'BP_Youtube_Lite_Plugin' ) );
		$this->assertInstanceOf( BP_Youtube_Lite_Plugin::class, bp_youtube_lite() );
	}

	public function test_stylesheet_covers_hero_and_centers_popup() {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin CSS.
		$css = file_get_contents( BP_YOUTUBE_LITE_PATH . 'assets/css/bp-youtube-lite.css' );

		$this->assertIsString( $css );
		$this->assertStringContainsString( '177.78vh', $css );
		$this->assertStringContainsString( 'eb-popup-container:has(.bp-yt-lite)', $css );
		$this->assertStringContainsString( 'wp-block-embed__wrapper::before', $css );
	}

	public function test_excludes_stylesheet_from_litespeed_css_combine() {
		$plugin = bp_youtube_lite();

		$this->assertSame(
			array( 'other.css', 'bp-youtube-lite' ),
			$plugin->exclude_from_litespeed_css( array( 'other.css' ) )
		);
		$this->assertSame(
			array( 'bp-youtube-lite' ),
			$plugin->exclude_from_litespeed_css( '' )
		);
	}
}
