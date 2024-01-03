import {ReturnTableRow} from "./returnTableRow";

const ReturnTableTbody = ({tBodyId, show, data, source, rankingParams, tableId}) => {
    return (
        <tbody id={tBodyId} style={{display: show}}>
        {
            /*Loop the array, and set the style*/
        }
        {data.map((post, i) => {
            let trClass = 'yasr-rankings-td-colored';
            let leftClass  = 'yasr-top-10-most-highest-left';
            let rightClass = 'yasr-top-10-most-highest-right'
            if(source === 'author_ranking') {
                trClass = 'yasr-rankings-td-white';
                leftClass  = 'yasr-top-10-overall-left';
                rightClass = 'yasr-top-10-overall-right'
            }
            if (i % 2 === 0) {
                trClass = 'yasr-rankings-td-white';
                if(source === 'author_ranking') {
                    trClass = 'yasr-rankings-td-colored';
                }
            }

            return(
                <ReturnTableRow
                    key={post.post_id}
                    source={source}
                    tableId={tableId}
                    rankingParams={rankingParams}
                    post={post}
                    trClass={trClass}
                    leftClass={leftClass}
                    rightClass={rightClass}
                />
            )
        })
        }
        </tbody>
    )
};

export {ReturnTableTbody};
