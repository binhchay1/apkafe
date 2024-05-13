<?php

if (!defined('ABSPATH'))
    die;

class CustomFieldCategory
{
    function __construct()
    {
        add_action('admin_init', [$this, 'init']);
        add_action('edit_term', [$this, 'save_custom_field_category']);
        add_action('create_term', [$this, 'save_custom_field_category']);
    }

    function init()
    {
        $arrayTaxonomies = get_taxonomies();

        if (is_array($arrayTaxonomies)) {
            foreach ($arrayTaxonomies as $taxonomy) {
                if ($taxonomy == 'category' || $taxonomy == 'product_cat') {
                    add_action($taxonomy . '_add_form_fields', [$this, 'extra_category_fields_add']);
                    add_action($taxonomy . '_edit_form_fields', [$this, 'extra_category_fields_edit']);
                }
            }
        }
    }

    function extra_category_fields_edit()
    {
        echo '<div class="form-field">
	    <label>List hot</label>
	    <ul id="list-hot">
	    </ul>
	    <label>List popular</label>
	    <ul id="list-popular">
	    </ul>
	    </div>';
    }

    function extra_category_fields_add()
    {
        echo '<div class="form-field">
	    <label>List hot</label>
	    <ul id="list-hot">
	    </ul>
	    <label>List popular</label>
	    <ul id="list-popular">
	    </ul>
	    </div>';
    }

    function save_custom_field_category($term_id)
    {
        // if (isset($_POST['zci_taxonomy_image'])) {
        //     update_option('z_taxonomy_image' . $term_id, $_POST['zci_taxonomy_image'], false);
        // }

        // if (isset($_POST['zci_taxonomy_image_id'])) {
        //     update_option('z_taxonomy_image_id' . $term_id, $_POST['zci_taxonomy_image_id'], false);
        // }
    }
}

if (class_exists('ZCategoriesImages')) {
    $custom_field = new CustomFieldCategory();
}
