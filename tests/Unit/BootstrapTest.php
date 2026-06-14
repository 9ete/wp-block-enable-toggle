<?php
/**
 * Harness sanity checks.
 *
 * Confirms the no-DB bootstrap loads the plugin classes and the core
 * "disabled" decision works at a basic level. Full behavioural coverage
 * lives in the BET-2 test suite.
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

namespace BlockEnableToggle\Tests\Unit;

use BlockEnableToggle\Frontend_Renderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BlockEnableToggle\Frontend_Renderer
 */
final class BootstrapTest extends TestCase {

	/**
	 * The plugin classes load from the bootstrap.
	 */
	public function test_plugin_classes_are_available(): void {
		$this->assertTrue( class_exists( \BlockEnableToggle\Plugin::class ) );
		$this->assertTrue( class_exists( \BlockEnableToggle\Editor_Assets::class ) );
		$this->assertTrue( class_exists( \BlockEnableToggle\Frontend_Renderer::class ) );
	}

	/**
	 * A block with no attribute is treated as enabled.
	 */
	public function test_block_without_attribute_is_enabled(): void {
		$renderer = new Frontend_Renderer();

		$this->assertFalse( $renderer->is_disabled( array( 'blockName' => 'core/paragraph' ) ) );
	}

	/**
	 * A block with the attribute set to false is treated as disabled.
	 */
	public function test_block_with_false_attribute_is_disabled(): void {
		$renderer = new Frontend_Renderer();

		$this->assertTrue( $renderer->is_disabled( array( 'attrs' => array( 'betEnabled' => false ) ) ) );
	}
}
