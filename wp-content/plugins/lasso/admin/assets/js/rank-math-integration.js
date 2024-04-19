/**
 * RankMath Lasso Shortcode Content integration class
 */
class RankMathLassoShortcodeContent {
	/**
	 * Class constructor
	 */
	constructor() {
		this.init();
		this.hooks();
	}

	/**
	 * Init the get Lasso shortcode content
	 */
	init() {
		this.getContent = this.getContent.bind( this );
	}

	/**
	 * Hook into Rank Math App eco-system
	 */
	hooks() {
		wp.hooks.addFilter( 'rank_math_content', 'rank-math', this.getContent, 11 );
		wp.hooks.addAction( 'lasso_after_rendering_shortcode_in_gutenberg', 'namespace', this.events );
		wp.hooks.addAction( 'editor_saving_post_refresh_rank_match_content', 'namespace', this.events );
	}

	/**
	 * Capture events when after rendered Lasso Shortcode to refresh Rank Math analysis
	 * Use lodash.debounce method to make sure just process 1 time when the event calling continuously
	 */
	events = _.debounce(function () {
		rankMathEditor.refresh( 'content' );
	}, 1000);

	/**
	 * Gather Lasso shortcode content data for analysis
	 *
	 * @param {string} content Rank Math Content analysis.
	 *
	 * @return {string} Replaced content.
	 */
	getContent = function( content ) {
		let lasso_shortcodes = jQuery('.lasso-container');

		if ( lasso_shortcodes.length ) {
			jQuery.each(lasso_shortcodes, function( index, lasso_shortcode ) {
				let shortcode_id = jQuery(lasso_shortcode).attr('id');
				let shortcode_full = "\n" + `<div id="${shortcode_id}" class="lasso-container">` + "\n";
				shortcode_full += jQuery(lasso_shortcode).html() + "\n";
				shortcode_full += "</div>\n";
				content += shortcode_full;
			});
		}

		return content;
	}
}

jQuery( function() {
	new RankMathLassoShortcodeContent();
} );

// // Update Rank Math content when post saving.
// wp.data.subscribe(function () {
// 	let isSavingPost = wp.data.select('core/editor').isSavingPost();

// 	if (isSavingPost) {
// 		wp.hooks.doAction('editor_saving_post_refresh_rank_match_content');
// 	}
// });
