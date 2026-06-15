<?php
/**
 * PHPUnit bootstrap for Block Enable Toggle.
 *
 * Runs the unit suite without a WordPress installation or database. The few
 * WordPress functions the plugin touches are stubbed here and their behaviour
 * is controlled through `$GLOBALS['_bet_*']` arrays so tests can assert what
 * the plugin registered, enqueued, or read.
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

$bet_plugin_dir = dirname( __DIR__ ) . '/';

define( 'ABSPATH', sys_get_temp_dir() . '/bet-fake-wp/' );
define( 'BLOCK_ENABLE_TOGGLE_VERSION', '1.0.0' );
define( 'BLOCK_ENABLE_TOGGLE_FILE', $bet_plugin_dir . 'block-enable-toggle.php' );
define( 'BLOCK_ENABLE_TOGGLE_DIR', $bet_plugin_dir );
define( 'BLOCK_ENABLE_TOGGLE_URL', 'https://example.test/wp-content/plugins/block-enable-toggle/' );

$GLOBALS['_bet_hooks']               = array();
$GLOBALS['_bet_enqueued_scripts']    = array();
$GLOBALS['_bet_enqueued_styles']     = array();
$GLOBALS['_bet_script_translations'] = array();
$GLOBALS['_bet_style_data']          = array();
$GLOBALS['_bet_is_admin']            = false;

if ( ! function_exists( 'is_admin' ) ) {
	/**
	 * Stub for is_admin(); controlled by $GLOBALS['_bet_is_admin'].
	 *
	 * @return bool
	 */
	function is_admin() {
		return (bool) ( $GLOBALS['_bet_is_admin'] ?? false );
	}
}

if ( ! function_exists( 'add_action' ) ) {
	/**
	 * Stub for add_action(); records the registration.
	 *
	 * @param string $hook          Hook name.
	 * @param mixed  $callback      Callback.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Accepted args.
	 * @return bool
	 */
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$GLOBALS['_bet_hooks'][] = array( 'type' => 'action', 'hook' => $hook, 'callback' => $callback, 'priority' => $priority, 'accepted_args' => $accepted_args );
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * Stub for add_filter(); records the registration.
	 *
	 * @param string $hook          Hook name.
	 * @param mixed  $callback      Callback.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Accepted args.
	 * @return bool
	 */
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$GLOBALS['_bet_hooks'][] = array( 'type' => 'filter', 'hook' => $hook, 'callback' => $callback, 'priority' => $priority, 'accepted_args' => $accepted_args );
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	/**
	 * Stub for wp_enqueue_script(); records the call.
	 *
	 * @param string $handle    Handle.
	 * @param string $src       Source URL.
	 * @param array  $deps      Dependencies.
	 * @param mixed  $ver       Version.
	 * @param bool   $in_footer In footer.
	 * @return bool
	 */
	function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
		$GLOBALS['_bet_enqueued_scripts'][] = array( 'handle' => $handle, 'src' => $src, 'deps' => $deps, 'ver' => $ver, 'in_footer' => $in_footer );
		return true;
	}
}

if ( ! function_exists( 'wp_set_script_translations' ) ) {
	/**
	 * Stub for wp_set_script_translations(); records the call.
	 *
	 * @param string $handle Handle.
	 * @param string $domain Text domain.
	 * @param string $path   Path.
	 * @return bool
	 */
	function wp_set_script_translations( $handle, $domain = 'default', $path = '' ) {
		$GLOBALS['_bet_script_translations'][] = array( 'handle' => $handle, 'domain' => $domain, 'path' => $path );
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	/**
	 * Stub for wp_enqueue_style(); records the call.
	 *
	 * @param string $handle Handle.
	 * @param string $src    Source URL.
	 * @param array  $deps   Dependencies.
	 * @param mixed  $ver    Version.
	 * @param string $media  Media.
	 * @return bool
	 */
	function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
		$GLOBALS['_bet_enqueued_styles'][] = array( 'handle' => $handle, 'src' => $src, 'deps' => $deps, 'ver' => $ver, 'media' => $media );
		return true;
	}
}

if ( ! function_exists( 'wp_style_add_data' ) ) {
	/**
	 * Stub for wp_style_add_data(); records the call.
	 *
	 * @param string $handle Handle.
	 * @param string $key    Data key.
	 * @param mixed  $value  Data value.
	 * @return bool
	 */
	function wp_style_add_data( $handle, $key, $value ) {
		$GLOBALS['_bet_style_data'][] = array( 'handle' => $handle, 'key' => $key, 'value' => $value );
		return true;
	}
}

require_once BLOCK_ENABLE_TOGGLE_DIR . 'includes/class-plugin.php';
require_once BLOCK_ENABLE_TOGGLE_DIR . 'includes/class-editor-assets.php';
require_once BLOCK_ENABLE_TOGGLE_DIR . 'includes/class-frontend-renderer.php';
