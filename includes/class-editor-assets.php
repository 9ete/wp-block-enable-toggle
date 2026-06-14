<?php
/**
 * Editor asset loader.
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

namespace BlockEnableToggle;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues the block editor script that adds the "Enabled" toggle.
 */
class Editor_Assets {

	/**
	 * Script handle.
	 */
	const HANDLE = 'block-enable-toggle-editor';

	/**
	 * Enqueue the compiled editor script, its styles, and its translations.
	 */
	public function enqueue(): void {
		$asset = $this->asset_data();

		wp_enqueue_script(
			self::HANDLE,
			BLOCK_ENABLE_TOGGLE_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_set_script_translations(
			self::HANDLE,
			'block-enable-toggle',
			BLOCK_ENABLE_TOGGLE_DIR . 'languages'
		);

		wp_enqueue_style(
			self::HANDLE,
			BLOCK_ENABLE_TOGGLE_URL . 'build/index.css',
			array(),
			$asset['version']
		);

		wp_style_add_data( self::HANDLE, 'rtl', 'replace' );
	}

	/**
	 * Read the build manifest produced by @wordpress/scripts.
	 *
	 * Falls back to the plugin version with no extra dependencies when the
	 * build has not run yet, so a missing manifest never fatals.
	 *
	 * @return array Associative array with 'dependencies' (string[]) and 'version' (string).
	 */
	public function asset_data(): array {
		$asset_path = BLOCK_ENABLE_TOGGLE_DIR . 'build/index.asset.php';

		$defaults = array(
			'dependencies' => array(),
			'version'      => BLOCK_ENABLE_TOGGLE_VERSION,
		);

		if ( ! file_exists( $asset_path ) ) {
			return $defaults;
		}

		$asset = require $asset_path;

		if ( ! is_array( $asset ) ) {
			return $defaults;
		}

		return array(
			'dependencies' => isset( $asset['dependencies'] ) && is_array( $asset['dependencies'] ) ? $asset['dependencies'] : array(),
			'version'      => isset( $asset['version'] ) && is_string( $asset['version'] ) ? $asset['version'] : BLOCK_ENABLE_TOGGLE_VERSION,
		);
	}
}
