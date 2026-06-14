<?php
/**
 * Plugin Name:       Block Enable Toggle
 * Plugin URI:        https://lowermedia.net/plugins/block-enable-toggle
 * Description:       Adds an "Enabled" toggle to every block. Disabled blocks stay in the editor but are never rendered on the front end.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            9ete
 * Author URI:        https://lowermedia.net
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block-enable-toggle
 * Domain Path:       /languages
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

namespace BlockEnableToggle;

defined( 'ABSPATH' ) || exit;

define( 'BET_VERSION', '1.0.0' );
define( 'BET_PLUGIN_FILE', __FILE__ );
define( 'BET_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BET_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once BET_PLUGIN_DIR . 'includes/class-plugin.php';
require_once BET_PLUGIN_DIR . 'includes/class-editor-assets.php';
require_once BET_PLUGIN_DIR . 'includes/class-frontend-renderer.php';

Plugin::instance()->init();
