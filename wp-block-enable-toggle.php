<?php
/**
 * Plugin Name: Block Enable/Disable Toggle (Global)
 * Description: Adds an "Enabled" toggle to all blocks. When disabled, the block is not rendered on the front end.
 * Version: 0.0.1
 * Author: 9ete
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_Block_Enable_Toggle')) {
    final class WP_Block_Enable_Toggle
    {
        private const ASSET_HANDLE = 'wp-block-toggle';

        public function __construct()
        {
            add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
            add_filter('render_block', [$this, 'maybe_omit_block_on_frontend'], 10, 2);
        }

        public function enqueue_editor_assets(): void
        {
            $dir = plugin_dir_path(__FILE__) . 'assets/wp-block-toggle.js';
            $url = plugin_dir_url(__FILE__) . 'assets/wp-block-toggle.js';

            wp_register_script(
                self::ASSET_HANDLE,
                $url,
                [
                    'wp-hooks',
                    'wp-element',
                    'wp-components',
                    'wp-i18n',
                    'wp-data',
                    'wp-block-editor',
                    'wp-edit-post',
                    'wp-compose',
                    'wp-blocks',
                ],
                file_exists($dir) ? filemtime($dir) : false,
                true
            );

            wp_enqueue_script(self::ASSET_HANDLE);
        }

        public function maybe_omit_block_on_frontend(string $block_content, array $block): string
        {
            if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
                return $block_content;
            }

            $attrs = isset($block['attrs']) && is_array($block['attrs']) ? $block['attrs'] : [];
            if (array_key_exists('wpBlockEnabled', $attrs) && $attrs['wpBlockEnabled'] === false) {
                return '';
            }

            return $block_content;
        }
    }

    new WP_Block_Enable_Toggle();
}
