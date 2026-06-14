<?php
/**
 * Main plugin orchestrator.
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

namespace BlockEnableToggle;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the plugin's components into WordPress.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Editor asset loader.
	 *
	 * @var Editor_Assets
	 */
	private $editor_assets;

	/**
	 * Front-end render filter.
	 *
	 * @var Frontend_Renderer
	 */
	private $frontend_renderer;

	/**
	 * Retrieve the shared plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Build the component graph.
	 */
	private function __construct() {
		$this->editor_assets     = new Editor_Assets();
		$this->frontend_renderer = new Frontend_Renderer();
	}

	/**
	 * Register WordPress hooks.
	 */
	public function init(): void {
		add_action( 'enqueue_block_editor_assets', array( $this->editor_assets, 'enqueue' ) );
		add_filter( 'render_block', array( $this->frontend_renderer, 'filter_block' ), 10, 2 );
		add_filter( 'rest_request_before_callbacks', array( $this->frontend_renderer, 'capture_rest_route' ), 10, 3 );
	}
}
