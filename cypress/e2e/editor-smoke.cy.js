/**
 * Editor smoke: the plugin's editor integration loads and registers its
 * filters, and the betEnabled attribute is applied to core blocks. This
 * checks the integration through the public wp.* APIs rather than driving
 * fragile editor UI.
 */

describe( 'Block Enable Toggle — editor integration', () => {
	beforeEach( () => {
		cy.login();
	} );

	it( 'registers the plugin filters in the editor', () => {
		cy.visit( '/wp-admin/post-new.php' );
		cy.get( '.block-editor', { timeout: 30000 } ).should( 'exist' );

		cy.window()
			.its( 'wp.hooks' )
			.then( ( hooks ) => {
				expect(
					hooks.hasFilter(
						'blocks.registerBlockType',
						'block-enable-toggle/attribute'
					)
				).to.eq( true );
				expect(
					hooks.hasFilter(
						'editor.BlockEdit',
						'block-enable-toggle/with-controls'
					)
				).to.eq( true );
				expect(
					hooks.hasFilter(
						'editor.BlockListBlock',
						'block-enable-toggle/disabled-canvas-class'
					)
				).to.eq( true );
			} );
	} );

	it( 'adds the betEnabled attribute (default true) to a core block', () => {
		cy.visit( '/wp-admin/post-new.php' );
		cy.get( '.block-editor', { timeout: 30000 } ).should( 'exist' );

		cy.window()
			.its( 'wp.blocks' )
			.then( ( blocks ) => {
				const settings = blocks.getBlockType( 'core/paragraph' );
				expect( settings.attributes ).to.have.property( 'betEnabled' );
				expect( settings.attributes.betEnabled.type ).to.eq( 'boolean' );
				expect( settings.attributes.betEnabled.default ).to.eq( true );
			} );
	} );

	it( 'loads the editor stylesheet', () => {
		cy.visit( '/wp-admin/post-new.php' );
		cy.get( '.block-editor', { timeout: 30000 } ).should( 'exist' );
		cy.get( 'link[href*="block-enable-toggle"][href*="index.css"]' ).should(
			'exist'
		);
	} );
} );
