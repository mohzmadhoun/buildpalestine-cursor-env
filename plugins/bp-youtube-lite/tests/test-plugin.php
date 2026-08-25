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
}
