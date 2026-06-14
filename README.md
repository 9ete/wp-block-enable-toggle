# Block Enable Toggle

Adds an **Enabled** toggle to every block in the WordPress block editor. When a
block is disabled it stays in the editor — so you can keep it as a draft, a
seasonal banner, or a work-in-progress — but it is never rendered on the front
end.

## How it works

- A boolean `betEnabled` attribute is registered on every block type (default
  `true`).
- A **Visibility → Enabled** toggle is added to the block inspector.
- Disabling a block stores `"betEnabled": false` in the block delimiter
  comment. Because the value lives in the comment and not the saved HTML, block
  validation is never affected.
- A server-side `render_block` filter removes disabled blocks from front-end
  output, so disabled content is never sent to the browser.

Disabled blocks are still rendered inside the editor (and its REST-driven
previews) so you can see and re-enable them.

## Requirements

- WordPress 6.6+
- PHP 7.4+

## Development

The JavaScript build uses [`@wordpress/scripts`](https://www.npmjs.com/package/@wordpress/scripts):

```bash
npm install        # install build tooling (@wordpress/scripts)
npm run build      # compile src/ → build/
npm run start      # watch mode
npm run lint:js    # lint the editor source
```

PHP linting, unit tests, end-to-end tests, and Plugin Check run through a
Composer-based gate (`composer check`) that is set up in the QA scaffold.

## License

GPL-2.0-or-later. See the plugin header for details.
