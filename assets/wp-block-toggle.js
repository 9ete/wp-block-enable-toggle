( function ( wp ) {
    const { addFilter } = wp.hooks;
    const { __ } = wp.i18n;
    const { createHigherOrderComponent } = wp.compose;
    const { InspectorControls } = wp.blockEditor || wp.editor;
    const { PanelBody, ToggleControl } = wp.components;

    const ATTR_NAME = 'wpBlockEnableEnabled';

    // 1) Attribute for ALL blocks
    addFilter(
        'blocks.registerBlockType',
        'wpBlockEnable/enable-toggle/attribute',
        function ( settings ) {
            settings.attributes = settings.attributes || {};
            if ( typeof settings.attributes[ ATTR_NAME ] === 'undefined' ) {
                settings.attributes[ ATTR_NAME ] = {
                    type: 'boolean',
                    default: true,
                };
            }
            return settings;
        }
    );

    // 2) Inspector toggle for ALL blocks
    const withEnableToggle = createHigherOrderComponent(
        ( BlockEdit ) => {
            return ( props ) => {
                const { attributes, setAttributes } = props;
                const enabled = attributes[ ATTR_NAME ];

                const original = wp.element.createElement( BlockEdit, props );

                const control = wp.element.createElement(
                    InspectorControls,
                    {},
                    wp.element.createElement(
                        PanelBody,
                        { title: __('Visibility', 'wpBlockEnable'), initialOpen: false },
                        wp.element.createElement( ToggleControl, {
                            label: __('Enabled', 'wpBlockEnable'),
                            help: enabled
                                ? __('Block will render on the front end.', 'wpBlockEnable')
                                : __('Block is disabled and will not render on the front end.', 'wpBlockEnable'),
                            checked: enabled !== false,
                            onChange: ( value ) => setAttributes( { [ ATTR_NAME ]: !!value } ),
                        } )
                    )
                );

                return wp.element.createElement(
                    wp.element.Fragment,
                    null,
                    original,
                    control
                );
            };
        },
        'withEnableToggle'
    );
    addFilter('editor.BlockEdit','wpBlockEnable/enable-toggle/control', withEnableToggle);

    /**
     * 3) Add a "disabled" indicator in the List View (Outline) next to the block title.
     * We observe the List View DOM and tag items for blocks with wpBlockEnableEnabled === false.
     * This is safe and resilient; if selectors shift slightly, nothing crashes.
     */
    const { subscribe, select } = wp.data;

    function getDisabledClientIds() {
        const blocks = select('core/block-editor').getBlocks();
        const disabled = new Set();
        (function walk(list){
            list.forEach(b => {
                if (b?.attributes && b.attributes[ATTR_NAME] === false) {
                    disabled.add(b.clientId);
                }
                if (Array.isArray(b?.innerBlocks) && b.innerBlocks.length) {
                    walk(b.innerBlocks);
                }
            });
        })(blocks || []);
        return disabled;
    }

    function updateListViewBadges() {
        const disabledIds = getDisabledClientIds();

        // Known selectors for list view items across versions.
        const items = document.querySelectorAll('.block-editor-list-view__block, .block-editor-list-view-block');
        items.forEach((el) => {
            const clientId = el.getAttribute('data-block') || el.dataset?.block;
            const isDisabled = clientId && disabledIds.has(clientId);

            el.classList.toggle('wpBlockEnable-disabled', !!isDisabled);

            // Add/remove an inline icon element for visual hint
            let badge = el.querySelector('.wpBlockEnable-disabled-icon');
            if (isDisabled) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'wpBlockEnable-disabled-icon';
                    badge.setAttribute('role', 'img');
                    badge.setAttribute('aria-label', __('Disabled block', 'wpBlockEnable'));
                    // Text fallback icon. CSS will replace/augment with an SVG if desired.
                    badge.textContent = '🚫';
                    // Try to append near the title text
                    const titleTarget = el.querySelector('.block-editor-list-view-block__title, .components-truncated-content, .block-editor-list-view__block-title') || el;
                    titleTarget.appendChild(badge);
                }
                // Improve SR label by hinting disabled in the row
                const oldLabel = el.getAttribute('aria-label') || '';
                if (isDisabled && oldLabel && !/\(Disabled\)/i.test(oldLabel)) {
                    el.setAttribute('aria-label', oldLabel + ' ' + __('(Disabled)', 'wpBlockEnable'));
                }
            } else if (badge) {
                badge.remove();
                // Clean ARIA label if we previously added "(Disabled)"
                const oldLabel = el.getAttribute('aria-label') || '';
                el.setAttribute('aria-label', oldLabel.replace(/\s*\(Disabled\)$/i, ''));
            }
        });
    }

    // Throttle UI updates a bit
    let rafId = null;
    function scheduleUpdate() {
        if (rafId) return;
        rafId = requestAnimationFrame(() => {
            rafId = null;
            try { updateListViewBadges(); } catch (e) { /* no-op */ }
        });
    }

    // Recompute when blocks change or when List View DOM mutates
    subscribe(scheduleUpdate);

    // Observe mutations in the editor sidebar where List View is rendered
    const observer = new MutationObserver(scheduleUpdate);
    const startObserver = () => {
        const sidebar = document.querySelector('.edit-post-sidebar, .interface-interface-skeleton__secondary-sidebar');
        if (sidebar) {
            observer.observe(sidebar, { childList: true, subtree: true });
        }
    };
    // Kick off after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => { startObserver(); scheduleUpdate(); }, { once: true });
    } else {
        startObserver(); scheduleUpdate();
    }
} )( window.wp );
