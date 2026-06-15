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

**What it does**

* Adds a **Visibility** toggle to the block inspector sidebar (and a quick-access
  toolbar button) for every block.
* Disabled blocks are dimmed on the canvas with a dashed outline and a "Disabled"
  label so you can tell at a glance which blocks are off.
* A red indicator appears next to the block in the document **List View** when a
  block is disabled.
* The enabled state is enforced server-side, so disabled content is never sent to
  the browser, your feeds, or the REST `content.rendered` field.

**Works with**

* All core static blocks (paragraph, heading, image, list, etc.)
* Dynamic blocks (latest posts, RSS, search, and all other server-rendered blocks)
* Container blocks with inner blocks (group, columns, cover, media-text, etc.)
* Synced patterns — disable the pattern at one location without affecting other
  usages.
* Widget areas in classic themes (widget blocks also use `render_block`).
* Full-site editing template parts.

**Known limitations**

* Disabling all children of a container leaves the container's wrapper element
  in the page source. Also disable the outer container to suppress the wrapper.
* Blocks inside a **synced pattern** share content across all usages; disabling
  an inner block affects every place the pattern appears. Disable the outer
  `core/block` reference to hide the pattern at a specific location only.
* Blocks inside a **Query Loop** post template apply to every post in the loop.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory, or install it
   through the Plugins screen in WordPress → Plugins → Add New Plugin.
2. Activate the plugin through the Plugins → Installed Plugins screen.
3. Edit any post or page. Select a block and open the **Visibility** panel in
   the block inspector sidebar (right side panel), or click the eye icon in the
   block toolbar.

== Frequently Asked Questions ==

= Does this delete my content? =

No. Disabling a block only hides it from the front end. The block and its
content remain in the editor and can be re-enabled at any time.

= Will disabled blocks appear in Google search results or RSS feeds? =

No. The plugin filters block output on the server, so disabled blocks are never
included in page HTML, REST API `content.rendered` responses, or your site's RSS
feed.

= Does it work with all block types? =

It works with all static and dynamic core blocks. See the "Works with" list in
the Description for supported types and the "Known limitations" section for
edge cases.

= Does this affect page performance? =

No. The filter is a simple attribute check with no database queries. The only
overhead is a boolean evaluation per block on each request.

= Is the enabled state preserved if I export and import post content? =

Yes. The `betEnabled: false` attribute is stored in the block comment delimiter
(e.g. `<!-- wp:paragraph {"betEnabled":false} -->`), which is part of the post
content. It survives copy-paste, export/import, and block duplication.

= Does it work with the Classic editor (TinyMCE)? =

No. The plugin is designed for the block editor (Gutenberg). It has no effect
on content edited with the Classic editor plugin.

= Is there a way to disable a block temporarily without losing its content? =

Yes — that is exactly what this plugin is for. Disable the block to hide it from
the front end, then re-enable it whenever you want it to appear again.

== Screenshots ==

1. The block canvas showing an enabled block and a disabled block dimmed with a
   dashed outline and "Disabled" label.
2. The Visibility toggle in the block inspector sidebar.
3. The published page with the disabled block omitted from the front end.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release — no upgrade steps required.
