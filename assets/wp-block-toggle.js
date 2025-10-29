( function ( wp ) {
    const { addFilter } = wp.hooks;
    const { __ } = wp.i18n;
    const { createHigherOrderComponent } = wp.compose;
    const { InspectorControls } = wp.blockEditor || wp.editor;
    const { PanelBody, ToggleControl } = wp.components;

    const ATTR_NAME = 'wpBlockEnabled';

    addFilter(
        'blocks.registerBlockType',
        'wp/enable-toggle/attribute',
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
                        { title: __('Visibility', 'wpBlockToggle'), initialOpen: false },
                        wp.element.createElement( ToggleControl, {
                            label: __('Enabled', 'wpBlockToggle'),
                            help: enabled
                                ? __('Block will render on the front end.', 'wpBlockToggle')
                                : __('Block is disabled and will not render on the front end.', 'wpBlockToggle'),
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

    addFilter(
        'editor.BlockEdit',
        'wp/enable-toggle/control',
        withEnableToggle
    );
} )( window.wp );
