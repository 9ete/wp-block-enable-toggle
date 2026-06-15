<?php
/**
 * Uninstall handler for Block Enable Toggle.
 *
 * The plugin stores its state exclusively in block attributes inside post
 * content (the `betEnabled` attribute). It creates no options, post meta,
 * custom tables, scheduled events, or transients, so there is nothing to
 * remove when the plugin is deleted. This file satisfies the WordPress
 * uninstall contract and is the home for cleanup should persistent storage
 * be introduced in a future version.
 *
 * @package BlockEnableToggle
 */

declare( strict_types=1 );

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
