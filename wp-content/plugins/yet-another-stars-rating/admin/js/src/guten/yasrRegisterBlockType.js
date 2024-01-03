const {registerBlockType}    = wp.blocks; // Import from wp.blocks

import metadataOverall      from '../../../../includes/blocks/overall-rating/block.json';
import metadataVV           from '../../../../includes/blocks/visitor-votes/block.json';
import metadataUsers        from '../../../../includes/blocks/ranking-users/block.json';
import metadataReviewers    from '../../../../includes/blocks/ranking-reviewers/block.json';
import metadataRateHistory  from '../../../../includes/blocks/user-rate-history/block.json';
import metadataRankingOv    from '../../../../includes/blocks/ranking-overall-rating/block.json';
import metadataRankingVV    from '../../../../includes/blocks/ranking-visitor-votes/block.json';
import metadataDisplayPosts from '../../../../includes/blocks/display-posts/block.json';


import edit      from './registerBlockTypeEdit';
import saveBlock from './registerBlockTypeSave';


const allShortcodesMetadata = {
    overallRating:   metadataOverall,
    visitorVotes:    metadataVV,
    mostActiveUsers: metadataUsers,
    topReviewers:    metadataReviewers,
    userRateHistory: metadataRateHistory,
    ovRanking:       metadataRankingOv,
    vvRanking:       metadataRankingVV,
    displayPosts:    metadataDisplayPosts
}

/**
 * Register: a Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */

for (const key of Object.keys(allShortcodesMetadata)) {
    registerBlockType (
        allShortcodesMetadata[key], {
            edit: edit,
            save: (props) => {
                return saveBlock(props, allShortcodesMetadata[key]);
            }
        }
    );
}