/**
 * This is the YASR Sidebar, when a user click on the star on top right
 */

const {__}                                        = wp.i18n; // Import __() from wp.i18n
const {registerPlugin}                            = wp.plugins;
const {PluginSidebar, PluginSidebarMoreMenuItem}  = wp.editPost;
const {PanelBody}                                 = wp.components;
const {Fragment}                                  = wp.element;

const ContentBelowSidebar = () => {
    return <div/>;
};

import {YasrDivRatingOverall} from './yasrGutenUtils';

/**
 * Auto insert block, will show only if auto insert in setting is enabled
 *
 * @param props
 */
const YasrAutoInsert = (props) => {
    //if auto insert is disabled in settings, just return
    if (yasrConstantGutenberg.autoInsert === 'disabled') {
        return (<></>);
    }

    let autoInsertMeta          = wp.data.select('core/editor').getCurrentPost().meta.yasr_auto_insert_disabled;
    let autoInsertCheckboxValue = false;

    if (autoInsertMeta === 'yes') {
        autoInsertCheckboxValue = true;
    }

    /**
     * Save post meta yasr_auto_insert_disabled with switcher value
     *
     * @param event
     */
    const savePostMetaAutoInsert = (event) => {
        const target            = event.target;
        autoInsertCheckboxValue = target.type === 'checkbox' ? target.checked : target.value;

        let metaValue = 'yes';

        if (autoInsertCheckboxValue !== true) {
            metaValue = 'no';
        }

        wp.data.dispatch('core/editor').editPost(
            { meta: { yasr_auto_insert_disabled: metaValue } }
        );
    }

    return (
        <div className="yasr-guten-block-panel-center">
            <hr />
            <label><span>{__('Disable auto insert for this post or page?', 'yet-another-stars-rating')}</span></label>
            <div className="yasr-onoffswitch-big yasr-onoffswitch-big-center" id="yasr-switcher-disable-auto-insert">
                <input type="checkbox"
                       name="yasr_auto_insert_disabled"
                       className="yasr-onoffswitch-checkbox"
                       value="yes"
                       id="yasr-auto-insert-disabled-switch"
                       defaultChecked={autoInsertCheckboxValue}
                       onChange={savePostMetaAutoInsert}
                />
                <label className="yasr-onoffswitch-label" htmlFor="yasr-auto-insert-disabled-switch">
                    <span className="yasr-onoffswitch-inner"/>
                    <span className="yasr-onoffswitch-switch"/>
                </label>
            </div>
        </div>
    );
}


/**
 * YASR sidebar
 *
 * @returns {JSX.Element}
 */
const yasrSidebar = () => {
    let YasrBelowSidebar = [<ContentBelowSidebar key={0}/>];
    {wp.hooks.doAction('yasr_below_panel', YasrBelowSidebar)}

    return (
        <Fragment>
            <PluginSidebarMoreMenuItem target="yasr-guten-sidebar" >
                { __( 'YASR post settings', 'yet-another-stars-rating' ) }
            </PluginSidebarMoreMenuItem>
            <PluginSidebar name="yasr-guten-sidebar" title="YASR Settings">
                <PanelBody>
                    <div className="yasr-guten-block-panel yasr-guten-block-panel-center">
                        <YasrDivRatingOverall />
                        <div>
                            {__('This is the same value that you find the "Yasr: Overall Rating" block.',
                                'yet-another-stars-rating')}
                        </div>
                        {<YasrAutoInsert />}
                        {YasrBelowSidebar}
                    </div>
                </PanelBody>
            </PluginSidebar>
        </Fragment>
    );
}

//Custom sidebar
registerPlugin( 'yasr-sidebar', {
    icon: 'star-half',
    title: __( 'Yasr: Settings', 'yet-another-stars-rating' ),
    render: yasrSidebar
} );