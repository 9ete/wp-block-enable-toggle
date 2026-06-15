<?php
/**
 * Tests for the editor asset loader.
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

namespace BlockEnableToggle\Tests\Unit;

use BlockEnableToggle\Editor_Assets;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( Editor_Assets::class )]
final class EditorAssetsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['_bet_enqueued_scripts']    = array();
		$GLOBALS['_bet_enqueued_styles']     = array();
		$GLOBALS['_bet_script_translations'] = array();
		$GLOBALS['_bet_style_data']          = array();
	}

	public function test_asset_data_reads_a_manifest(): void {
		// Use a fixture manifest so the unit test does not depend on the real
		// compiled build output.
		$assets = new class() extends Editor_Assets {
			protected function manifest_path(): string {
				return __DIR__ . '/../fixtures/index.asset.php';
			}
		};

		$data = $assets->asset_data();

		$this->assertSame( array( 'wp-element', 'wp-i18n' ), $data['dependencies'] );
		$this->assertSame( 'fixtureversion123', $data['version'] );
	}

	public function test_asset_data_falls_back_when_manifest_missing(): void {
		$assets = new class() extends Editor_Assets {
			protected function manifest_path(): string {
				return '/does/not/exist/index.asset.php';
			}
		};

		$data = $assets->asset_data();

		$this->assertSame( array(), $data['dependencies'] );
		$this->assertSame( BLOCK_ENABLE_TOGGLE_VERSION, $data['version'] );
	}

	public function test_enqueue_registers_script_in_footer(): void {
		( new Editor_Assets() )->enqueue();

		$this->assertCount( 1, $GLOBALS['_bet_enqueued_scripts'] );
		$script = $GLOBALS['_bet_enqueued_scripts'][0];
		$this->assertSame( 'block-enable-toggle-editor', $script['handle'] );
		$this->assertStringEndsWith( 'build/index.js', $script['src'] );
		$this->assertTrue( $script['in_footer'] );
	}

	public function test_enqueue_uses_manifest_version_and_dependencies(): void {
		$assets   = new Editor_Assets();
		$expected = $assets->asset_data();
		$assets->enqueue();

		$script = $GLOBALS['_bet_enqueued_scripts'][0];
		$this->assertSame( $expected['version'], $script['ver'] );
		$this->assertSame( $expected['dependencies'], $script['deps'] );
	}

	public function test_enqueue_registers_style_with_rtl(): void {
		( new Editor_Assets() )->enqueue();

		$this->assertCount( 1, $GLOBALS['_bet_enqueued_styles'] );
		$this->assertSame( 'block-enable-toggle-editor', $GLOBALS['_bet_enqueued_styles'][0]['handle'] );
		$this->assertStringEndsWith( 'build/index.css', $GLOBALS['_bet_enqueued_styles'][0]['src'] );

		$this->assertCount( 1, $GLOBALS['_bet_style_data'] );
		$this->assertSame( 'rtl', $GLOBALS['_bet_style_data'][0]['key'] );
		$this->assertSame( 'replace', $GLOBALS['_bet_style_data'][0]['value'] );
	}

	public function test_enqueue_sets_script_translations(): void {
		( new Editor_Assets() )->enqueue();

		$this->assertCount( 1, $GLOBALS['_bet_script_translations'] );
		$this->assertSame( 'block-enable-toggle', $GLOBALS['_bet_script_translations'][0]['domain'] );
	}
}
