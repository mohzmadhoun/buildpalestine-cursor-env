<?php
/**
 * HTML transforms that keep YouTube off the critical path.
 *
 * @package BP_Youtube_Lite
 */

defined( 'ABSPATH' ) || exit;

/**
 * Strips the homepage hero YouTube API and replaces YouTube iframes with a facade.
 */
class BP_Youtube_Lite_Transformer {

	/**
	 * YouTube video id pattern (11 characters).
	 *
	 * @var string
	 */
	const VIDEO_ID_PATTERN = '[a-zA-Z0-9_-]{11}';

	/**
	 * Extracts a YouTube video id from a URL or iframe HTML.
	 *
	 * @param string $value URL or HTML snippet.
	 * @return string Video id, or empty string when none is found.
	 */
	public function extract_video_id( $value ) {
		$value = (string) $value;

		if ( '' === $value ) {
			return '';
		}

		$patterns = array(
			'~(?:youtube(?:-nocookie)?\.com/embed/)(' . self::VIDEO_ID_PATTERN . ')~i',
			'~(?:youtube(?:-nocookie)?\.com/watch\?[^"\'\s]*v=)(' . self::VIDEO_ID_PATTERN . ')~i',
			'~(?:youtu\.be/)(' . self::VIDEO_ID_PATTERN . ')~i',
			'~(?:youtube\.com/shorts/)(' . self::VIDEO_ID_PATTERN . ')~i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $value, $matches ) ) {
				return $matches[1];
			}
		}

		return '';
	}

	/**
	 * Removes the homepage hero YouTube IFrame API so the CSS poster can paint.
	 *
	 * Keeps `#yt-player` as an empty click-to-play target, with the video id
	 * copied from the stripped player script.
	 *
	 * @param string $html HTML to transform.
	 * @return string
	 */
	public function strip_hero_youtube_api( $html ) {
		$html     = (string) $html;
		$video_id = '';

		if ( preg_match( '/videoId:\s*[\'"](' . self::VIDEO_ID_PATTERN . ')[\'"]/', $html, $matches ) ) {
			$video_id = $matches[1];
		}

		$html = preg_replace(
			'~<script[^>]+src=(["\'])https?://(?:www\.)?youtube\.com/iframe_api\1[^>]*>\s*</script>~i',
			'',
			$html
		);

		$html = preg_replace(
			'~<script\b[^>]*>\s*function\s+onYouTubeIframeAPIReady\s*\([\s\S]*?</script>~i',
			'',
			$html
		);

		$hero = '<div id="yt-player" class="bp-yt-hero-player"';
		if ( '' !== $video_id ) {
			$hero .= ' data-video-id="' . esc_attr( $video_id ) . '"';
		}
		$hero .= '></div>';

		$html = preg_replace(
			'~<div id=(["\'])yt-player\1(?:\s[^>]*)?></div>~i',
			$hero,
			$html
		);

		return is_string( $html ) ? $html : '';
	}

	/**
	 * Replaces YouTube iframes with a click-to-play facade.
	 *
	 * @param string $html HTML to transform.
	 * @return string
	 */
	public function replace_youtube_iframes( $html ) {
		$html = (string) $html;

		$replaced = preg_replace_callback(
			'~<iframe\b[^>]*>\s*</iframe>~i',
			array( $this, 'replace_iframe_match' ),
			$html
		);

		return is_string( $replaced ) ? $replaced : $html;
	}

	/**
	 * Transforms one iframe match into a facade when it is a YouTube player.
	 *
	 * @param array<int, string> $matches Preg match from replace_youtube_iframes().
	 * @return string
	 */
	public function replace_iframe_match( $matches ) {
		$iframe = $matches[0];
		$id     = $this->extract_video_id( $iframe );

		if ( '' === $id ) {
			return $iframe;
		}

		$title = '';
		if ( preg_match( '/\btitle=(["\'])(.*?)\1/i', $iframe, $title_match ) ) {
			$title = html_entity_decode( $title_match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}

		return $this->facade_markup( $id, $title );
	}

	/**
	 * Builds the facade HTML for a video id.
	 *
	 * @param string $video_id YouTube video id.
	 * @param string $title    Accessible title.
	 * @return string
	 */
	public function facade_markup( $video_id, $title = '' ) {
		$video_id = $this->sanitize_video_id( $video_id );

		if ( '' === $video_id ) {
			return '';
		}

		$title = trim( (string) $title );
		if ( '' === $title ) {
			$title = __( 'Play YouTube video', 'bp-youtube-lite' );
		}

		$poster = sprintf( 'https://i.ytimg.com/vi/%s/hqdefault.jpg', rawurlencode( $video_id ) );

		return sprintf(
			'<div class="bp-yt-lite" data-video-id="%1$s" data-title="%2$s"><button type="button" class="bp-yt-lite__button" aria-label="%2$s"><img class="bp-yt-lite__poster" src="%3$s" alt="" width="480" height="360" loading="lazy" decoding="async" /><span class="bp-yt-lite__play" aria-hidden="true"></span></button></div>',
			esc_attr( $video_id ),
			esc_attr( $title ),
			esc_url( $poster )
		);
	}

	/**
	 * Applies every first-paint transform.
	 *
	 * @param string $html HTML to transform.
	 * @return string
	 */
	public function transform( $html ) {
		$html = $this->strip_hero_youtube_api( $html );
		return $this->replace_youtube_iframes( $html );
	}

	/**
	 * Returns a video id only when it matches the YouTube id shape.
	 *
	 * @param string $video_id Candidate id.
	 * @return string
	 */
	public function sanitize_video_id( $video_id ) {
		$video_id = (string) $video_id;

		if ( preg_match( '/^' . self::VIDEO_ID_PATTERN . '$/', $video_id ) ) {
			return $video_id;
		}

		return '';
	}
}
