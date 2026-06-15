/**
 * Primary user flow: a disabled block is removed from the front end while an
 * enabled block renders normally, with no PHP notices.
 */

const VISIBLE = 'BET-VISIBLE-E2E';
const HIDDEN = 'BET-HIDDEN-E2E';

const CONTENT =
	`<!-- wp:paragraph --><p>${ VISIBLE }</p><!-- /wp:paragraph -->` +
	`<!-- wp:paragraph {"betEnabled":false} --><p>${ HIDDEN }</p><!-- /wp:paragraph -->`;

describe( 'Block Enable Toggle — front-end rendering', () => {
	let postId;

	before( () => {
		cy.task( 'createPost', { title: 'BET Primary Flow', content: CONTENT } ).then(
			( post ) => {
				postId = post.id;
				expect( postId, 'seeded post id' ).to.match( /^\d+$/ );
			}
		);
	} );

	after( () => {
		if ( postId ) {
			cy.task( 'deletePost', postId );
		}
	} );

	it( 'activates the plugin', () => {
		cy.task( 'wpCli', 'plugin list --status=active --field=name' ).then(
			( out ) => {
				expect( out ).to.contain( 'block-enable-toggle' );
			}
		);
	} );

	it( 'renders enabled blocks and omits disabled blocks', () => {
		cy.visit( `/?p=${ postId }` );
		cy.contains( VISIBLE ).should( 'exist' );
		cy.contains( HIDDEN ).should( 'not.exist' );
	} );

	it( 'emits no PHP errors on the rendered page', () => {
		cy.request( `/?p=${ postId }` )
			.its( 'body' )
			.should( ( body ) => {
				expect( body ).to.not.match(
					/(Fatal error|Parse error|Warning:|Notice:|Deprecated:)/
				);
			} );
	} );

	it( 'keeps disabled blocks out of the REST content.rendered field', () => {
		// Use the rest_route query form so the test does not depend on
		// pretty-permalink configuration.
		cy.request( `/?rest_route=/wp/v2/posts/${ postId }` )
			.its( 'body.content.rendered' )
			.should( ( rendered ) => {
				expect( rendered ).to.contain( VISIBLE );
				expect( rendered ).to.not.contain( HIDDEN );
			} );
	} );
} );
