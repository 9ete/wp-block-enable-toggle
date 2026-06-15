/**
 * Generates WordPress.org screenshots. Not part of the e2e gate (excluded in
 * cypress.config.js); run on demand with `npm run wp-repo-screenshots`, then
 * curate the output from cypress/screenshots into .wordpress-org/.
 *
 * The editor canvas is iframed (WP 6.3+), so blocks are selected via wp.data
 * rather than by reaching into the iframe DOM; screenshots capture the
 * rendered viewport (including the iframe) as pixels.
 */

const ENABLED = 'This paragraph is enabled and appears on the front end.';
const DISABLED = 'This paragraph is disabled, so it is hidden on the front end.';

const CONTENT =
	'<!-- wp:heading --><h2>Block Enable Toggle</h2><!-- /wp:heading -->' +
	`<!-- wp:paragraph --><p>${ ENABLED }</p><!-- /wp:paragraph -->` +
	`<!-- wp:paragraph {"betEnabled":false} --><p>${ DISABLED }</p><!-- /wp:paragraph -->`;

describe( 'WordPress.org screenshots', () => {
	let postId;

	before( () => {
		cy.task( 'createPost', {
			title: 'Block Enable Toggle demo',
			content: CONTENT,
		} ).then( ( post ) => {
			postId = post.id;
		} );
	} );

	beforeEach( () => {
		cy.login();
	} );

	after( () => {
		if ( postId ) {
			cy.task( 'deletePost', postId );
		}
	} );

	it( 'screenshot-1: editor canvas with a disabled block dimmed', () => {
		cy.openEditor( postId );
		cy.wait( 2500 );
		cy.screenshot( 'screenshot-1', { capture: 'viewport', overwrite: true } );
	} );

	it( 'screenshot-2: the Visibility toggle in the block inspector', () => {
		cy.openEditor( postId );

		cy.window().then( ( win ) => {
			const blocks = win.wp.data
				.select( 'core/block-editor' )
				.getBlocks();
			const disabled = blocks.find(
				( block ) => block.attributes.betEnabled === false
			);
			if ( disabled ) {
				win.wp.data
					.dispatch( 'core/block-editor' )
					.selectBlock( disabled.clientId );
			}
			const editPost = win.wp.data.dispatch( 'core/edit-post' );
			if ( editPost && editPost.openGeneralSidebar ) {
				editPost.openGeneralSidebar( 'edit-post/block' );
			}
		} );

		// Best-effort: expand the Visibility panel if it is present, then
		// screenshot the inspector regardless of panel/markup differences.
		cy.wait( 1500 );
		cy.get( 'body' ).then( ( $body ) => {
			const toggle = $body
				.find( 'button' )
				.filter(
					( _i, el ) =>
						/^visibility$/i.test( ( el.textContent || '' ).trim() )
				);
			if ( toggle.length ) {
				cy.wrap( toggle.first() ).click( { force: true } );
			}
		} );
		cy.wait( 800 );
		cy.screenshot( 'screenshot-2', { capture: 'viewport', overwrite: true } );
	} );

	it( 'screenshot-3: the front end with the disabled block omitted', () => {
		cy.visit( `/?p=${ postId }` );
		cy.contains( ENABLED ).should( 'exist' );
		cy.contains( DISABLED ).should( 'not.exist' );
		cy.wait( 800 );
		cy.screenshot( 'screenshot-3', { capture: 'viewport', overwrite: true } );
	} );
} );
