<?php

/*

Copyright 2014 Dario Curvino (email : d.curvino@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

/**
 * Create a new table class that will extend the WP_List_Table
 */
class YasrStatsListTable extends WP_List_Table {

    /**
     * I will store here the $set_id
     *
     * @var $set_id
     */
    private $set_id;


    /**
     * The array that will store the multiset data
     *      $multiset = array {
     *          [SETID]=> array {
     *              ["set_name"]=> SET NAME
     *              [FIELD ID]  => FIELD NAME
     *          }
     *      }
     *
     * @var array
     */
    private $multiset = array();

    private $active_tab;

    public function __construct($active_tab) {
        parent::__construct();
        $this->active_tab = $active_tab;
    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        //print bulk_Actions
        $this->get_bulk_actions();
        $this->process_bulk_action();

        $perPage     = 25;
        $currentPage = $this->get_pagenum();
        $offset      = ($currentPage - 1) * $perPage;

        if ($this->active_tab === 'logs_multi') {
            $data = YasrDB::returnLogMulti($perPage, $offset);

            //The number of total rows on _yasr_log
            $totalItems = YasrDB::returnLogMultiNumberOfRows();
        }
        else if ($this->active_tab === 'overall') {
            $data = YasrDB::allOverallRatings($perPage, $offset);

            //The number of total rows on postmeta where metakey = yasr_overall_rating
            $totalItems = YasrDB::ovNumberOfRows();
        }
        //Visitor votes logs is the default tab
        else {
            $data = YasrDB::allVisitorVotes($perPage, $offset);

            //The number of total rows on _yasr_log
            $totalItems = YasrDB::vvNumberOfRows();
        }

        usort($data, array($this, 'sort_data'));

        $this->set_pagination_args(
            array(
                'total_items' => $totalItems,
                'per_page'    => $perPage
            )
        );

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items           = $data;

    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {

        $columns = array(
            'cb'      => '<input type="checkbox" />',
            'id'      => 'ID',
            'post_id' => 'Title',
            'vote'    => 'Vote',
            'user_id' => 'User ID',
            'date'    => 'Date'
        );

        if ($this->active_tab === 'logs_multi') {
            //insert multiset and field name
            $columns = array_slice($columns, 0, 3, true) + array('set_type' => 'MultiSet', 'field_id' => 'Field Name')
                + array_slice($columns, 3, count($columns) - 1, true);
        }

        if (YASR_ENABLE_IP === 'yes') {
            //do not show column IP if it is overall rating
            if ($this->active_tab !== 'overall') {
                $columns['ip'] = 'IP';
            }
        }
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        return array('id');
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns() {

        $sortable_columns = array(
            'post_id' => array('post_id', false),
            'user_id' => array('user_id', false),
            'vote'    => array('vote', false),
            'date'    => array('date', false),
            'ip'      => array('ip', false)
        );

        if ($this->active_tab === 'logs_multi') {
            $sortable_columns['set_type'] = array('set_type', false);
            $sortable_columns['field_id'] = array('field_id', false);
        }

        return $sortable_columns;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param Array  $item        Data
     * @param String $column_name - Current column name
     *
     * @return Mixed|void
     */
    protected function column_default($item, $column_name) {
        global $wpdb;

        switch ($column_name) {
            case 'post_id':
                $post_id    = (int)$item[$column_name];

                $title_post = wp_strip_all_tags(get_the_title($post_id));
                $link       = get_permalink($post_id);

                return '<a href="' . $link . '">' . $title_post . '</a>';

            case 'user_id':
                $user_id = (int)$item[$column_name];

                $user = get_user_by('id', $user_id);

                //If !user means that the vote are anonymous
                if ($user === false) {
                    $user             = (object) array('user_login');
                    $user->user_login = __('anonymous', 'yet-another-stars-rating');
                }

                return $user->user_login;

            case 'set_type':
                if (isset($item['set_type'])) {
                    $this->set_id = (int) $item['set_type'];
                }

                //do the query only if the key set_id doesn't exist into array multiset
                if (!array_key_exists($this->set_id, $this->multiset)) {
                    $this->multiset[$this->set_id]['set_name'] = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT set_name
                                FROM " . YASR_MULTI_SET_NAME_TABLE . "
                                WHERE set_id = %d", $this->set_id
                        )
                    );
                }

                //return the multiset name
                if (!empty($this->multiset[$this->set_id]['set_name'])) {
                    return $this->multiset[$this->set_id]['set_name'];
                }

                return __('Multi Set doesn\'t exists', 'yet-another-stars-rating');

            case 'field_id':
                $field_id = (int)$item[$column_name];

                //get the field name only if not exists in $this->multiset
                if(!array_key_exists($field_id, $this->multiset[$this->set_id])) {
                    $this->multiset[$this->set_id][$field_id] =
                        $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT field_name
                                    FROM " . YASR_MULTI_SET_FIELDS_TABLE . "
                                    WHERE parent_set_id = %d
                                    AND field_id = %d",
                                $this->set_id,
                                $field_id
                            )
                    );
                }

                if (!empty($this->multiset[$this->set_id][$field_id])) {
                    return $this->multiset[$this->set_id][$field_id];
                }

                return __('Field doesn\'t exists', 'yet-another-stars-rating');

            case 'date':
                $date = $item[$column_name];
                if ($item[$column_name] === '0000-00-00 00:00:00') {
                    $date = __('Imported Data', 'yet-another-stars-rating');
                }
                return $date;

            //All other columns must return their content
            case 'vote':
            case 'ip':
                return $item[$column_name];
        }

    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    protected function sort_data($a, $b) {

        // Set defaults (just need to avoid undefined variable at first load,
        // it is already ordered with the query
        $orderby = 'date';
        $order   = 'desc';

        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }


    protected function get_bulk_actions() {
        return array(
            'delete' => 'Delete'
        );
    }

    protected function column_cb($item) {
        return "<input type='checkbox' name='yasr_logs_votes_to_delete[]' id='{$item['id']}' value='{$item['id']}' />";
    }

    //process bulk action
    protected function process_bulk_action() {
        if ($this->current_action() === 'delete') {
            check_admin_referer('yasr-delete-stats-logs', 'yasr-nonce-delete-stats-logs');

            global $wpdb;

            $table = YASR_LOG_TABLE;

            if ($this->active_tab === 'logs_multi') {
                $table = YASR_LOG_MULTI_SET;
            }

            foreach ($_POST['yasr_logs_votes_to_delete'] as $log_id) {
                //force to be an int
                $log_id = (int) $log_id;

                //If user is deleting an overall rating value, use delete_meta and return
                if ($this->active_tab === 'overall') {
                    delete_meta($log_id);
                    return;
                }

                //delete the log id
                $wpdb->delete(
                    $table, array(
                        'id' => $log_id
                    ), array('%d')
                );

            }
        }

    }

}
