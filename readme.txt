=== Block Enable Toggle ===
Contributors: 9ete
Tags: block editor, gutenberg, visibility, blocks, hide
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add an Enabled toggle to every block. Disabled blocks stay in the editor but are never rendered on the front end.

== Description ==

Block Enable Toggle adds a simple **Enabled** switch to every block in the
WordPress block editor. Turn a block off and it stays right where it is in the
editor — handy for drafts, seasonal content, or work in progress — but it is
never rendered on the front end.

The enabled state is removed server-side, so disabled content is never sent to
the browser, your feeds, or the REST `content.rendered` output.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory, or install it
   through the Plugins screen in WordPress.
2. Activate the plugin through the Plugins screen.
3. Edit any post or page. Select a block and open the **Visibility** panel in
   the block inspector to toggle it on or off.

== Frequently Asked Questions ==

= Does this delete my content? =

No. Disabling a block only hides it from the front end. The block and its
content remain in the editor and can be re-enabled at any time.

== Changelog ==

= 1.0.0 =
* Initial release.
