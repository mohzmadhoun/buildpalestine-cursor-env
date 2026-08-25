<?php
/**
 * Transformer tests.
 *
 * @package BP_Youtube_Lite
 */

/**
 * Covers YouTube first-paint HTML transforms.
 */
class Test_BP_Youtube_Lite_Transformer extends WP_UnitTestCase {

	/**
	 * Transformer under test.
	 *
	 * @var BP_Youtube_Lite_Transformer
	 */
	private $transformer;

	public function set_up() {
		parent::set_up();
		$this->transformer = new BP_Youtube_Lite_Transformer();
	}

	public function test_extracts_video_id_from_watch_embed_and_short_urls() {
		$this->assertSame( 'm_LNspjrlyM', $this->transformer->extract_video_id( 'https://www.youtube.com/watch?v=m_LNspjrlyM' ) );
		$this->assertSame( 'm_LNspjrlyM', $this->transformer->extract_video_id( 'https://www.youtube.com/embed/m_LNspjrlyM?feature=oembed' ) );
		$this->assertSame( 'm_LNspjrlyM', $this->transformer->extract_video_id( 'https://youtu.be/m_LNspjrlyM' ) );
		$this->assertSame( 'm_LNspjrlyM', $this->transformer->extract_video_id( 'https://www.youtube-nocookie.com/embed/m_LNspjrlyM' ) );
		$this->assertSame( '', $this->transformer->extract_video_id( 'https://example.com/video' ) );
	}

	public function test_strip_hero_youtube_api_removes_iframe_api_and_player_init() {
		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript -- fixture HTML from the homepage.
		$html = '<div class="mzm-background-video"><div id="yt-player"></div></div>'
			. '<script src="https://www.youtube.com/iframe_api"></script>'
			. '<script>
  function onYouTubeIframeAPIReady() {
    new YT.Player(\'yt-player\', { videoId: \'m_LNspjrlyM\' });
  }
</script>'
			. '<h1>Palestinians are building their own liberation.</h1>';
		// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript

		$output = $this->transformer->strip_hero_youtube_api( $html );

		$this->assertStringNotContainsString( 'iframe_api', $output );
		$this->assertStringNotContainsString( 'onYouTubeIframeAPIReady', $output );
		$this->assertStringContainsString( 'id="yt-player"', $output );
		$this->assertStringContainsString( 'data-video-id="m_LNspjrlyM"', $output );
		$this->assertStringContainsString( 'mzm-background-video', $output );
		$this->assertStringContainsString( 'Palestinians are building their own liberation.', $output );
	}

	public function test_replace_youtube_iframes_uses_facade_and_leaves_other_iframes() {
		$html = '<iframe title="Root Fellows" src="https://www.youtube.com/embed/m_LNspjrlyM?feature=oembed" width="1080" height="608"></iframe>'
			. '<iframe src="https://example.com/donate" title="Donate"></iframe>';

		$output = $this->transformer->replace_youtube_iframes( $html );

		$this->assertStringNotContainsString( 'youtube.com/embed', $output );
		$this->assertStringContainsString( 'bp-yt-lite', $output );
		$this->assertStringContainsString( 'data-video-id="m_LNspjrlyM"', $output );
		$this->assertStringContainsString( 'https://example.com/donate', $output );
		$this->assertStringContainsString( 'i.ytimg.com/vi/m_LNspjrlyM/hqdefault.jpg', $output );
	}

	public function test_facade_markup_escapes_title() {
		$output = $this->transformer->facade_markup( 'm_LNspjrlyM', 'Root <script>alert(1)</script>' );

		$this->assertStringNotContainsString( '<script>', $output );
		$this->assertStringContainsString( 'Root &lt;script&gt;alert(1)&lt;/script&gt;', $output );
	}

	public function test_facade_markup_rejects_invalid_ids() {
		$this->assertSame( '', $this->transformer->facade_markup( 'not a video' ) );
		$this->assertSame( '', $this->transformer->facade_markup( '<script>' ) );
	}

	public function test_the_content_filter_strips_hero_api_and_replaces_embed() {
		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript -- fixture HTML from the homepage.
		$content = '<div class="mzm-background-video"><div id="yt-player"></div></div>'
			. '<script src="https://www.youtube.com/iframe_api"></script>'
			. '<script>function onYouTubeIframeAPIReady(){ new YT.Player("yt-player", { videoId: "m_LNspjrlyM" }); }</script>'
			. '<iframe title="Root Fellows" src="https://www.youtube.com/embed/m_LNspjrlyM"></iframe>';
		// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript

		$output = apply_filters( 'the_content', $content );

		$this->assertStringNotContainsString( 'iframe_api', $output );
		$this->assertStringNotContainsString( 'onYouTubeIframeAPIReady', $output );
		$this->assertStringNotContainsString( 'youtube.com/embed', $output );
		$this->assertStringContainsString( 'bp-yt-lite', $output );
	}

	public function test_oembed_filter_replaces_youtube_html() {
		$html = '<iframe title="Root Fellows" src="https://www.youtube.com/embed/m_LNspjrlyM?feature=oembed"></iframe>';

		$output = apply_filters( 'embed_oembed_html', $html, 'https://www.youtube.com/watch?v=m_LNspjrlyM' );

		$this->assertStringContainsString( 'bp-yt-lite', $output );
		$this->assertStringNotContainsString( '<iframe', $output );
	}
}
