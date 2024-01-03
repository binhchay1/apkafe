import {YasrRanking} from "../react-components/yasrRanking";

const  {render} = wp.element;

const yasrDrawRankings = () => {
    //check if there is some shortcode with class yasr-table-chart
    const yasrRankingsInDom = document.getElementsByClassName('yasr-stars-rankings');

    if (yasrRankingsInDom.length > 0) {
        for (let i = 0; i < yasrRankingsInDom.length; i++) {
            const tableId      = yasrRankingsInDom.item(i).id;
            const source       = JSON.parse(yasrRankingsInDom.item(i).dataset.rankingSource);
            const params       = JSON.parse(yasrRankingsInDom.item(i).dataset.rankingParams);
            const nonce        = JSON.parse(yasrRankingsInDom.item(i).dataset.rankingNonce);
            const rankingTable = document.getElementById(tableId);

            render(<YasrRanking source={source} tableId={tableId} params={params} nonce={nonce} />, rankingTable);
        }
    }
}

//Drow Rankings
yasrDrawRankings();

export {yasrDrawRankings};