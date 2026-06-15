const { defineConfig } = require( 'cypress' );
const { execSync } = require( 'child_process' );

/**
 * Run a WP-CLI command inside the wp-env "cli" container and return stdout.
 *
 * @param {string} args WP-CLI arguments (without the leading "wp").
 * @return {string} Command stdout.
 */
function wpCli( args ) {
	return execSync( `wp-env run cli wp ${ args }`, { encoding: 'utf8' } );
}

/**
 * Escape a value for safe interpolation inside a shell single-quoted string.
 * Single quotes cannot be embedded inside '...', so we close the quote, add an
 * escaped literal single-quote, and reopen.
 *
 * @param {string} value Raw value.
 * @return {string} Shell-safe value for use between single quotes.
 */
function shellEscape( value ) {
	return String( value ).replace( /'/g, "'\\''");
}

module.exports = defineConfig( {
	e2e: {
		baseUrl: process.env.CYPRESS_BASE_URL || 'http://localhost:8888',
		defaultCommandTimeout: 15000,
		video: false,
		screenshotsFolder: 'cypress/screenshots',
		retries: { runMode: 1, openMode: 0 },
		// The screenshot generator is an on-demand release tool, not part of
		// the e2e gate; run it with `npm run wp-repo-screenshots`.
		excludeSpecPattern: [ '**/wp-repo-screenshots.cy.js' ],
		setupNodeEvents( on ) {
			on( 'task', {
				wpCli( args ) {
					return wpCli( args );
				},
				createPost( { title, content } ) {
					const out = execSync(
						`wp-env run cli wp post create --post_title='${ shellEscape( title ) }' --post_status=publish --post_content='${ shellEscape( content ) }' --porcelain`,
						{ encoding: 'utf8' }
					);
					const id = out
						.split( '\n' )
						.map( ( line ) => line.trim() )
						.filter( ( line ) => /^\d+$/.test( line ) )
						.pop();
					return { id };
				},
				deletePost( id ) {
					return wpCli( `post delete ${ id } --force` );
				},
			} );
		},
	},
} );
