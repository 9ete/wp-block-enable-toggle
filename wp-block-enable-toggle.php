<?php
/**
 * Plugin Name: Block Enable/Disable Toggle (Global)
 * Description: Adds an "Enabled" toggle to all blocks. When disabled, the block is not rendered on the front end. Shows a disabled badge in List View.
 * Version: 1.2.2
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
            $js_path = plugin_dir_path(__FILE__) . 'assets/wp-block-toggle.js';
            $js_url  = plugin_dir_url(__FILE__) . 'assets/wp-block-toggle.js';
            $css_path = plugin_dir_path(__FILE__) . 'assets/wp-block-toggle.css';
            $css_url  = plugin_dir_url(__FILE__) . 'assets/wp-block-toggle.css';

            wp_register_script(
                self::ASSET_HANDLE,
                $js_url,
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
                file_exists($js_path) ? filemtime($js_path) : time(),
                true
            );
            wp_enqueue_script(self::ASSET_HANDLE);

            wp_register_style(
                self::ASSET_HANDLE,
                $css_url,
                [],
                file_exists($css_path) ? filemtime($css_path) : time()
            );
            wp_enqueue_style(self::ASSET_HANDLE);
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
