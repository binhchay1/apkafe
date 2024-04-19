var blockProps;
var customizing_display = JSON.parse(lassoOptionsData.customizing_display);
var schema_display = JSON.parse(lassoOptionsData.schema_display);
var attributes = build_lasso_gutenberg_attributes();
var toogle_attributes = customizing_display['toogle_attributes'];
var textarea_attributes = customizing_display['textarea_attributes'];
var schema_toogle_attributes = schema_display['toogle_attributes'];
var default_attributes = ['show_short_code', 'short_code', 'button_text', 'button_update_text', 'button_edit_display'];
var default_lasso_shortcode_attributes = ['ref', 'id', 'link_id', 'type', 'category'];
var customize_attribute_codes = [];
var focus_customize_data = [];
var window_url_detail;
var shortcodes_reload = [];
var is_schema_structure_block_disable = false;
var client_id_lasso_was_enabled_schema = null;
var attr_toggle_enable_disable_list = ['data_nosnippet', 'disclosure'];
var lassoUrlDetailsSchemaData = typeof lassoUrlDetailsSchemaData !== 'undefined' ? lassoUrlDetailsSchemaData : {
    lasso_id_using_schema: null,
    lasso_urls_schema_data: [],
    post_author: 'admin',
    root_fields_id: {
        primary_rating: 1,
        pros: 2,
        cons: 3
    },
    post_id: lasso_helper.get_url_parameter('post')
};

jQuery(function() {
    function loadPopupContent() {
        jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'get',
            data: {
                action: 'lasso_get_display_html',
            }
        })
        .done(function(res) {
            res = res.data;
            html = res.html;
            jQuery('#wpcontent').append(html);
        });
    }

    /**
     * If some required datas is missing. We call request to get it.
     */
    function getMissingDatas() {
        // Get Lasso Url details schema datas if not existed.
        if ( typeof lassoUrlDetailsSchemaData.post_id != 'undefined' && parseInt( lassoUrlDetailsSchemaData.post_id ) ) {
            jQuery.ajax({
                url: lassoOptionsData.ajax_url,
                type: 'get',
                data: {
                    action: 'lasso_get_gutenberg_schema_data',
                    post_id: lassoUrlDetailsSchemaData.post_id
                }
            })
            .done(function(res) {
                if ( res && res.data && typeof res.data.lassoUrlDetailsSchemaData ) {
                    lassoUrlDetailsSchemaData = res.data.lassoUrlDetailsSchemaData;
                }
            });
        }
    }

    getMissingDatas();
    loadPopupContent();    
});

( function( $ ) {
    $(document).ready(function() {
        // EVERYTHING HERE IS A UNIQUE SCOPE
        function this_init(){
            // Start calling your functions from here:
            scan_lasso_shortcodes();
        }

        let blockLoaded = false;
        let blockLoadedInterval = setInterval(function() {
            if(document.getElementById('post-title-0')  // Working with version < 5.9
                || document.getElementsByClassName('editor-post-title').length) { // Working with version >= 5.9
                blockLoaded = true;
                this_init();
            }
            if(blockLoaded) {
                clearInterval(blockLoadedInterval);
            }
        }, 500);
        
        function scan_lasso_shortcodes(){
            var lasso_shortcode_blocks = jQuery('div[data-type="affiliate-plugin/lasso"]');
            if(lasso_shortcode_blocks.length > 0) {
                for (let index = 0; index < lasso_shortcode_blocks.length; index++) {
                    const element = jQuery(lasso_shortcode_blocks[index]);
                    var blockId = element.attr('id').replace(/^block-/gm,'');
                    var shortcode = element.find('input').val();
                    getLassoShortcodeHtml(blockId, shortcode);
                }
            }
        }

        // Lasso Segment Tracking
        jQuery( document )
            .on( 'click', '.editor-block-list-item-affiliate-plugin-lasso', lasso_cactus_icon_click)
            .on( 'hide.bs.modal', '#lasso-display-add', lasso_display_close_event);
    });
})(jQuery);

function getLassoShortcodeHtml(blockId, shortcode, is_new_display) {
    var loading_img = '<div class="py-5"><div class="ls-loader"></div></div>';
    return jQuery.ajax({
        url: lassoOptionsData.ajax_url,
        type: 'get',
        data: {
            action: 'lasso_get_shortcode_content',
            shortcode: shortcode,
        },
        beforeSend: function( xhr ) {
            jQuery('#block-' + blockId).find('div.shortcode-html').html(loading_img);
        }
    })
    .done(function(res) {
        res = res.data;
        html = res.html;
        jQuery('#block-' + blockId).find('div.shortcode-html').html(html);

        // Tracking if Display Added
        if ( is_new_display ) {
            lasso_segment_tracking('Display Added', {
                shortcode: shortcode
            });
        }
        wp.hooks.doAction('lasso_after_rendering_shortcode_in_gutenberg');
    })
    .always(function() {
        jQuery('#block-' + blockId).find('div.py-5').remove();
    });
}

function lasso_pop_up(props) {
    blockProps = props;
    var lasso_display = jQuery('#lasso-display-add');
    var lasso_display_type = jQuery('#lasso-display-type');
    lasso_display.modal('toggle');

    lasso_segment_tracking('Open "Choose a Display Type" Popup');

    // hide other tab, only show the types of shortcode (single, button, image, grid, list, gallery)
    if(lasso_display.hasClass('modal')) {
        lasso_display.removeClass('modal');
        lasso_display.addClass('show');
        lasso_display.find('.close-modal').remove();

        lasso_display.find('.modal-content').children().addClass('d-none');
        lasso_display_type.removeClass('d-none');
    }

    // hide the popup when clicking out of `lasso-display-add`
    jQuery(document).click(function(e) { 
        var el = jQuery(e.target);
        var id = el.attr('id');
        if(id == 'lasso-display-add') {
            lasso_display.modal('hide');
            lasso_display.removeClass('show');
            lasso_display.find('.close-modal').remove();
            jQuery('.jquery-modal.blocker.current').trigger('click');
        }
    });
}

function setBlockAttributes(block_id, shortcode) {
    blockProps.setAttributes({
        show_short_code:  true,
        short_code: shortcode,
        button_text: 'Select a New Display',
        button_update_text: 'Update Display',
        button_edit_display: 'Edit Display'
    });

    // if blockProps.setAttributes doesn't work, it will update the shortcode
    jQuery('#block-' + block_id).find('input').val(shortcode);

    // hide the popup
    var lasso_block = jQuery('#lasso-display-add');
    lasso_block.modal('hide');
    lasso_block.removeClass('show');
    jQuery('.jquery-modal.blocker.current').trigger('click');
}

function add_short_code_single_block(shortcode) {
    let block_id = blockProps.clientId;

    getLassoShortcodeHtml(block_id, shortcode, true).then( res => {
        handle_schema_add_single_shortcode( res );
    });

    setBlockAttributes(block_id, shortcode);
}

function add_short_code_button_block(shortcode) {
    let block_id = blockProps.clientId;

    getLassoShortcodeHtml(block_id, shortcode, true);
    setBlockAttributes(block_id, shortcode);
}


function add_short_code_image_block(shortcode) {
    let block_id = blockProps.clientId;

    getLassoShortcodeHtml(block_id, shortcode, true);
    setBlockAttributes(block_id, shortcode);
}


function add_short_code_grid_block(shortcode) {
    let block_id = blockProps.clientId;

    getLassoShortcodeHtml(block_id, shortcode, true);
    setBlockAttributes(block_id, shortcode);
}

function add_short_code_list_block(shortcode) {
    let block_id = blockProps.clientId;

    getLassoShortcodeHtml(block_id, shortcode, true);
    setBlockAttributes(block_id, shortcode);
}

function add_short_code_gallery_block(shortcode) {
    let block_id = blockProps.clientId;

    getLassoShortcodeHtml(block_id, shortcode, true);
    setBlockAttributes(block_id, shortcode);
}

function add_short_code_table_block(shortcode) {
    let block_id = blockProps.clientId;

    getLassoShortcodeHtml(block_id, shortcode, true);
    setBlockAttributes(block_id, shortcode);
}

function add_short_code_aitext_block(shortcode) {
    let block_id = blockProps.clientId;

    getLassoShortcodeHtml(block_id, shortcode, true);
    setBlockAttributes(block_id, shortcode);
}

function is_show_edit_display_btn(shortcode) {
    if ( ! shortcode ) {
        return false;
    }
    let current_attributes = get_lasso_shortcode_attributes( shortcode );
    let display_type       = current_attributes['type'] ? current_attributes['type'] : 'single';

    return ['single', 'button', 'image', 'table'].includes(display_type) ? true : false;
}

function handle_schema_add_single_shortcode( res_schema_data ) {
    if ( ! res_schema_data || ! 'data' in res_schema_data || ! res_schema_data.data ) {
        return;
    }

    let res                         = res_schema_data.data;
    let lasso_id                    = res.schema.lasso_id;
    let schema_price                = res.schema.price;
    let schema_price_currency       = res.schema.currency;
    let schema_pros                 = res.schema.pros;
    let schema_cons                 = res.schema.cons;
    let schema_rating               = res.schema.primary_rating ? res.schema.primary_rating : '';
    let is_display_pros_cons_toggle = res.schema.is_display_pros_cons_toggle;

    lassoUrlDetailsSchemaData.lasso_urls_schema_data[ res.schema.lasso_id ] = {
        lasso_id: lasso_id,
        price: schema_price,
        currency: schema_price_currency,
        pros: schema_pros,
        cons: schema_cons,
        is_display_pros_cons_toggle: is_display_pros_cons_toggle,
        primary_rating: schema_rating,
    };

    blockProps.setAttributes({
        schema_price: schema_price,
        schema_price_currency: schema_price_currency,
        schema_pros: schema_pros,
        schema_cons: schema_cons,
        schema_rating: schema_rating,
    });
}

function open_url_detail_window(props) {
    blockProps = props;
    if( typeof window_url_detail === 'object' ) {
        window_url_detail.close();  // close windows are opening
    }

    let shortcode          = blockProps.attributes.short_code;
    let current_attributes = get_lasso_shortcode_attributes(shortcode);
    let display_type       = current_attributes['type'] ? current_attributes['type'] : 'single';
    let detail_page        = '';
    let post_id            = 0;
    let prompt             = current_attributes['prompt'] ? current_attributes['prompt'] : '';

    if ( current_attributes.hasOwnProperty('id') ) {
        post_id = current_attributes.id;
        if ( 'table' === display_type ) {
            detail_page = lassoOptionsData.site_url + "/wp-admin/edit.php?post_type=lasso-urls&page=table-details&id=" + post_id;
        } else {
            detail_page = lassoOptionsData.site_url + "/wp-admin/edit.php?post_type=lasso-urls&page=url-details&post_id=" + post_id;
        }
    }

    if ( post_id !== 0 && ! isNaN(post_id) ) {
        shortcodes_reload.push({blockId: blockProps.clientId, shortcode: blockProps.attributes.short_code});
        window_url_detail = window.open(detail_page,'_blank');
        window_url_detail.onload = function(){
            this.onbeforeunload = function(){
                for ( let i = 0; i < shortcodes_reload.length; i++ ) {
                    getLassoShortcodeHtml(shortcodes_reload[i].blockId, shortcodes_reload[i].shortcode);
                }
                shortcodes_reload = [];
            }
        }
    } else if ( prompt !== '' ) {
        jQuery('#lasso-aitext #prompt').val(prompt);
        lasso_pop_up(blockProps);
        jQuery('#lasso-ai').trigger('click');
        get_response_from_prompt(true);

    }
}

function LassoIcon(props) {
    let width = props.width ? props.width : '100';
    let height = props.height ? props.height : '100';
    return React.createElement("svg", {xmlns: "http://www.w3.org/2000/svg",width: width, height: height,viewBox: "0 0 500 500"},
        React.createElement("defs", null, React.createElement("clipPath", {id: "b"},
        React.createElement("rect", {width: "500",height: "500"}))),
        React.createElement("circle", {cx: "249.5", cy: "249.5", r: "249.5", transform: "translate(1 1)", fill: "#5e36ca"}),
        React.createElement("g", {id: "a","clipPath": "url(#b)"},
        React.createElement("g", {transform: "translate(59.684 92.664)"},
        React.createElement("g", {transform: "translate(90.918 0.437)"},
        React.createElement("path", { d: "M177.568,52.494h0a25.365,25.365,0,0,0-25.84,25.613l.443,9.957c-.371,62.1-18.019,59.155-20.892,58.341V30.649C131.284,16.543,119.335,5,104.734,5h0C90.128,5,78.179,16.543,78.179,30.649V147.743C53.909,154.035,58.167,82.39,58.167,82.39V57.457c0-14.374-13.9-25.989-28.805-25.989h0c-14.874,0-24.29,11.759-24.29,26.133L5,82.673C12.208,193.8,78.179,183.648,78.179,183.648l.036,37.434H131.32l-.036-37.542C200.1,183.267,204.391,88.3,204.391,88.3v-9.89C204.385,64.155,192.318,52.494,177.568,52.494Z", transform: "translate(-5 -5)", fill: "#00ffd3" }),
        React.createElement("path", { d: "M4.762,37.732c0,10.173,6.178,18.5,13.736,18.5h44.43c7.558,0,13.741-8.325,13.741-18.5L81.416,0H0Z",transform: "translate(59.721 257.209)", fill: "#cc4afc" })),
        React.createElement("path", { d: "M195.564,425.8H103.779c-4.193.017-7.588,4.181-7.6,9.321v14.692c.011,5.14,3.406,9.3,7.6,9.321h91.785c4.2-.014,7.6-4.178,7.609-9.321V435.121C203.159,429.978,199.76,425.814,195.564,425.8Z",transform: "translate(41.681 -205.257)",fill: "#cc4afc"}))));
}


var lassoBlock = wp.blocks.registerBlockType('affiliate-plugin/lasso', {
    title: 'Lasso',
    icon: React.createElement(LassoIcon, null),
    category: 'common',
    keywords: [
        "link",
        "affiliate",
        "lasso"
    ],
    attributes,
    edit: function(props) {
        function onChangeContent( e ) {
            props.setAttributes( { short_code: e.target.value } );
            update_customize_data( e.target.value );
        }

        // ? Event blur handle reload preview display
        function onBlurContent( e ) {
            this.getLassoShortcodeHtml( props.clientId, props.attributes.short_code );
        }

        // ? Event keypress Enter handle reload preview display
        function onKeyPressContent( e ) {
            if ( e.key == 'Enter' ) {
                this.getLassoShortcodeHtml( props.clientId, props.attributes.short_code );
            }
        }

        function update_customize_data( shortcode ) {
            try {
                if ( shortcode && shortcode.match(/\[lasso.*\]/) ) {
                    var current_attributes               = get_lasso_shortcode_attributes(shortcode);
                    var current_attribute_codes          = Object.keys(current_attributes);
                    var customize_attribute_codes        = get_customize_attribute_codes();
                    var customize_attribute_code_missing = customize_attribute_codes.filter(x => !current_attribute_codes.includes(x));

                    // Update customize data if existing in shortcode
                    for (const property in current_attributes) {
                        if ((default_attributes.indexOf(property) === -1) && (typeof props.attributes[property] != 'undefined')) {
                            props.setAttributes( { [property]: current_attributes[property] } );
                            jQuery('input.cuz-attr-' + property).val(current_attributes[property]);
                        }
                    }

                    // Delete customize data if don't exist in shortcode
                    for (const index in customize_attribute_code_missing) {
                        props.setAttributes( { [customize_attribute_code_missing[index]]: '' } );
                        jQuery('input.cuz-attr-' + customize_attribute_code_missing[index]).val('');
                    }
                }
            } catch (e) {
                console.log('ERROR: ', e);
            }
        }

        function get_customize_attribute_codes() {
            if (customize_attribute_codes.length) {
                return customize_attribute_codes;
            }

            for (const attr_code in props.attributes) {
                if (default_attributes.indexOf(attr_code) == -1) {
                    customize_attribute_codes.push(attr_code);
                }
            }

            return customize_attribute_codes;
        }

        function on_change_customize_data( e ) {
            try {
                var value      = e.target.value;
                var value_attr = e.target.value;
                var attr_code  = e.target.className.replace('cuz-attr-', '');

                if ( textarea_attributes.includes( attr_code ) ) {
                    // Replace line breaks with <br> tags
                    value = value.replace(/\r?\n/g, 'lasso-br-code');
                } else {
                    value = value_attr = value.replace(/\"/g, ''); // Don't allow double quote value
                }

                // ID format: Replace whitespace by "-"
                if ( 'anchor_id' == attr_code ) {
                    value = value.replace(/\s/g, '-').replace(/(\-)+/g, '-');
                }
                e.target.value = value_attr;
                props.setAttributes( { [attr_code]: value_attr } ); // Update new value for editor attribute

                // Build new shortcode content
                var new_short_code = customize_shortcode(attr_code, value);
                props.setAttributes( { short_code: new_short_code } );
                jQuery('#block-' + props.clientId).find('input.shortcode-input').val(new_short_code);
            } catch (e) {
                console.log('Error: On change customize data', e);
            }
        }

        /**
         * Return suitable toogle function name for each Lasso attribute.
         *
         * @param attr_code Lasso shortcode attribute code.
         * @returns toogle function name
         */
        function get_toogle_function( attr_code ) {
            let toogle_onchange_function = on_change_toggle_price;

            switch(attr_code) {
                case 'field':
                    toogle_onchange_function = on_change_toggle_field;
                    break;
                case 'rating':
                    toogle_onchange_function = on_change_toggle_rating;
                    break;
                case 'schema_review':
                    toogle_onchange_function = on_change_toggle_schema_review;
                    break;
                case 'schema_pros_cons':
                    toogle_onchange_function = on_change_toggle_schema_pros_cons;
                    break;
                case 'data_nosnippet':
                    toogle_onchange_function = on_change_toggle_data_nosnippet;
                    break;
                case 'disclosure':
                    toogle_onchange_function = on_change_toggle_disclousure;
                    break;
            }

            return toogle_onchange_function;
        }

        function on_change_toggle_price( value ) {
            on_change_toggle_data( 'price', value );
        }

        function on_change_toggle_field( value ) {
            on_change_toggle_data( 'field', value );
        }

        function on_change_toggle_rating( value ) {
            on_change_toggle_data( 'rating', value );
        }

        function on_change_toggle_data_nosnippet( value ) {
            on_change_toggle_data( 'data_nosnippet', value );
        }

        function on_change_toggle_disclousure( value ) {
            on_change_toggle_data( 'disclosure', value );
        }

        function on_change_toggle_schema_review( value ) {
            on_change_toggle_data( 'schema_review', value, false );

            if ( value ) {
                is_schema_structure_block_disable  = true;
                client_id_lasso_was_enabled_schema = props.clientId;

                let shortcode          = props.attributes.short_code;
                let current_attributes = get_lasso_shortcode_attributes( shortcode );
                let current_lasso_id   = current_attributes['id'];
                let primary_rating     = lassoUrlDetailsSchemaData.lasso_urls_schema_data[current_lasso_id].primary_rating

                if ( ! primary_rating ) {
                    let field_id   = lassoUrlDetailsSchemaData.root_fields_id.primary_rating;
                    let field_name = 'primary_rating';

                    add_custom_field( props.clientId, field_id, current_lasso_id ).then( res => {
                        handle_add_field_response( res.data, field_name, current_lasso_id, true, '3.5' );
                    }).always(function() {
                        jQuery('#block-' + props.clientId).find('div.py-5').remove();
                    });
                }
            } else {
                if ( ! props.attributes.schema_pros_cons ) {
                    is_schema_structure_block_disable  = false;
                    client_id_lasso_was_enabled_schema = null;
                    lassoUrlDetailsSchemaData.lasso_id_using_schema = null;
                }
            }
        }

        function on_change_toggle_schema_pros_cons( value ) {
            on_change_toggle_data( 'schema_pros_cons', value, false );

            if ( value ) {
                is_schema_structure_block_disable  = true;
                client_id_lasso_was_enabled_schema = props.clientId;
            } else {
                if ( ! props.attributes.schema_review ) {
                    is_schema_structure_block_disable = false;
                    client_id_lasso_was_enabled_schema = null;
                    lassoUrlDetailsSchemaData.lasso_id_using_schema = null;
                }
            }
        }

        function add_custom_field( blockId, field_id, lasso_id ) {
            let loading_img = '<div class="py-5"><div class="ls-loader"></div></div>';

            return jQuery.ajax({
                url: lassoOptionsData.ajax_url,
                type: 'post',
                context: this,
                data: {
                    action: 'lasso_add_field_to_page',
                    field_id: field_id,
                    post_id: lasso_id
                }
                ,beforeSend: function (xhr) {
                    jQuery('#block-' + blockId).find('div.shortcode-html').html(loading_img);
                }
            });
        };

        function handle_add_field_response( res, field_name, lasso_id, is_reload_shortcode_html = true, default_value = ' ' ) {
            if (res.status) {
                lassoUrlDetailsSchemaData.lasso_urls_schema_data[lasso_id][field_name] = default_value;
                switch( field_name ) {
                    case 'primary_rating':
                        props.setAttributes({ schema_rating: default_value });
                        break;
                    case 'pros':
                        props.setAttributes({ schema_pros: default_value });
                        break;
                    case 'cons':
                        props.setAttributes({ schema_cons: default_value });
                        break;
                }

                if ( is_reload_shortcode_html ) {
                    let blockId   = props.clientId;
                    let shortcode = props.attributes.short_code;
                    this.getLassoShortcodeHtml(blockId, shortcode);
                }
            }
        }

        function on_change_toggle_data( attr_code, value, is_reload_shortcode_html = true ) {
            try {
                // Build new shortcode content
                let new_short_code = customize_shortcode(attr_code, value);
                props.attributes[attr_code] = value;
                props.setAttributes( { short_code: new_short_code } );
                jQuery('#block-' + props.clientId).find('input.shortcode-input').val(new_short_code);
                if ( is_reload_shortcode_html ) {
                    this.getLassoShortcodeHtml(props.clientId, new_short_code);
                }
            } catch (e) {
                console.log('Error: On change customize data', e);
            }
        }

        function focus_customize( e ) {
            var value     = e.target.value;
            var attr_code = e.target.className.replace('cuz-attr-', '');

            if (typeof focus_customize_data[props.clientId] == 'undefined') {
                focus_customize_data[props.clientId] = [];
            }

            focus_customize_data[props.clientId][attr_code] = value;
        }

        function update_custimized_display( e ) {
            var value     = e.target.value;
            var attr_code = e.target.className.replace('cuz-attr-', '');
            var blockId   = props.clientId;

            if (value != focus_customize_data[blockId][attr_code]) {
                var blockId = props.clientId;
                var shortcode = props.attributes.short_code;
                this.getLassoShortcodeHtml(blockId, shortcode);
            }
        }

        function handle_customize_key_press( event ) {
            if (event.key == 'Enter') {
                focus_customize( event );
                this.getLassoShortcodeHtml(props.clientId, props.attributes.short_code);
            }
        }

        function render_customize_content() {
            var customize_content = [
                React.createElement(
                    "div",
                    {
                        dangerouslySetInnerHTML: {
                            __html: customizing_display['notice']
                        },
                        className: 'cuz-notice',
                    },
                )
            ];
            var shortcode = props.attributes.short_code;
            if (props.attributes.show_short_code && shortcode) {
                var current_attributes = get_lasso_shortcode_attributes( shortcode );
                var display_type       = current_attributes['type'] ? current_attributes['type'] : 'single';

                if (display_type in customizing_display) {
                    var customizing_display_item = customizing_display[display_type];
                    var available_attributes = customizing_display_item['attributes'];

                    for (const property in available_attributes) {
                        var attr_name = available_attributes[property]['name'];
                        var attr_code = available_attributes[property]['attr'];
                        var attr_desc = available_attributes[property]['desc'];
                        let input_type_el;

                        // Toogle input
                        if (toogle_attributes.includes(attr_code)) {
                            let checked = true;

                            if (shortcode && shortcode.match(/\[lasso.*\]/)) {
                                if ( attr_toggle_enable_disable_list.includes(attr_code) ) {
                                    checked = current_attributes[attr_code] === 'enable';
                                } else {
                                    checked = current_attributes[attr_code] !== 'hide';
                                }
                            }

                            input_type_el = React.createElement(
                                wp.components.ToggleControl,
                                {
                                    onChange: get_toogle_function( attr_code ),
                                    checked: checked,
                                    className: 'cuz-attr-' + attr_code,
                                });
                        } else if (textarea_attributes.includes(attr_code)) {
                            input_type_el = React.createElement(
                                "textarea",
                                {
                                    type: "text",
                                    // value: props.attributes.short_code, // input can't be changed
                                    defaultValue: props.attributes[attr_code], // input can be changed
                                    onChange: on_change_customize_data,
                                    onFocus: focus_customize,
                                    onBlur: update_custimized_display,
                                    onKeyPress: handle_customize_key_press,
                                    style:{
                                        display: props.attributes.show_short_code ? 'block' : 'none',
                                        width: '100%',

                                    },
                                    className: 'cuz-attr-' + attr_code,
                                }
                            );
                        } else { // Text box input
                            input_type_el = React.createElement(
                                "input",
                                {
                                    type: "text",
                                    // value: props.attributes.short_code, // input can't be changed
                                    defaultValue: props.attributes[attr_code], // input can be changed
                                    onChange: on_change_customize_data,
                                    onFocus: focus_customize,
                                    onBlur: update_custimized_display,
                                    onKeyPress: handle_customize_key_press,
                                    style:{
                                        display: props.attributes.show_short_code ? 'block' : 'none',
                                        width: '100%',

                                    },
                                    className: 'cuz-attr-' + attr_code,
                                }
                            );
                        }

                        var el = React.createElement(
                            "div",
                            {
                                style:{
                                    display: props.attributes.show_short_code ? 'block' : 'none',
                                },
                                className: 'cuz-item',
                            },
                            [
                                React.createElement(
                                    "div",
                                    {
                                        className: 'cuz-name',
                                    },
                                    attr_name
                                ),
                                input_type_el,
                                wp.element.createElement( 'div', {
                                    dangerouslySetInnerHTML: {
                                        __html: attr_desc
                                    },
                                    className: 'cuz-desc',
                                })
                            ]
                        );

                        customize_content.push(el);
                    }
                }
            }

            return customize_content;
        }

        function render_schema_content() {
            let customize_content = [
                React.createElement(
                    "div",
                    {
                        dangerouslySetInnerHTML: {
                            __html: schema_display['notice']
                        },
                        className: 'cuz-notice',
                    },
                )
            ];
            let shortcode = props.attributes.short_code;
            if (props.attributes.show_short_code && shortcode) {
                let current_attributes = get_lasso_shortcode_attributes( shortcode );
                let display_type       = current_attributes['type'] ? current_attributes['type'] : 'single';
                let current_lasso_id   = current_attributes['id'];

                if (display_type in schema_display) {
                    let schema_display_item  = schema_display[display_type];
                    let available_attributes = schema_display_item['attributes'];

                    for (const property in available_attributes) {
                        let attr_name = available_attributes[property]['name'];
                        let attr_code = available_attributes[property]['attr'];
                        let attr_desc = available_attributes[property]['desc'];
                        let input_type_el;
                        let input_type_el_style = {};

                        // Toogle input
                        if (schema_toogle_attributes.includes(attr_code)) {
                            let checked = true;

                            if (shortcode && shortcode.match(/\[lasso.*\]/)) {
                                checked = current_attributes[attr_code] === 'enable';
                            }

                            props.attributes[attr_code] = checked;

                            if ( 'schema_pros_cons' === attr_code ) {
                                try {
                                    let is_display_pros_cons_toggle = lassoUrlDetailsSchemaData.lasso_urls_schema_data[current_lasso_id].is_display_pros_cons_toggle;
                                    input_type_el_style.display     = is_display_pros_cons_toggle ? 'block' : 'none';
                                } catch (e){
                                }
                            }

                            input_type_el = React.createElement(
                                wp.components.ToggleControl,
                                {
                                    onChange: get_toogle_function( attr_code ),
                                    checked: checked,
                                    className: 'cuz-attr-' + attr_code,
                                });
                        } else { // Text box input
                            let class_name = '';
                            style = {
                                display: props.attributes.show_short_code ? 'block' : 'none',
                                width: '100%',
                            }

                            switch( attr_code ) {
                                case 'schema_price_currency':
                                    try {
                                        props.attributes[attr_code] = lassoUrlDetailsSchemaData.lasso_urls_schema_data[current_lasso_id].currency;
                                    } catch (e) {
                                    }
                                    break;
                                case 'schema_price':
                                    try {
                                        let price                   = lassoUrlDetailsSchemaData.lasso_urls_schema_data[current_lasso_id].price;
                                        props.attributes[attr_code] = parseFloat(price).toFixed(2);
                                    } catch (e) {
                                    }

                                    break;
                                case 'schema_rating':
                                    try {
                                        let primary_rating          = lassoUrlDetailsSchemaData.lasso_urls_schema_data[current_lasso_id].primary_rating
                                        props.attributes[attr_code] = primary_rating ? primary_rating : '';
                                    } catch (e) {
                                    }

                                    class_name = ' customize-wrapper-disable';
                                    break;
                                case 'schema_review_author':
                                    props.attributes[attr_code] = lassoUrlDetailsSchemaData.post_author;
                                    class_name = ' customize-wrapper-disable';
                                    break;
                                case 'schema_pros':
                                    try {
                                        let current_schema          = lassoUrlDetailsSchemaData.lasso_urls_schema_data[current_lasso_id];
                                        let pros_value              = current_schema.pros;
                                        props.attributes[attr_code] = pros_value ? pros_value : '';

                                        let is_display_pros_cons_toggle = current_schema.is_display_pros_cons_toggle;
                                        input_type_el_style.display     = is_display_pros_cons_toggle ? 'block' : 'none';
                                    } catch (e){
                                    }

                                    class_name = ' customize-wrapper-disable';
                                    break;
                                case 'schema_cons':
                                    try {
                                        let current_schema          = lassoUrlDetailsSchemaData.lasso_urls_schema_data[current_lasso_id];
                                        let cons_value              = current_schema.cons;
                                        props.attributes[attr_code] = cons_value ? cons_value : '';

                                        let is_display_pros_cons_toggle = current_schema.is_display_pros_cons_toggle;
                                        input_type_el_style.display     = is_display_pros_cons_toggle ? 'block' : 'none';
                                    } catch (e){
                                    }

                                    class_name = ' customize-wrapper-disable';
                                    break;
                            }

                            input_type_el = React.createElement(
                                "input",
                                {
                                    type: "text",
                                    // value: props.attributes.short_code, // input can't be changed
                                    defaultValue: props.attributes[attr_code], // input can be changed
                                    onChange: on_change_customize_data,
                                    onFocus: focus_customize,
                                    onBlur: update_custimized_display,
                                    onKeyPress: handle_customize_key_press,
                                    style: style,
                                    className: 'cuz-attr-' + attr_code + class_name,
                                }
                            );
                        }

                        var el = React.createElement(
                            "div",
                            {
                                style: input_type_el_style,
                                className: 'cuz-item',
                            },
                            [
                                React.createElement(
                                    "div",
                                    {
                                        className: 'cuz-name',
                                    },
                                    attr_name
                                ),
                                input_type_el,
                                wp.element.createElement( 'div', {
                                    dangerouslySetInnerHTML: {
                                        __html: attr_desc
                                    },
                                    className: 'cuz-desc',
                                })
                            ]
                        );

                        customize_content.push(el);
                    }
                }
            }

            return customize_content;
        }

        function get_schema_structured_css_class() {
            let shortcode                       = props.attributes.short_code;
            let current_attributes              = get_lasso_shortcode_attributes( shortcode );
            let current_shortcode_id            = current_attributes['id'];
            let is_configurable_structure_block = false;

            if ( is_schema_structure_block_disable && client_id_lasso_was_enabled_schema !== props.clientId) {
                return 'customize-wrapper customize-wrapper-disable';
            }

            if ( parseInt(current_shortcode_id) === parseInt(lassoUrlDetailsSchemaData.lasso_id_using_schema) ) {
                is_configurable_structure_block    = true;
                is_schema_structure_block_disable  = true;
                client_id_lasso_was_enabled_schema = props.clientId;
            }

            // Case all Lasso shortcode in post still not enable Schema Review or Schema Pros/Cons yet
            if (lassoUrlDetailsSchemaData.lasso_id_using_schema === null) {
                is_configurable_structure_block = true;
            }

            return is_configurable_structure_block ? 'customize-wrapper' : 'customize-wrapper customize-wrapper-disable';
        }

        function customize_shortcode( cus_attr_name, cus_attr_value ) {
            var shortcode = props.attributes.short_code;
            if (shortcode && shortcode.match(/\[lasso.*\]/)) {
                var current_attributes = get_lasso_shortcode_attributes( shortcode );

                if (Object.keys( current_attributes ).length !== 0) {
                    shortcode = get_new_customize_shortcode( current_attributes, cus_attr_name, cus_attr_value );
                }
            }

            return shortcode;
        }

        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement(
                wp.blockEditor.InspectorControls,
                null,
                wp.element.createElement(
                    wp.components.PanelBody,
                    {
                        title: 'Customize Display',
                        initialOpen: true,
                        className: 'customize-wrapper'
                    },
                    render_customize_content()
                ),
                wp.element.createElement(
                    wp.components.PanelBody,
                    {
                        title: 'Schema & Structured Data',
                        initialOpen: false,
                        className: get_schema_structured_css_class()
                    },
                    render_schema_content()
                )
            ),
            React.createElement(
                "div",
                {
                    style: {
                        textAlign: 'center',
                        backgroundColor: "#5E36CA",
                        borderRadius: "10px",
                        padding: "0px 0px 20px 0px",
                        fontFamily: '"Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif',
                    }
                },
                React.createElement(
                    "div",
                    {
                        style:{
                            display: props.attributes.show_short_code ? 'block' : 'none',
                            margin: '0 auto',
                            background: 'white',
                            padding: '1px 0',
                            'text-align': 'initial',
                        },
                        class: 'shortcode-html'
                    },
                    ''
                ),
                React.createElement(
                    "div",
                    {
                        style: {
                            display: 'flex',
                            alignItems: 'center',
                            padding: '10px 0 0 0',
                            justifyContent: 'center',
                        }
                    },
                    React.createElement(LassoIcon, {width: 50, height: 50}),
                    React.createElement(
                        "span",
                        {
                            style: {
                                fontSize: '26px',
                                fontWeight: 700,
                            }
                        }
                    )
                ),
                React.createElement(
                    "span",
                    {
                        style:{
                            display: props.attributes.show_short_code ? 'none' : 'block',
                            marginBottom: '20px',
                            marginTop: '10px',
                            fontSize: '18px',
                            color: '#ffffff',
                            fontFamily: '"Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif',
                        }
                    },
                    "Choose a Lasso Link to display."
                ),
                React.createElement(
                    "input",
                    {
                        type: "text",
                        // value: props.attributes.short_code, // input can't be changed
                        defaultValue: props.attributes.short_code, // input can be changed
                        onChange: onChangeContent,
                        onBlur: onBlurContent,
                        onKeyPress: onKeyPressContent,
                        style:{
                            display: props.attributes.show_short_code ? 'block' : 'none',
                            margin: '10px auto 20px auto',
                            padding: '0.5rem 0.75rem',
                            borderRadius: '0.5rem',
                            border: '1px solid #ced4da',
                            width: '85%',
                            height: 'auto',
                            lineHeight: '2',
                            fontSize: '1rem',
                            fontFamily: '"Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif',
                        },
                        class: 'shortcode-input'
                    }
                ),
                React.createElement(
                    "button",
                    {
                        style: {
                            display: props.attributes.short_code !== '' ? 'inline-block' : 'none',
                            backgroundColor: "#22BAA0",
                            color: '#ffffff',
                            padding: "0.75rem 2rem",
                            borderRadius: '100rem',
                            fontSize: '1rem',
                            margin: '0.5rem',
                            fontWeight: 800,
                            fontFamily: '"Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif',
                            border: 0,
                            cursor: 'pointer'
                        },
                        onClick: function(e) {
                            var blockId = props.clientId;
                            var shortcode = props.attributes.short_code;
                            this.getLassoShortcodeHtml(blockId, shortcode);
                        }.bind(this)
                    },
                    props.attributes.button_update_text
                ),
                React.createElement(
                    "button",
                    {
                        style: {
                            backgroundColor: "#22BAA0",
                            color: '#ffffff',
                            padding: "0.75rem 2rem",
                            borderRadius: '100rem',
                            fontSize: '1rem',
                            margin: '0.5rem',
                            fontWeight: 800,
                            fontFamily: '"Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif',
                            border: 0,
                            cursor: 'pointer'
                        },
                        onClick: function() {
                            this.lasso_pop_up(props)
                        }.bind(this)
                    },
                    props.attributes.button_text
                ),
                React.createElement(
                    "button",
                    {
                        style: {
                            display: this.is_show_edit_display_btn(props.attributes.short_code) ? 'inline-block' : 'none',
                            backgroundColor: "#22BAA0",
                            color: '#ffffff',
                            padding: "0.75rem 2rem",
                            borderRadius: '100rem',
                            fontSize: '1rem',
                            margin: '0.5rem',
                            fontWeight: 800,
                            fontFamily: '"Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif',
                            border: 0,
                            cursor: 'pointer'
                        },
                        onClick: function() {
                            this.open_url_detail_window(props)
                        }.bind(this)
                    },
                    props.attributes.button_edit_display
                ),
            )
        );
    },
    save: function(props) {
        return wp.element.createElement(
            "div",
            null,
            props.attributes.short_code
        );
    }
});

// it works for core blocks
wp.hooks.addFilter(
    "blocks.registerBlockType",
    "affiliate-plugin/lasso/attribute/data",
    (settings, name) => {
        if (name.includes('genesis-custom-blocks/') || name.includes('kadence/')) {
            return settings;
        }

        settings.attributes = Object.assign( settings.attributes, {
            'data-lasso-id': {
                attribute: "data-lasso-id",
                selector: "a",
                source: "attribute",
                type: "string",
            },
            'data-lasso-name': {
                attribute: "data-lasso-name",
                selector: "a",
                source: "attribute",
                type: "string",
            },
            'rel': {
                attribute: "rel",
                selector: "a",
                source: "attribute",
                type: "string",
            },
            'target': {
                attribute: "target",
                selector: "a",
                source: "attribute",
                type: "string",
            },
            'all-links': {
                selector: "a",
                source: "query",
                type: "array",
                query: {
                    data_lasso_id: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'data-lasso-id',
                    },
                    data_lasso_name: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'data-lasso-name',
                    },
                    href: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'href',
                    },
                    target: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'target',
                    },
                    rel: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'rel',
                    },
                }
            },
            'data-lasso-id-img': {
                attribute: "data-lasso-id",
                selector: "img",
                source: "attribute",
                type: "string",
            },
        });

        return settings;
    }
);

// parse attributes from shortcode/html
wp.hooks.addFilter(
    'blocks.getSaveContent.extraProps',
    'affiliate-plugin/lasso',
    (extraProps, blockType, attributes) => {
        try {
            if(blockType.name.includes('kadence/')
                || blockType.name.includes('tph/')
                || blockType.name.includes('editor-blocks/')
            ) {
                return extraProps;
            }

            let lasso_id_img = attributes["data-lasso-id-img"] || '';
            let lasso_id = attributes["data-lasso-id"] || '';
            let lasso_name = attributes["data-lasso-name"] || '';
            let rel = attributes["rel"] || '';
            let target = attributes["target"] || '';

            let figure_props = 'children' in extraProps && extraProps.children != '' && extraProps.children !== undefined ? extraProps.children.props : false;
            let first_child = figure_props && 'children' in figure_props ? figure_props.children[0] : false;

            if(figure_props && first_child && first_child.type == 'a') {
                if(lasso_id != '') first_child.props['data-dev'] = lasso_id; // fix conflict with other js
                if(lasso_id != '') first_child.props['data-lasso-id'] = lasso_id;
                if(lasso_name != '') first_child.props['data-lasso-name'] = lasso_name;
                if(rel != '') first_child.props['rel'] = rel;
                if(target != '') first_child.props['target'] = target;
            }
            if(!first_child && figure_props && ( figure_props.type == 'a' || figure_props.tagName == 'a' ) ) {
                if(lasso_id != '') figure_props['data-lasso-id'] = lasso_id;
                if(lasso_name != '') figure_props['data-lasso-name'] = lasso_name;
                if(rel != '') figure_props['rel'] = rel;
                if(target != '') figure_props['target'] = target;
            }
            if(blockType.name == 'core/image' && extraProps !== undefined) {
                let first_child = extraProps && 'children' in extraProps ? extraProps.children[0] : false;
                if(first_child && first_child.type == 'a') {
                    if(lasso_id != '') first_child.props['data-dev'] = lasso_id; // fix conflict with other js
                    if(lasso_id != '') first_child.props['data-lasso-id'] = lasso_id;
                    if(lasso_name != '') first_child.props['data-lasso-name'] = lasso_name;

                    let img_child = first_child.props.children;
                    if(img_child && img_child.type == 'img') {
                        if(lasso_id_img != '') img_child.props['data-lasso-id'] = lasso_id_img;
                    }
                } else if(first_child && first_child.type == 'img') {
                    if(lasso_id_img != '') first_child.props['data-lasso-id'] = lasso_id_img;
                }
            }
        } catch (error) {
            console.error(error);
        }

        return extraProps;
    }
);

wp.hooks.addFilter(
    "blocks.getSaveElement",
    "affiliate-plugin/lasso",
    (element, block, attributes) => {
        try {
            if( block.name.includes('tph/') || block.name.includes('editor-blocks/') ) {
                return element;
            }

            let lasso_id = attributes["data-lasso-id"] || '';
            let links = attributes["all-links"] || [];
            let lasso_name = attributes["data-lasso-name"] || '';
            let rel = attributes["rel"] || '';
            let target = attributes["target"] || '';

            let figure_props = element && 'props' in element && element.props ? element.props : false;
            let child = figure_props && (typeof figure_props.children == 'object') && 'props' in figure_props.children ? figure_props.children.props : false;

            if(figure_props && child && 'tagName' in child && child.tagName == 'a') {
                if(lasso_id != '') child['data-lasso-id'] = lasso_id;
                if(lasso_name != '') child['data-lasso-name'] = lasso_name;
                if(rel != '') child['rel'] = rel;
                if(target != '') child['target'] = target;
            }

            if(figure_props) {
                // Find all link props in a block and set the correct lasso-data attribute.
                let link_propses = find_link_props(figure_props);
                for ( let index in link_propses ) {
                    let link_prop = link_propses[index];
                    let link_prop_href = link_prop['href'];
                    let link_prop_lasso_id = lasso_id;
                    let link_prop_lasso_name = lasso_name;
                    let link_prop_lasso_target = target;
                    let link_prop_lasso_rel = rel;

                    if ( links[index] != undefined && links[index]['href'] == link_prop_href && links[index]['data_lasso_id'] ) {
                        link_prop_lasso_id = links[index]['data_lasso_id'];
                        link_prop_lasso_name = links[index]['data_lasso_name'];
                        link_prop_lasso_target = links[index]['target'];
                        link_prop_lasso_rel = links[index]['rel'];

                        if(link_prop_lasso_id) link_prop['data-lasso-id'] = link_prop_lasso_id;
                        if(link_prop_lasso_name) link_prop['data-lasso-name'] = link_prop_lasso_name;
                        if(link_prop_lasso_target) link_prop['target'] = link_prop_lasso_target;
                        if(link_prop_lasso_rel) link_prop['rel'] = link_prop_lasso_rel;
                    }
                }

                // Fix gutenberg block attributes invalidate issue for GenerateBlocks - Button
                if ( ! link_propses.length ) {
                    if( block.name.includes('generateblocks/button') && ('htmlAttrs' in figure_props ) && (figure_props['htmlAttrs'] instanceof Object)
                        && ('tagName' in figure_props ) && figure_props.tagName == 'a' ) {
                        let link_prop_lasso_id = lasso_id;
                        let link_prop_lasso_name = lasso_name;
                        let link_prop_lasso_target = target;
                        let link_prop_lasso_rel = rel;

                        if(link_prop_lasso_id) figure_props['htmlAttrs']['data-lasso-id'] = link_prop_lasso_id;
                        if(link_prop_lasso_name) figure_props['htmlAttrs']['data-lasso-name'] = link_prop_lasso_name;
                        if(link_prop_lasso_target) figure_props['htmlAttrs']['target'] = link_prop_lasso_target;
                        if(link_prop_lasso_rel) figure_props['htmlAttrs']['rel'] = link_prop_lasso_rel;
                    }
                }
            }
        } catch (error) {
            console.error('Lasso hook "blocks.getSaveElement" error: ' + error);
        }

        return element;
    }
);

/**
 * Lasso Cactus icon click
 */
function lasso_cactus_icon_click() {
    lasso_segment_tracking('Click Lasso cactus icon');
}

/**
 * Lasso Display block close
 */
function lasso_display_close_event() {
    lasso_segment_tracking('Close "Choose a Display Type" Popup');
}

function get_lasso_shortcode_attributes( shortcode ) {
    var result = {};

    try {
        var raw_attributes = shortcode.replace(/\[lasso/g, '').replace(/\]/g, '').trim();
        var temporary_element = '<div ' + raw_attributes + '></div>';
        temporary_element = jQuery(temporary_element);

        jQuery(temporary_element).each(function() {
            jQuery.each(this.attributes, function() {
                if(this.specified) {
                    result[this.name] = this.value;
                }
            });
        });
    } catch (e) {}

    return result;
}

function get_new_customize_shortcode( current_attributes, cus_attr_name, cus_attr_value ) {
    var attribute_content = '';
    var old_customize_attributes = [];

    current_attributes[cus_attr_name] = cus_attr_value; // Add/Update new customize value

    // Build default attributes and newest customize before
    for (const property in current_attributes) {
        if ((default_lasso_shortcode_attributes.indexOf(property) !== -1) || (property === cus_attr_name) ) {
            var value = current_attributes[property];
            if ( toogle_attributes.includes(property) ) { // Toogle attributes

                if ( attr_toggle_enable_disable_list.includes(property) ) {
                    let attr_value = current_attributes[property] ? 'enable' : '';

                    // Add "enable" value for toogle attribute, else do nothing
                    if ( 'enable' === attr_value ) {
                        attribute_content += ' ' + property + '="' + attr_value + '"';
                    }
                } else {
                    let attr_value = current_attributes[property] ? 'show' : 'hide';

                    // Add "hide" value for toogle attribute, else do nothing
                    if ( 'hide' === attr_value || 'disclosure' === property ) {
                        attribute_content += ' ' + property + '="' + attr_value + '"';
                    }
                }
            } else if ( schema_toogle_attributes.includes(property) ) { // Toogle schema attributes
                let attr_value = current_attributes[property] ? 'enable' : 'disable';

                // Add "enable" value for toogle attribute, else do nothing
                if ( 'enable' === attr_value ) {
                    attribute_content += ' ' + property + '="' + attr_value + '"';
                }
            } else if (value) { // Text box attributes
                attribute_content += ' ' + property + '="' + current_attributes[property] + '"';
            }
        } else {
            old_customize_attributes.push(property);
        }
    }

    // Build old customize attributes later
    old_customize_attributes.forEach(old_cuz_attr => {
        var value = current_attributes[old_cuz_attr];
        if (value) {
            attribute_content += ' ' + old_cuz_attr + '="' + current_attributes[old_cuz_attr] + '"';
        }
    });

    return '[lasso' + attribute_content + ']';
}

function build_lasso_gutenberg_attributes() {
    var result = {
        show_short_code: {
            type: 'boolean',
            default: false
        },
        short_code: {
            type: 'string',
            default: ''
        },
        button_text: {
            type: 'string',
            default: 'Add a Display'
        },
        button_update_text: {
            type: 'string',
            default: 'Update Display'
        },
        button_edit_display: {
            type: 'string',
            default: 'Edit Display'
        },
    };

    try {
        for (const property in customizing_display['all_attributes']) {
            var attr_name = customizing_display['all_attributes'][property];
            result[attr_name] = {
                type: 'string',
                default: ''
            };
        }

        for (const property in schema_display['all_attributes']) {
            var attr_name = schema_display['all_attributes'][property];
            result[attr_name] = {
                type: 'string',
                default: ''
            };
        }
    } catch (e) {
        console.log('Error: Build customize display data', e);
    }

    return result;
}

function find_link_props( item, link_propses = [] ) {
    if (('children' in item) && item['children'] instanceof Array) {
        for ( let index in item['children'] ) {
            let child_item = item['children'][index];
            if ( child_item && child_item instanceof Object && ( 'props' in child_item ) ) {
                if (('type' in child_item) && child_item['type'] == 'a') {
                    link_propses.push(child_item['props']);
                } else {
                    link_propses = find_link_props(child_item['props'], link_propses);
                }
            }
        }
    }

    if ( ('children' in item) && (item['children'] instanceof Object) && ('props' in item['children']) ) {
        if ( ('type' in item['children']) && item['children']['type'] == 'a' ) {
            link_propses.push(item['children']['props']);
        } else {
            link_propses = find_link_props(item['children']['props'], link_propses);
        }
    }

    return link_propses;
}
