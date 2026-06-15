/**
 * Log into wp-admin and cache the session.
 *
 * @param {string} user Username.
 * @param {string} pass Password.
 */
Cypress.Commands.add( 'login', ( user = 'admin', pass = 'password' ) => {
	cy.session( [ user, pass ], () => {
		cy.visit( '/wp-login.php' );
		cy.get( '#user_login', { timeout: 20000 } )
			.should( 'be.visible' )
			.clear()
			.type( user );
		cy.get( '#user_pass' ).clear().type( `${ pass }{enter}`, { log: false } );
		cy.location( 'pathname', { timeout: 30000 } ).should(
			'include',
			'/wp-admin'
		);
	} );
} );

/**
 * Open the block editor for a post and wait for it to be interactive,
 * dismissing the welcome guide if it appears.
 *
 * @param {number|string} postId Post ID to edit.
 */
Cypress.Commands.add( 'openEditor', ( postId ) => {
	cy.visit( `/wp-admin/post.php?post=${ postId }&action=edit` );
	cy.get( '.block-editor', { timeout: 30000 } ).should( 'exist' );
	cy.get( 'body' ).then( ( $body ) => {
		const close = $body.find(
			'.components-modal__screen-overlay button[aria-label="Close"]'
		);
		if ( close.length ) {
			cy.wrap( close.first() ).click( { force: true } );
		}
	} );
} );
