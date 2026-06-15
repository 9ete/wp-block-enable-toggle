<?php
/**
 * Front-end render filter.
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

namespace BlockEnableToggle;

defined( 'ABSPATH' ) || exit;

/**
 * Removes disabled blocks from front-end output.
 */
class Frontend_Renderer {

	/**
	 * Block attribute that stores the enabled state.
	 */
	const ATTRIBUTE = 'betEnabled';

	/**
	 * Route of the in-flight REST request, captured before its callback runs.
	 *
	 * @var string
	 */
	private $rest_route = '';

	/**
	 * Memoized result of {@see Frontend_Renderer::is_editor_request()}.
	 *
	 * @var bool|null
	 */
	private $is_editor = null;

	/**
	 * Record the current REST route so editor previews can be detected.
	 *
	 * Hooked on `rest_request_before_callbacks`, which fires before the route
	 * callback (and therefore before any `render_block` call it makes).
	 *
	 * @param mixed $response Unused; returned untouched.
	 * @param mixed $handler  Unused route handler.
	 * @param mixed $request  The current request object.
	 * @return mixed The unmodified response.
	 */
	public function capture_rest_route( $response, $handler, $request ) {
		if ( is_object( $request ) && method_exists( $request, 'get_route' ) ) {
			$this->rest_route = (string) $request->get_route();
			$this->is_editor  = null;
		}

		return $response;
	}

	/**
	 * Filter callback for `render_block`.
	 *
	 * Disabled blocks are stripped from every front-end surface (page output,
	 * feeds, and the REST `content.rendered` field). They are preserved only
	 * inside the editor and its block-renderer previews so authors can still
	 * see and re-enable them.
	 *
	 * @param string $block_content Rendered block HTML.
	 * @param array  $block         Parsed block, including its attributes.
	 * @return string Block HTML, or an empty string when the block is disabled.
	 */
	public function filter_block( string $block_content, array $block ): string {
		if ( $this->is_editor_request() ) {
			return $block_content;
		}

		if ( $this->is_disabled( $block ) ) {
			return '';
		}

		return $block_content;
	}

	/**
	 * Determine whether the current request renders blocks for the editor.
	 *
	 * True for any admin-screen render and for the editor's block-renderer
	 * REST preview. All other REST requests (notably `content.rendered`) are
	 * treated as front-end output so disabled blocks are removed there too.
	 *
	 * @return bool
	 */
	public function is_editor_request(): bool {
		if ( null !== $this->is_editor ) {
			return $this->is_editor;
		}

		$is_editor = false;

		if ( is_admin() ) {
			$is_editor = true;
		} elseif ( $this->is_rest_request() ) {
			$is_editor = ( false !== strpos( $this->rest_route, '/block-renderer/' ) );
		}

		$this->is_editor = $is_editor;

		return $is_editor;
	}

	/**
	 * Whether the current request is a REST API request.
	 *
	 * Isolated so tests can exercise the REST branch without defining the
	 * process-global REST_REQUEST constant.
	 *
	 * @return bool
	 */
	protected function is_rest_request(): bool {
		return defined( 'REST_REQUEST' ) && REST_REQUEST;
	}

	/**
	 * Determine whether a parsed block has been explicitly disabled.
	 *
	 * The attribute defaults to `true` and is only serialized when an author
	 * turns it off, so an absent attribute means "enabled". A present value is
	 * evaluated with FILTER_VALIDATE_BOOLEAN: recognised truthy values (true,
	 * 1, "1", "true", "yes", "on") keep the block enabled, while any other
	 * present value (false, 0, "0", "", "false", "no", "off", null, or a
	 * non-scalar) disables it. This fails closed for imported or hand-authored
	 * markup.
	 *
	 * @param array $block Parsed block.
	 * @return bool True when the block should be hidden on the front end.
	 */
	public function is_disabled( array $block ): bool {
		$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

		if ( ! array_key_exists( self::ATTRIBUTE, $attrs ) ) {
			return false;
		}

		return false === filter_var( $attrs[ self::ATTRIBUTE ], FILTER_VALIDATE_BOOLEAN );
	}
}
