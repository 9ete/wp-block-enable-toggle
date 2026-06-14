/**
 * Block Enable Toggle — editor integration.
 *
 * Adds a boolean `betEnabled` attribute to every block and a "Visibility"
 * toggle in the block inspector. Disabled blocks are removed from the front
 * end by the PHP `render_block` filter; this file is purely the editor UI.
 */

import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __, _x } from '@wordpress/i18n';

/**
 * Name of the attribute added to every block.
 *
 * @type {string}
 */
export const ATTRIBUTE = 'betEnabled';

/**
 * Register the `betEnabled` attribute on all block types.
 *
 * Defaults to `true`, so the attribute is only serialized into block markup
 * when an author disables a block. Keeping the value in the block delimiter
 * comment (not the saved HTML) means block validation is never affected.
 *
 * @param {Object} settings Block type settings.
 * @param {string} name     Registered block name.
 * @return {Object} Settings with the attribute added.
 */
function addAttribute( settings, name ) {
	if ( ! name ) {
		return settings;
	}

	settings.attributes = settings.attributes || {};

	if ( typeof settings.attributes[ ATTRIBUTE ] === 'undefined' ) {
		settings.attributes[ ATTRIBUTE ] = {
			type: 'boolean',
			default: true,
		};
	}

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'block-enable-toggle/attribute',
	addAttribute
);

/**
 * Add the "Visibility" inspector panel with the enable toggle.
 */
const withEnableToggle = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { attributes, setAttributes } = props;
		const enabled = attributes[ ATTRIBUTE ] !== false;

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ _x(
							'Visibility',
							'block inspector panel title',
							'block-enable-toggle'
						) }
						initialOpen={ false }
					>
						<ToggleControl
							label={ _x(
								'Enabled',
								'block visibility toggle label',
								'block-enable-toggle'
							) }
							help={
								enabled
									? __(
											'Block renders on the front end.',
											'block-enable-toggle'
									  )
									: __(
											'Block is hidden on the front end.',
											'block-enable-toggle'
									  )
							}
							checked={ enabled }
							onChange={ ( value ) =>
								setAttributes( { [ ATTRIBUTE ]: value } )
							}
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withEnableToggle' );

addFilter(
	'editor.BlockEdit',
	'block-enable-toggle/with-toggle',
	withEnableToggle
);
