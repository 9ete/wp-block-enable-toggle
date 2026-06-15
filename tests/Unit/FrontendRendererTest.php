<?php
/**
 * Tests for the front-end render filter.
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

namespace BlockEnableToggle\Tests\Unit;

use BlockEnableToggle\Frontend_Renderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass( Frontend_Renderer::class )]
final class FrontendRendererTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_bet_is_admin'] = false;
	}

	/**
	 * Truth table for is_disabled(): a block is disabled only when the
	 * attribute is present and does not validate as boolean true.
	 *
	 * @return array<string, array{0: array, 1: bool}>
	 */
	public static function disabled_provider(): array {
		return array(
			'absent attribute'   => array( array( 'blockName' => 'core/paragraph' ), false ),
			'no attrs key'       => array( array(), false ),
			'attrs not an array' => array( array( 'attrs' => 'nope' ), false ),
			'boolean false'      => array( array( 'attrs' => array( 'betEnabled' => false ) ), true ),
			'boolean true'       => array( array( 'attrs' => array( 'betEnabled' => true ) ), false ),
			'string "false"'     => array( array( 'attrs' => array( 'betEnabled' => 'false' ) ), true ),
			'string "FALSE"'     => array( array( 'attrs' => array( 'betEnabled' => 'FALSE' ) ), true ),
			'integer 0'          => array( array( 'attrs' => array( 'betEnabled' => 0 ) ), true ),
			'string "0"'         => array( array( 'attrs' => array( 'betEnabled' => '0' ) ), true ),
			'empty string'       => array( array( 'attrs' => array( 'betEnabled' => '' ) ), true ),
			'null'               => array( array( 'attrs' => array( 'betEnabled' => null ) ), true ),
			'array value'        => array( array( 'attrs' => array( 'betEnabled' => array() ) ), true ),
			'integer 1'          => array( array( 'attrs' => array( 'betEnabled' => 1 ) ), false ),
			'string "true"'      => array( array( 'attrs' => array( 'betEnabled' => 'true' ) ), false ),
			'string "yes"'       => array( array( 'attrs' => array( 'betEnabled' => 'yes' ) ), false ),
			'string "on"'        => array( array( 'attrs' => array( 'betEnabled' => 'on' ) ), false ),
		);
	}

	#[DataProvider( 'disabled_provider' )]
	public function test_is_disabled( array $block, bool $expected ): void {
		$renderer = new Frontend_Renderer();
		$this->assertSame( $expected, $renderer->is_disabled( $block ) );
	}

	public function test_front_end_removes_disabled_block(): void {
		$renderer = new Frontend_Renderer();
		$block    = array( 'attrs' => array( 'betEnabled' => false ) );
		$this->assertSame( '', $renderer->filter_block( 'CONTENT', $block ) );
	}

	public function test_front_end_keeps_enabled_block(): void {
		$renderer = new Frontend_Renderer();
		$block    = array( 'attrs' => array( 'betEnabled' => true ) );
		$this->assertSame( 'CONTENT', $renderer->filter_block( 'CONTENT', $block ) );
	}

	public function test_front_end_keeps_block_without_attribute(): void {
		$renderer = new Frontend_Renderer();
		$block    = array( 'blockName' => 'core/paragraph' );
		$this->assertSame( 'CONTENT', $renderer->filter_block( 'CONTENT', $block ) );
	}

	public function test_front_end_keeps_block_with_malformed_attrs(): void {
		$renderer = new Frontend_Renderer();
		$this->assertSame( 'CONTENT', $renderer->filter_block( 'CONTENT', array( 'attrs' => 'nope' ) ) );
	}

	public function test_admin_keeps_disabled_block(): void {
		$GLOBALS['_bet_is_admin'] = true;
		$renderer                 = new Frontend_Renderer();
		$block                    = array( 'attrs' => array( 'betEnabled' => false ) );
		$this->assertSame( 'CONTENT', $renderer->filter_block( 'CONTENT', $block ) );
	}

	public function test_admin_is_editor_request(): void {
		$GLOBALS['_bet_is_admin'] = true;
		$renderer                 = new Frontend_Renderer();
		$this->assertTrue( $renderer->is_editor_request() );
	}

	public function test_plain_front_end_is_not_editor_request(): void {
		// Not admin, not a REST request (default is_rest_request() is false here).
		$renderer = new Frontend_Renderer();
		$this->assertFalse( $renderer->is_editor_request() );
	}

	public function test_capture_rest_route_returns_response_unchanged(): void {
		$renderer = new Frontend_Renderer();
		$response = (object) array( 'foo' => 'bar' );
		$result   = $renderer->capture_rest_route( $response, null, $this->mock_request( '/wp/v2/posts/1' ) );
		$this->assertSame( $response, $result );
	}

	public function test_rest_block_renderer_keeps_disabled_block(): void {
		$renderer = $this->rest_renderer();
		$renderer->capture_rest_route( null, null, $this->mock_request( '/wp/v2/block-renderer/core/paragraph' ) );

		$this->assertTrue( $renderer->is_editor_request() );
		$block = array( 'attrs' => array( 'betEnabled' => false ) );
		$this->assertSame( 'CONTENT', $renderer->filter_block( 'CONTENT', $block ) );
	}

	public function test_rest_content_route_removes_disabled_block(): void {
		$renderer = $this->rest_renderer();
		$renderer->capture_rest_route( null, null, $this->mock_request( '/wp/v2/posts/1' ) );

		$this->assertFalse( $renderer->is_editor_request() );
		$block = array( 'attrs' => array( 'betEnabled' => false ) );
		$this->assertSame( '', $renderer->filter_block( 'CONTENT', $block ) );
	}

	public function test_rest_with_empty_route_is_not_editor(): void {
		// A REST request that never matched a route must be treated as front end.
		$renderer = $this->rest_renderer();
		$this->assertFalse( $renderer->is_editor_request() );
	}

	public function test_capture_rest_route_resets_memo_between_requests(): void {
		$renderer = $this->rest_renderer();

		$renderer->capture_rest_route( null, null, $this->mock_request( '/wp/v2/block-renderer/core/paragraph' ) );
		$this->assertTrue( $renderer->is_editor_request() );

		// A later sub-request on the same instance must re-evaluate, not reuse
		// the stale memoized answer.
		$renderer->capture_rest_route( null, null, $this->mock_request( '/wp/v2/posts/1' ) );
		$this->assertFalse( $renderer->is_editor_request() );
	}

	public function test_capture_rest_route_ignores_null_request(): void {
		$renderer = $this->rest_renderer();
		$response = (object) array( 'x' => 1 );

		$this->assertSame( $response, $renderer->capture_rest_route( $response, null, null ) );
		// No route was captured, so this is not the block-renderer preview.
		$this->assertFalse( $renderer->is_editor_request() );
	}

	public function test_capture_rest_route_ignores_request_without_get_route(): void {
		$renderer = $this->rest_renderer();
		$renderer->capture_rest_route( null, null, new \stdClass() );
		$this->assertFalse( $renderer->is_editor_request() );
	}

	public function test_is_editor_request_is_memoized(): void {
		$GLOBALS['_bet_is_admin'] = true;
		$renderer                 = new Frontend_Renderer();
		$this->assertTrue( $renderer->is_editor_request() );

		// Changing the underlying state does not change the memoized answer.
		$GLOBALS['_bet_is_admin'] = false;
		$this->assertTrue( $renderer->is_editor_request() );
	}

	/**
	 * A renderer whose is_rest_request() reports true, without touching the
	 * process-global REST_REQUEST constant.
	 *
	 * @return Frontend_Renderer
	 */
	private function rest_renderer(): Frontend_Renderer {
		return new class() extends Frontend_Renderer {
			protected function is_rest_request(): bool {
				return true;
			}
		};
	}

	/**
	 * Minimal stand-in for a WP_REST_Request exposing get_route().
	 *
	 * @param string $route Route to report.
	 * @return object
	 */
	private function mock_request( string $route ) {
		return new class( $route ) {
			/**
			 * @var string
			 */
			private $route;

			public function __construct( string $route ) {
				$this->route = $route;
			}

			public function get_route(): string {
				return $this->route;
			}
		};
	}
}
