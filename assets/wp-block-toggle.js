( function ( wp ) {
    const { addFilter } = wp.hooks;
    const { __ } = wp.i18n;
    const { createHigherOrderComponent } = wp.compose;
    const { InspectorControls } = wp.blockEditor || wp.editor;
    const { PanelBody, ToggleControl } = wp.components;
    const { subscribe, select } = wp.data;

    const ATTR_NAME = 'wpBlockEnabled';

    // Attribute for all blocks
    addFilter(
        'blocks.registerBlockType',
        'wp/enable-toggle/attribute',
        function ( settings ) {
            settings.attributes = settings.attributes || {};
            if ( typeof settings.attributes[ ATTR_NAME ] === 'undefined' ) {
                settings.attributes[ ATTR_NAME ] = { type: 'boolean', default: true };
            }
            return settings;
        }
    );

    // Inspector toggle
    const withEnableToggle = createHigherOrderComponent(
        ( BlockEdit ) => ( props ) => {
            const { attributes, setAttributes } = props;
            const enabled = attributes[ ATTR_NAME ];
            const original = wp.element.createElement( BlockEdit, props );
            const control = wp.element.createElement(
                InspectorControls,
                {},
                wp.element.createElement(
                    PanelBody,
                    { title: __('Visibility', 'wp-block-toggle'), initialOpen: false },
                    wp.element.createElement( ToggleControl, {
                        label: __('Enabled', 'wp-block-toggle'),
                        help: enabled !== false
                            ? __('Block will render on the front end.', 'wp-block-toggle')
                            : __('Block is disabled and will not render on the front end.', 'wp-block-toggle'),
                        checked: enabled !== false,
                        onChange: ( value ) => setAttributes( { [ ATTR_NAME ]: !!value } ),
                    } )
                )
            );
            return wp.element.createElement(wp.element.Fragment, null, original, control);
        },
        'withEnableToggle'
    );
    addFilter('editor.BlockEdit', 'wp/enable-toggle/control', withEnableToggle);

    // Editor canvas class for disabled blocks
    const withDisabledWrapperClass = createHigherOrderComponent(
        ( BlockListBlock ) => ( props ) => {
            const enabled = props?.attributes?.[ ATTR_NAME ];
            if ( enabled !== false ) {
                return wp.element.createElement( BlockListBlock, props );
            }

            const extraClass = 'wp-block-toggle-editor-disabled';
            const mergedProps = { ...props };

            if ( props.wrapperProps ) {
                const wrapperClass = props.wrapperProps.className || '';
                mergedProps.wrapperProps = {
                    ...props.wrapperProps,
                    className: wrapperClass
                        ? wrapperClass + ' ' + extraClass
                        : extraClass,
                };
            } else {
                const baseClass = props.className || '';
                mergedProps.className = baseClass
                    ? baseClass + ' ' + extraClass
                    : extraClass;
            }

            return wp.element.createElement( BlockListBlock, mergedProps );
        },
        'withDisabledWrapperClass'
    );
    addFilter('editor.BlockListBlock', 'wp/enable-toggle/disabled-wrapper-class', withDisabledWrapperClass);

    // -------- List View Badge Logic (more defensive) --------
    function getDisabledClientIds() {
        const blocks = select('core/block-editor').getBlocks();
        const disabled = new Set();
        (function walk(list){
            (list || []).forEach((b) => {
                if (b && b.attributes && b.attributes[ATTR_NAME] === false) disabled.add(b.clientId);
                if (b && b.innerBlocks && b.innerBlocks.length) walk(b.innerBlocks);
            });
        })(blocks);
        return disabled;
    }

    function findClientId(el) {
        // Try self, then descendants, then ancestor chain
        if (el.getAttribute) {
            const selfId = el.getAttribute('data-block') || (el.dataset && el.dataset.block);
            if (selfId) return selfId;
        }
        const desc = el.querySelector && el.querySelector('[data-block]');
        if (desc) return desc.getAttribute('data-block') || (desc.dataset && desc.dataset.block);
        const ancestor = el.closest && el.closest('[data-block]');
        if (ancestor) return ancestor.getAttribute('data-block') || (ancestor.dataset && ancestor.dataset.block);
        return null;
    }

    function titleTarget(el) {
        return el.querySelector('.block-editor-list-view-block__title, .block-editor-list-view__block-title, .components-truncated-content, .components-flex__item') || el;
    }

    function listViewItems() {
        // Cover sidebar list view, popover, and potential changes
        return document.querySelectorAll([
            '.block-editor-list-view__block',
            '.block-editor-list-view-block',
            '.block-editor-list-view__list [data-block]',
            '.block-editor-list-view__tree .components-tree-item',
            '[role="treeitem"][data-block]'
        ].join(', '));
    }

    function updateListViewBadges() {
        const disabledIds = getDisabledClientIds();
        const items = listViewItems();
        items.forEach((el) => {
            const clientId = findClientId(el);
            const isDisabled = clientId && disabledIds.has(clientId);
            el.classList.toggle('wp-block-toggle-disabled', !!isDisabled);

            let badge = el.querySelector('.wp-block-toggle-badge');
            if (isDisabled) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'wp-block-toggle-badge';
                    badge.setAttribute('role', 'img');
                    badge.setAttribute('aria-label', __('Disabled block', 'wp-block-toggle'));
                    badge.textContent = '🚫';
                    titleTarget(el).appendChild(badge);
                }
                const oldLabel = el.getAttribute('aria-label') || '';
                if (oldLabel && !/\(Disabled\)/i.test(oldLabel)) {
                    el.setAttribute('aria-label', oldLabel + ' ' + __('(Disabled)', 'wp-block-toggle'));
                }
            } else if (badge) {
                badge.remove();
                const oldLabel = el.getAttribute('aria-label') || '';
                el.setAttribute('aria-label', oldLabel.replace(/\s*\(Disabled\)$/i, ''));
            }
        });
    }

    // Schedule update via rAF
    let rafId = null;
    function scheduleUpdate() {
        if (rafId) return;
        rafId = requestAnimationFrame(() => {
            rafId = null;
            try { updateListViewBadges(); } catch (e) {}
        });
    }

    // React to block changes
    subscribe(scheduleUpdate);

    // Observe broader editor DOM (handles sidebar + popover)
    const observer = new MutationObserver(scheduleUpdate);
    function startObserver() {
        const roots = document.querySelectorAll('.interface-interface-skeleton, .edit-post-layout, body');
        roots.forEach(root => observer.observe(root, { childList: true, subtree: true }));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => { startObserver(); scheduleUpdate(); }, { once: true });
    } else {
        startObserver(); scheduleUpdate();
    }

    // Also run after a small timeout to catch lazy mounts
    setTimeout(scheduleUpdate, 500);
    setTimeout(scheduleUpdate, 1500);
} )( window.wp );
