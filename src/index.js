/**
 * Block Enable Toggle — editor integration.
 *
 * Adds a boolean `betEnabled` attribute to every block (default true) and the
 * UI to flip it: an inspector toggle, a block-toolbar button, a dimmed canvas
 * treatment, and a List View indicator for disabled blocks. Disabled blocks
 * are removed from the front end by the PHP `render_block` filter; this file
 * is purely the editor experience.
 */

import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment } from '@wordpress/element';
import {
	InspectorControls,
	BlockControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import { __, _x } from '@wordpress/i18n';
import { select, subscribe } from '@wordpress/data';

import './editor.scss';

/**
 * Name of the attribute added to every block.
 *
 * @type {string}
 */
export const ATTRIBUTE = 'betEnabled';

/**
 * CSS class applied to disabled blocks in the canvas and List View.
 *
 * @type {string}
 */
const DISABLED_CLASS = 'is-block-enable-toggle-disabled';

/**
 * Whether a block's attributes mark it as enabled.
 *
 * @param {Object} attributes Block attributes.
 * @return {boolean} True when the block is enabled (the default).
 */
const isEnabled = ( attributes ) => attributes?.[ ATTRIBUTE ] !== false;

/**
 * Register the `betEnabled` attribute on all block types.
 *
 * Defaults to `true`, so it is only serialized into block markup when an
 * author disables a block. Keeping the value in the block delimiter comment
 * (not the saved HTML) means block validation is never affected.
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
 * Add the inspector toggle and the toolbar button.
 */
const withEnableControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { attributes, setAttributes } = props;
		const enabled = isEnabled( attributes );

		const setEnabled = ( value ) =>
			setAttributes( { [ ATTRIBUTE ]: value } );

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
							__nextHasNoMarginBottom
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
							onChange={ setEnabled }
						/>
					</PanelBody>
				</InspectorControls>
				<BlockControls group="other">
					<ToolbarGroup>
						<ToolbarButton
							icon={ enabled ? 'visibility' : 'hidden' }
							label={
								enabled
									? __(
											'Disable on the front end',
											'block-enable-toggle'
									  )
									: __(
											'Enable on the front end',
											'block-enable-toggle'
									  )
							}
							isPressed={ ! enabled }
							onClick={ () => setEnabled( ! enabled ) }
						/>
					</ToolbarGroup>
				</BlockControls>
			</Fragment>
		);
	};
}, 'withEnableControls' );

addFilter(
	'editor.BlockEdit',
	'block-enable-toggle/with-controls',
	withEnableControls
);

/**
 * Dim disabled blocks on the editor canvas and label them.
 */
const withDisabledCanvasClass = createHigherOrderComponent(
	( BlockListBlock ) => {
		return ( props ) => {
			if ( isEnabled( props.attributes ) ) {
				return <BlockListBlock { ...props } />;
			}

			const label = _x(
				'Disabled',
				'editor canvas indicator',
				'block-enable-toggle'
			);

			const className = [ props.className, DISABLED_CLASS ]
				.filter( Boolean )
				.join( ' ' );

			const wrapperProps = {
				...props.wrapperProps,
				'data-block-enable-toggle-label': label,
			};

			return (
				<BlockListBlock
					{ ...props }
					className={ className }
					wrapperProps={ wrapperProps }
				/>
			);
		};
	},
	'withDisabledCanvasClass'
);

addFilter(
	'editor.BlockListBlock',
	'block-enable-toggle/disabled-canvas-class',
	withDisabledCanvasClass
);

// ---------------------------------------------------------------------------
// List View indicator
//
// List View exposes no public API for per-row decoration, so the disabled
// state is mirrored onto its rows from the DOM. Everything below degrades
// safely: if a clientId can't be resolved the row is simply left undecorated.
// ---------------------------------------------------------------------------

let cachedBlocks = null;
let cachedDisabled = new Set();

/**
 * Collect the client IDs of every disabled block, recursing into inner blocks.
 *
 * Memoized on the block tree reference from getBlocks(), so the deep walk only
 * runs when the blocks actually change — not on every unrelated store update.
 *
 * @return {Set<string>} Disabled client IDs.
 */
function getDisabledClientIds() {
	const blocks = select( blockEditorStore ).getBlocks();

	if ( blocks === cachedBlocks ) {
		return cachedDisabled;
	}

	const disabled = new Set();

	const walk = ( list ) => {
		( list || [] ).forEach( ( block ) => {
			if ( block.attributes?.[ ATTRIBUTE ] === false ) {
				disabled.add( block.clientId );
			}
			if ( block.innerBlocks?.length ) {
				walk( block.innerBlocks );
			}
		} );
	};

	walk( blocks );

	cachedBlocks = blocks;
	cachedDisabled = disabled;

	return disabled;
}

/**
 * Resolve the block clientId a List View row represents.
 *
 * The List View leaf row exposes the clientId as a `data-block` attribute and
 * its select button links to `#block-{clientId}`; either is accepted so the
 * lookup survives markup differences across WordPress versions.
 *
 * @param {Element} row List View row element.
 * @return {string|null} The clientId, or null when it cannot be resolved.
 */
function getRowClientId( row ) {
	if ( row.dataset && row.dataset.block ) {
		return row.dataset.block;
	}

	const link = row.querySelector( 'a[href^="#block-"]' );
	if ( link ) {
		return link.getAttribute( 'href' ).slice( '#block-'.length );
	}

	return null;
}

/**
 * Mirror the disabled state onto List View rows.
 *
 * Exits cheaply when List View is closed (no rows in the DOM).
 */
function syncListView() {
	const rows = document.querySelectorAll( '.block-editor-list-view-leaf' );

	if ( ! rows.length ) {
		return;
	}

	const disabled = getDisabledClientIds();

	rows.forEach( ( row ) => {
		const clientId = getRowClientId( row );
		row.classList.toggle(
			DISABLED_CLASS,
			!! clientId && disabled.has( clientId )
		);
	} );
}

let scheduledFrame = null;

/**
 * Debounce List View syncing to one run per animation frame.
 */
function scheduleListViewSync() {
	if ( scheduledFrame ) {
		return;
	}

	scheduledFrame = window.requestAnimationFrame( () => {
		scheduledFrame = null;
		try {
			syncListView();
		} catch ( e ) {
			// Never let a DOM mismatch break the editor.
		}
	} );
}

// A single page-lifetime subscription. Block edits refresh the memoized
// disabled set; opening List View is itself a store change that re-syncs.
subscribe( scheduleListViewSync );
