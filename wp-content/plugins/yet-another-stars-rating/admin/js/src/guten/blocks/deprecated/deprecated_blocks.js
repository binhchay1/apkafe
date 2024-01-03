//setting costants
const { __ } = wp.i18n; // Import __() from wp.i18n
const {registerBlockType} = wp.blocks; // Import from wp.blocks
const {PanelBody, PanelRow} = wp.components;
const {Fragment} = wp.element;
const {BlockControls,InspectorControls} = wp.editor;


const yasrOptionalText            = __('All these settings are optional', 'yet-another-stars-rating');

const yasrLabelSelectSize         = __('Choose Size', 'yet-another-stars-rating');

const yasrSelectSizeChoose        = __('Choose stars size', 'yet-another-stars-rating');
const yasrSelectSizeSmall         = __('Small', 'yet-another-stars-rating');
const yasrSelectSizeMedium        = __('Medium', 'yet-another-stars-rating');
const yasrSelectSizeLarge         = __('Large', 'yet-another-stars-rating');

const yasrLeaveThisBlankText      = __('Leave this blank if you don\'t know what you\'re doing.', 'yet-another-stars-rating');

const yasrOverallDescription      = __('Remember: only the post author can rate here.', 'yet-another-stars-rating');
const yasrVisitorVotesDescription = __('This is the star set where your users will be able to vote', 'yet-another-stars-rating');

const yasrDeprecatedOv = __('This block is now deprecated. It will still work, but I suggest to replace it with the ' +
    'new one by simply removing it and adding "Yasr Overall Rating" again.','yet-another-stars-rating');

const yasrDeprecatedVV = __('This block is now deprecated. It will still work, but I suggest to replace it with the ' +
    'new one by simply removing it and adding "Yasr Visitors Votes" again.','yet-another-stars-rating');

function YasrProText () {
    const YasrProText1 = __('To be able to customize this ranking, you need', 'yet-another-stars-rating');
    const YasrProText2 =  __('You can buy the plugin, including support, updates and upgrades, on',
        'yet-another-stars-rating');

    return (
        <h3>
            {YasrProText1}
            &nbsp;
            <a href="https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=gutenberg_panel&utm_campaign=yasr_editor_screen&utm_content=rankings#yasr-pro">
                Yasr Pro.
            </a><br />
            {YasrProText2}
            &nbsp;
            <a href="https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=gutenberg_panel&utm_campaign=yasr_editor_screen&utm_content=rankings">
                yetanotherstarsrating.com
            </a>
        </h3>
    )

}

function YasrNoSettingsPanel (props) {

    return (
        <div>
            <YasrProText/>
        </div>
    );

}

registerBlockType(
    'yet-another-stars-rating/yasr-overall-rating', {
        title: __( '[DEPRECATED]Yasr: Overall Rating', 'yet-another-stars-rating' ),
        description: yasrDeprecatedOv,
        icon: 'star-half',
        keywords: [
            __('rating', 'yet-another-stars-rating'),
            __('author', 'yet-another-stars-rating'),
            __('overall', 'yet-another-stars-rating')
        ],
        attributes: {
            overallRatingMeta: {
                type: 'number',
                source: 'meta',
                meta: 'yasr_overall_rating'
            },
            size: {
                type: 'string',
                default: '--'
            },
            postId: {
                type: 'string',
                default: '--'
            },
        },

        edit:
            function(props) {
                const {attributes: {overallRatingMeta, size, postId}, setAttributes, isSelected} = props;

                let overallRating = overallRatingMeta;

                let sizeAttribute = null;
                let postIdAttribute = null;
                let isNum = false;

                if (size !== '--') {
                    sizeAttribute = ' size="' + size + '"';
                }

                isNum = /^\d+$/.test(postId);

                if (postId !== '--' && isNum === true) {
                    postIdAttribute = ' postid="' +postId + '"';
                }

                class YasrDivRatingOverall extends React.Component  {

                    constructor(props) {
                        super(props);
                        this.yasrOverallRateThis = __("Rate this article / item", 'yet-another-stars-rating');
                    }

                    render () {
                        return (
                            <div>
                                {this.yasrOverallRateThis}
                                <div>
                                    <div id="overall-rater" ref={()=>
                                        raterJs({
                                            starSize: 32,
                                            step: 0.1,
                                            showToolTip: false,
                                            rating: overallRating,
                                            readOnly: false,
                                            element: document.querySelector("#overall-rater"),
                                            rateCallback: function rateCallback(rating, done) {

                                                rating = rating.toFixed(1);
                                                rating = parseFloat(rating);

                                                setAttributes( { overallRatingMeta: rating } );

                                                this.setRating(rating);

                                                done();

                                            }
                                        })
                                    }
                                    />
                                </div>
                            </div>

                        );
                    }

                }

                function YasrPrintSelectSize () {
                    return (
                        <form>
                            <select value={size} onChange={ yasrSetStarsSize }>
                                <option value="--">{yasrSelectSizeChoose}</option>
                                <option value="small">{yasrSelectSizeSmall}</option>
                                <option value="medium">{yasrSelectSizeMedium}</option>
                                <option value="large">{yasrSelectSizeLarge}</option>
                            </select>
                        </form>
                    );
                }

                function yasrSetStarsSize(event) {
                    const selected = event.target.querySelector( 'option:checked' );
                    setAttributes( { size: selected.value } );
                    event.preventDefault();
                }

                function YasrPrintInputId() {
                    return (
                        <div>
                            <input type="text" size="4" onKeyPress={yasrSetPostId} />
                        </div>
                    );
                }

                function yasrSetPostId (event) {
                    if (event.key === 'Enter') {
                        const postIdValue = event.target.value;

                        //postID is always a string, here I check if this string is made only by digits
                        var isNum = /^\d+$/.test(postIdValue);

                        if (isNum === true || postIdValue === '') {
                            setAttributes({postId: postIdValue})
                        }
                        event.preventDefault();
                    }
                }

                function YasrOverallPanel (props) {

                    return (
                        <InspectorControls>
                            <div class="yasr-guten-block-panel yasr-guten-block-panel-center">
                                <YasrDivRatingOverall />
                            </div>

                            <PanelBody title='Settings'>
                                <h3>{yasrOptionalText}</h3>

                                <div className="yasr-guten-block-panel">
                                    <label>{yasrLabelSelectSize}</label>
                                    <div>
                                        <YasrPrintSelectSize />
                                    </div>
                                </div>

                                <div className="yasr-guten-block-panel">
                                    <label>Post ID</label>
                                    <YasrPrintInputId/>
                                    <div className="yasr-guten-block-explain">
                                        {yasrLeaveThisBlankText}
                                    </div>
                                </div>

                                <div className="yasr-guten-block-panel">
                                    {yasrOverallDescription}
                                </div>
                            </PanelBody>
                        </InspectorControls>
                    );

                }

                return (
                    <Fragment>
                        <YasrOverallPanel />
                        <div className={ props.className }>
                            [yasr_overall_rating{sizeAttribute}{postIdAttribute}]
                            {isSelected && <YasrPrintSelectSize />}
                        </div>
                    </Fragment>
                );
            },

        /**
         * The save function defines the way in which the different attributes should be combined
         * into the final markup, which is then serialized by Gutenberg into post_content.
         *
         * The "save" property must be specified and must be a valid function.
         *
         * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
         */
        save:
            function(props) {
                const {attributes: {size, postId}} = props;

                let yasrOverallAttributes = '';
                let post_id = postId;

                if (size) {
                    yasrOverallAttributes += 'size="' +size+ '"';
                }
                if (postId) {
                    if(postId === '--') {
                        post_id = wp.data.select("core/editor").getCurrentPostId();
                    }
                    yasrOverallAttributes += ' postid="'+post_id+'"';
                }

                return (
                    <div>[yasr_overall_rating {yasrOverallAttributes}]</div>
                );
            },

    });

registerBlockType(
    'yet-another-stars-rating/yasr-visitor-votes', {
        title: __( '[DEPRECATED]Yasr: Visitor Votes', 'yet-another-stars-rating' ),
        description: yasrDeprecatedVV,
        icon: 'star-half',
        keywords: [
            __('rating', 'yet-another-stars-rating'),
            __('visitor', 'yet-another-stars-rating'),
            __('votes', 'yet-another-stars-rating')
        ],
        attributes: {
            //name of the attribute
            size: {
                type: 'string',
                default: '--'
            },
            postId: {
                type: 'string',
                default: '--'
            },
        },

        edit:
            function( props ) {

                const { attributes: { size, postId }, setAttributes, isSelected } = props;

                let sizeAttribute = null;
                let postIdAttribute = null;
                let isNum = false;

                isNum = /^\d+$/.test(postId);

                if (size !== '--') {
                    sizeAttribute = ' size="' + size + '"';
                }

                if (postId !== '--' && isNum === true) {
                    postIdAttribute = ' postid="' +postId + '"';
                }

                function YasrPrintSelectSize () {
                    return (
                        <form>
                            <select value={size} onChange={ yasrSetStarsSize }>
                                <option value="--">{yasrSelectSizeChoose}</option>
                                <option value="small">{yasrSelectSizeSmall}</option>
                                <option value="medium">{yasrSelectSizeMedium}</option>
                                <option value="large">{yasrSelectSizeLarge}</option>
                            </select>
                        </form>
                    );
                }

                function yasrSetStarsSize(event) {
                    const selected = event.target.querySelector( 'option:checked' );
                    setAttributes( { size: selected.value } );
                    event.preventDefault();
                }

                function YasrPrintInputId() {
                    return (
                        <div>
                            <input type="text" size="4" onKeyPress={yasrSetPostId} />
                        </div>
                    );
                }

                function yasrSetPostId (event) {
                    if (event.key === 'Enter') {
                        const postIdValue = event.target.value;

                        //postID is always a string, here I check if this string is made only by digits
                        var isNum = /^\d+$/.test(postIdValue);

                        //if isNum or if is empty (to remove the value)
                        if (isNum === true || postIdValue === '') {
                            setAttributes({postId: postIdValue})
                        }
                        event.preventDefault();
                    }
                }

                function YasrVVPanel (props) {

                    return (
                        <InspectorControls>
                            <PanelBody title='Settings'>
                                <h3>{yasrOptionalText}</h3>

                                <div className="yasr-guten-block-panel">
                                    <label>{yasrLabelSelectSize}</label>
                                    <div>
                                        <YasrPrintSelectSize />
                                    </div>
                                </div>

                                <div className="yasr-guten-block-panel">
                                    <label>Post ID</label>
                                    <YasrPrintInputId/>
                                    <div className="yasr-guten-block-explain">
                                        {yasrLeaveThisBlankText}
                                    </div>
                                </div>

                                <div className="yasr-guten-block-panel">
                                    {yasrVisitorVotesDescription}
                                </div>
                            </PanelBody>
                        </InspectorControls>
                    );

                }

                return (
                    <Fragment>
                        <YasrVVPanel />
                        <div className={props.className}>
                            [yasr_visitor_votes{sizeAttribute}{postIdAttribute}]
                            {isSelected && <YasrPrintSelectSize />}
                        </div>
                    </Fragment>
                );

            },

        /**
         * The save function defines the way in which the different attributes should be combined
         * into the final markup, which is then serialized by Gutenberg into post_content.
         *
         * The "save" property must be specified and must be a valid function.
         *
         * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
         */
        save:
            function( props ) {
                const { attributes: {size, postId} } = props;

                let yasrVVAttributes = '';
                let post_id = postId;

                if (size) {
                    yasrVVAttributes += 'size="' +size+ '"';
                }
                if (postId) {
                    if(postId === '--') {
                        post_id = wp.data.select("core/editor").getCurrentPostId();
                    }
                    yasrVVAttributes += ' postid="'+post_id+'"';
                }

                return (
                    <div>[yasr_visitor_votes {yasrVVAttributes}]</div>
                );
            },

    });

//hide the deprecated blocks from the panel
wp.data.dispatch('core/edit-post').hideBlockTypes('yet-another-stars-rating/yasr-overall-rating');
wp.data.dispatch('core/edit-post').hideBlockTypes('yet-another-stars-rating/yasr-visitor-votes');