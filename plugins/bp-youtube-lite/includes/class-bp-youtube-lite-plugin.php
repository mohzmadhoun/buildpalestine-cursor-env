<?php
/**
 * Plugin bootstrap.
 *
 * @package BP_Youtube_Lite
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wires YouTube lite embeds into WordPress.
 */
class BP_Youtube_Lite_Plugin {

	/**
	 * Shared instance.
	 *
	 * @var BP_Youtube_Lite_Plugin|null
	 */
	private static $instance = null;

	/**
	 * HTML transformer.
	 *
	 * @var BP_Youtube_Lite_Transformer
	 */
	private $transformer;

	/**
	 * Builds the transformer.
	 */
	private function __construct() {
		$this->transformer = new BP_Youtube_Lite_Transformer();
	}

	/**
	 * Returns the shared instance.
	 *
	 * @return BP_Youtube_Lite_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns the HTML transformer.
	 *
	 * @return BP_Youtube_Lite_Transformer
	 */
	public function transformer() {
		return $this->transformer;
	}

	/**
	 * Registers every hook the plugin uses.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'filter_the_content' ), 20 );
		add_filter( 'render_block', array( $this, 'filter_render_block' ), 10, 2 );
	}

	/**
	 * Enqueues the facade stylesheet and click-to-load script.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'bp-youtube-lite',
			BP_YOUTUBE_LITE_URL . 'assets/css/bp-youtube-lite.css',
			array(),
			BP_YOUTUBE_LITE_VERSION
		);

		wp_enqueue_script(
			'bp-youtube-lite',
			BP_YOUTUBE_LITE_URL . 'assets/js/bp-youtube-lite.js',
			array(),
			BP_YOUTUBE_LITE_VERSION,
			true
		);
	}

	/**
	 * Replaces a YouTube oEmbed iframe with a facade.
	 *
	 * @param string $html The cached HTML result, stored in post_meta.
	 * @param string $url  The attempted embed URL.
	 * @return string
	 */
	public function filter_embed_oembed_html( $html, $url ) {
		$id = $this->transformer->extract_video_id( $url );

		if ( '' === $id ) {
			$id = $this->transformer->extract_video_id( (string) $html );
		}

		if ( '' === $id ) {
			return $html;
		}

		$title = '';
		if ( preg_match( '/\btitle=(["\'])(.*?)\1/i', (string) $html, $matches ) ) {
			$title = html_entity_decode( $matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}

		return $this->transformer->facade_markup( $id, $title );
	}

	/**
	 * Strips the hero YouTube API and any leftover YouTube iframes from post HTML.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function filter_the_content( $content ) {
		return $this->transformer->transform( (string) $content );
	}

	/**
	 * Transforms rendered HTML blocks that still contain YouTube players.
	 *
	 * @param string               $block_content Rendered block HTML.
	 * @param array<string, mixed> $block         Parsed block.
	 * @return string
	 */
	public function filter_render_block( $block_content, $block ) {
		if ( empty( $block['blockName'] ) ) {
			return $block_content;
		}

		$needs_transform = (
			false !== strpos( (string) $block_content, 'youtube.com' )
			|| false !== strpos( (string) $block_content, 'youtu.be' )
			|| false !== strpos( (string) $block_content, 'yt-player' )
			|| false !== strpos( (string) $block_content, 'onYouTubeIframeAPIReady' )
		);

		if ( ! $needs_transform ) {
			return $block_content;
		}

		return $this->transformer->transform( (string) $block_content );
	}
}
