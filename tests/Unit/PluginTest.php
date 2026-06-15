<?php
/**
 * Tests for the plugin orchestrator.
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

namespace BlockEnableToggle\Tests\Unit;

use BlockEnableToggle\Plugin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( Plugin::class )]
final class PluginTest extends TestCase {

	public function test_instance_returns_a_singleton(): void {
		$this->assertSame( Plugin::instance(), Plugin::instance() );
	}

	public function test_init_registers_the_expected_hooks(): void {
		$GLOBALS['_bet_hooks'] = array();

		Plugin::instance()->init();

		$hooks = $GLOBALS['_bet_hooks'];
		$names = array_map( static fn( array $hook ): string => $hook['hook'], $hooks );

		$this->assertContains( 'enqueue_block_editor_assets', $names );
		$this->assertContains( 'render_block', $names );
		$this->assertContains( 'rest_request_before_callbacks', $names );
	}

	public function test_render_block_filter_uses_two_arguments(): void {
		$GLOBALS['_bet_hooks'] = array();

		Plugin::instance()->init();

		$render_block = null;
		foreach ( $GLOBALS['_bet_hooks'] as $hook ) {
			if ( 'render_block' === $hook['hook'] ) {
				$render_block = $hook;
				break;
			}
		}

		$this->assertNotNull( $render_block );
		$this->assertSame( 'filter', $render_block['type'] );
		$this->assertSame( 10, $render_block['priority'] );
		$this->assertSame( 2, $render_block['accepted_args'] );
		$this->assertIsCallable( $render_block['callback'] );
		$this->assertSame( 'filter_block', $render_block['callback'][1] );
	}

	public function test_rest_capture_filter_uses_three_arguments(): void {
		$GLOBALS['_bet_hooks'] = array();

		Plugin::instance()->init();

		$capture = null;
		foreach ( $GLOBALS['_bet_hooks'] as $hook ) {
			if ( 'rest_request_before_callbacks' === $hook['hook'] ) {
				$capture = $hook;
				break;
			}
		}

		$this->assertNotNull( $capture );
		$this->assertSame( 3, $capture['accepted_args'] );
	}
}
