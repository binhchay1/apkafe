//get active Tab
import {getActiveTab} from "./yasr-admin-functions";

const activeTab = getActiveTab();

if (activeTab === 'yasr_csv_export') {
    wp.hooks.doAction('yasr_stats_page_csv');
}