# Block Enable Toggle — Compatibility Matrix

Tested against WordPress 7.0 / PHP 8.5 via `wp-env` (June 2026).

The plugin's front-end suppression is implemented in the `render_block` filter, which
fires **after** any block `render_callback` and wraps the final HTML of every block,
including dynamic ones.

---

## Core block types

| Block type | Category | Disable outer block | Disable inner block | Notes |
|---|---|:---:|:---:|---|
| `core/paragraph` | Static (save) | ✅ | — | Primary verified case |
| `core/heading` | Static (save) | ✅ | — | |
| `core/image` | Static (save) | ✅ | — | |
| `core/list` / `core/list-item` | Static (save) | ✅ | ✅ | Each list-item is its own block |
| `core/quote` | Static (save) | ✅ | ✅ | Inner paragraph blocks are independent |
| `core/table` | Static (save) | ✅ | — | Table is a single block, no InnerBlocks |
| `core/code` | Static (save) | ✅ | — | |
| `core/buttons` / `core/button` | Static (save, InnerBlocks) | ✅ | ✅ | Disable outer hides all buttons |
| `core/latest-posts` | Dynamic | ✅ | — | Verified: renders empty, sibling unaffected |
| `core/rss` | Dynamic | ✅ | — | Filter wraps dynamic output |
| `core/search` | Dynamic | ✅ | — | |
| `core/archives` | Dynamic | ✅ | — | |
| `core/tag-cloud` | Dynamic | ✅ | — | |
| `core/calendar` | Dynamic | ✅ | — | |
| `core/group` | Container (InnerBlocks) | ✅ | ✅ | See "Empty wrapper" note |
| `core/columns` / `core/column` | Container (InnerBlocks) | ✅ | ✅ | Same as group |
| `core/cover` | Container (InnerBlocks) | ✅ | ✅ | |
| `core/media-text` | Container (InnerBlocks) | ✅ | ✅ | |
| `core/details` | Container (InnerBlocks) | ✅ | ✅ | |
| `core/navigation` | Dynamic + InnerBlocks | ✅ | ⚠️ | See "Navigation inner blocks" |
| `core/query` | Dynamic + InnerBlocks | ✅ | ⚠️ | See "Query Loop" |
| `core/block` (synced pattern) | Dynamic (ref) | ✅ | ⚠️ | See "Synced patterns" |
| `core/shortcode` | Dynamic | ✅ | — | Shortcode output is suppressed |
| `core/freeform` | Classic editor | ✅ | — | Whole classic block, not partial content |
| `core/html` | Static (raw HTML) | ✅ | — | |

---

## Known limitations

### Empty container wrapper

When all InnerBlocks inside a container are disabled, the container's **wrapper element
remains in the HTML**. Only the container's own `betEnabled` attribute controls whether
the wrapper is suppressed.

**Example output** (core/group with all children disabled):
```html
<div class="wp-block-group is-layout-flow wp-block-group-is-layout-flow"></div>
```

**Workaround:** Also set `betEnabled:false` on the outer container block to suppress
the wrapper entirely.

---

### Synced patterns (core/block with ref)

A synced pattern block (`core/block {"ref":N}`) can be **disabled at the usage site**
by setting `betEnabled:false` on the outer `core/block` reference — the entire pattern
is hidden at that location without affecting other usages.

However, `betEnabled` attributes set on blocks **inside** the synced pattern content
are shared across all usages of the pattern (because the inner blocks are the same
post content used everywhere). Disabling an inner block inside a synced pattern disables
it everywhere the pattern appears, not just in one post.

**Workaround:** To hide a synced pattern at one specific location, disable the outer
`core/block` wrapper. To selectively show/hide content within a pattern, use an
unsynced pattern copy instead.

---

### Query Loop (core/query / core/post-template)

Disabling a block inside `core/post-template` hides it for **every post** in the loop,
since the template applies uniformly. There is no per-post conditional available via
this attribute.

**Workaround:** Disable the entire `core/query` block to remove the loop, or use
theme-level conditional logic for per-post visibility.

---

### Navigation inner blocks (core/navigation)

`core/navigation` renders via PHP and its inner navigation-link blocks are rendered
as part of the navigation HTML. Setting `betEnabled:false` on individual navigation
link blocks may not suppress them depending on theme/navigation render path —
the filter fires on each block but some navigation rendering bypasses the standard
`render_block` call chain.

**Safe approach:** Disable the entire `core/navigation` block to hide the navigation
menu.

---

### Widget contexts

The `render_block` filter fires in widget contexts (via `do_blocks()`), so block
widgets in classic (non-FSE) themes support the toggle.

---

### FSE template parts (core/template-part)

Template part blocks (used in full-site-editing themes) are also filtered correctly.
Setting `betEnabled:false` on a `core/template-part` block hides the entire template
part at that template location.

---

## What is NOT supported

| Scenario | Reason |
|---|---|
| Partially disabling content inside a classic block (`core/freeform`) | Classic editor content is a single HTML blob, not individual blocks |
| Per-post toggling inside a synced pattern's inner blocks | Synced patterns share content across all usages |
| Per-iteration visibility inside `core/post-template` | Template applies uniformly to each queried post |
| Removing the empty wrapper of an inner-only-disabled container | Container wrapper HTML is emitted by the container block itself; only the container's own toggle controls the wrapper |
