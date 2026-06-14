<?php
/**
 * Harness sanity check.
 *
 * Confirms the no-database bootstrap loads the plugin classes. Behavioural
 * coverage lives in the per-class test files.
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

namespace BlockEnableToggle\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class BootstrapTest extends TestCase {

	public function test_plugin_classes_are_available(): void {
		$this->assertTrue( class_exists( \BlockEnableToggle\Plugin::class ) );
		$this->assertTrue( class_exists( \BlockEnableToggle\Editor_Assets::class ) );
		$this->assertTrue( class_exists( \BlockEnableToggle\Frontend_Renderer::class ) );
	}
}
