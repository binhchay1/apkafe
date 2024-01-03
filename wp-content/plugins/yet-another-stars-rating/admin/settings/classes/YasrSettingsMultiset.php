<?php

class YasrSettingsMultiset {

    public function init () {
        //This is for general options
        add_action('admin_init', array($this, 'manageMultiset'));

        //Add Ajax Endpoint to manage more multi set
        add_action('wp_ajax_yasr_get_multi_set', array($this, 'editFormAjax'));
    }

    public function manageMultiset () {
        register_setting(
            'yasr_multiset_options_group', // A settings group name. Must exist prior to the register_setting call. This must match the group name in settings_fields()
            'yasr_multiset_options', //The name of an option to sanitize and save.
            array($this, 'sanitize')
        );

        $option_multiset = get_option('yasr_multiset_options');

        if ($option_multiset === false) {
            $option_multiset = array(
                'show_average' => 'no'
            );
        }

        if (!isset($option_multiset['show_average'])) {
            $option_multiset['show_average'] = 'yes';
        }

        $this->addSettingsSections();
        $this->addSettingsFields($option_multiset);
    }

    /**
     * Run add_setting_section for the page
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.3
     */
    public function addSettingsSections() {

        //Add Section for new multiset
        add_settings_section(
            'yasr_new_multiset_form_section_id',
            '',
            '',
            'yasr_new_multiset_form'
        );

        //add section for show/hide average
        add_settings_section(
            'yasr_multiset_options_section_id',
            '',
            '',
            'yasr_multiset_tab'
        );
    }

    /**
     * Run addSettingsField for the page
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.3
     */
    public function addSettingsFields ($option_multiset) {
        $yasr_settings_descriptions = new YasrSettingsDescriptions();

        add_settings_field(
            'add_multi_set',
            $yasr_settings_descriptions->descriptionMultiset(),
            array($this, 'formCreateMultiset'),
            'yasr_new_multiset_form',
            'yasr_new_multiset_form_section_id'
        );

        add_settings_field(
            'yasr_multiset_hide_average_id',
            $yasr_settings_descriptions->descriptionShowAverage(),
            array($this, 'hideAverage'),
            'yasr_multiset_tab',
            'yasr_multiset_options_section_id',
            $option_multiset
        );
    }

    /**
     * Main container with both forms for forms, new and edit
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.3
     */
    public function formCreateMultiset () {
        ?>
        <div class="yasr-settings-multi-set">
            <div style="width: 49%;" id="yasr-new-multi-set">
                <form method="post">
                    <div class="yasr-multi-set-form-headers">
                        <?php esc_html_e('Add new Multi Set', 'yet-another-stars-rating'); ?>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span style="color: #FEB209; font-size: x-large"> | </span> = required
                    </div>

                    <div>
                        <?php
                            wp_nonce_field('add-multi-set', 'add-nonce-new-multi-set'); //Must be inside the form
                            $multiset_name_info = esc_html__('Unique name to identify your set.', 'yet-another-stars-rating');
                        ?>
                        <div>
                            <div>
                                <br />
                                <div id="yasr-multiset-page-new-set-criteria-name" class="criteria-row">
                                    <label for="new-multi-set-name">
                                    </label>
                                    <input type="text"
                                           name="multi-set-name"
                                           id="new-multi-set-name"
                                           class="input-text-multi-set"
                                           placeholder="Name"
                                           required
                                    >
                                    <span class="dashicons dashicons-info yasr-multiset-info-delete"
                                          title="<?php echo esc_attr($multiset_name_info) ?>"></span>
                                </div>
                            </div>

                            <?php $this->newMultiCriteria(); ?>

                            <div>
                                <button class="button-secondary" id="new-criteria-button">
                                    <span class="dashicons dashicons-insert" style="line-height: 1.4"></span>
                                    <?php esc_html_e('Add new Criteria', 'yet-another-stars-rating'); ?>
                                </button>
                            </div>
                        </div>
                        <br />
                        <div>
                            <p>
                                <input type="submit"
                                       value="<?php esc_attr_e('Create New Set', 'yet-another-stars-rating') ?>"
                                       class="button-primary"
                                />
                            </p>
                        </div>
                    </div>
                </form>
            </div>
            <?php $this->formEditMultisetContainer() ?>
        </div>
        <?php
    }

    /**
     * Default form when a new set is created
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.3
     */
    public function newMultiCriteria () {
        ?>
            <div id="new-set-criteria-container">
                <?php
                for ($i = 1; $i <= 4; $i ++) {
                    $element_n =  esc_html__('Element ', 'yet-another-stars-rating') . '#'.$i;
                    $name         = 'multi-set-name-element-'.$i;
                    $id           = 'multi-set-name-element-'.$i;
                    $id_container = 'criteria-row-container-'.$i;

                    $required  = '';

                    if($i === 1) {
                        $placeholder = 'Story';
                        $required    = 'required';
                    }
                    elseif($i === 2) {
                        $placeholder = 'Gameplay';
                        $required    = 'required';
                    }
                    elseif($i === 3) {
                        $placeholder = 'Graphics';
                    }
                    elseif($i === 4) {
                        $placeholder = 'Sound';
                    }
                    else {
                        $placeholder = $element_n;
                    }

                    $this->outputCriteria($id_container, $i, $id, $name, $placeholder, $required, 'new-form');
                } //End foreach
                ?>
            </div>
        <?php
    }

    /**
     * Output the single criteria row, used in both new and edit forms
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $id_container
     * @param $i
     * @param $input_id
     * @param $name
     * @param $placeholder
     * @param $required
     * @param $value
     * @param $context
     *
     * @since  3.1.3
     */
    public function outputCriteria (
            $id_container, $i, $input_id, $name, $placeholder, $required, $context, $value='')
    {
        $i = (int)$i;
        $class = 'criteria-row ';

        if($context === 'edit-form') {
            $class .= 'edit-form-removable-criteria';
            $remove_button_id = 'edit-form-remove-' . $i;
        } else {
            $class .= 'removable-criteria';
            $remove_button_id = 'new-form-remove-' . $i;
        }

        ?>
        <div class="<?php echo esc_attr($class) ?>"
             id="<?php echo esc_attr($id_container) ?>"
             data-row="<?php echo esc_attr($i) ?>">
            <label>
                <input type="text"
                       name="<?php echo esc_attr($name); ?>"
                       value="<?php echo esc_attr($value); ?>"
                       id="<?php echo esc_attr($input_id); ?>"
                       class="input-text-multi-set"
                       placeholder="<?php echo esc_attr($placeholder); ?>"
                       <?php echo esc_attr($required) ?>
                >
            </label>

            <?php
                if($required !== 'required') {
                    $checkbox_id = 'remove-element-'.$i;
                    //When the delete button is clicked, remove the dom element and check a hidden checkbox
                    echo '<span class="dashicons dashicons-remove yasr-multiset-info-delete criteria-delete" 
                                id="'.esc_attr($remove_button_id).'"
                                data-id-criteria="'.esc_attr($id_container).'"
                                onclick="document.getElementById(\''.(esc_js($id_container)).'\').remove();
                                document.getElementById(\''.(esc_js($checkbox_id)).'\').checked = true;">
                          </span>';
                }
            ?>
        </div>

        <?php
    }

    /**
     * Shows a form to edit the Multi Set
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.3
     */
    public function formEditMultisetContainer() {
        ?>
        <div class="yasr-manage-multiset" style="width: 49%;" >
            <div class="yasr-multi-set-form-headers">
                <?php esc_html_e('Manage Multi Set', 'yet-another-stars-rating'); ?>
            </div>

            <?php
                global $wpdb;
                $multi_set   = YasrDB::returnMultiSetNames();
                $n_multi_set = $wpdb->num_rows; //wpdb->num_rows always store the results count for a ‘SELECT’

                if($n_multi_set < 1 ) {
                    esc_html_e('No Multi Set were found', 'yet-another-stars-rating');
                    return;
                }

                //if n_multiset > 1, print the select
                if ($n_multi_set > 1) {
                    $title = __('Select Set:', 'yet-another-stars-rating');
                    $id    = 'yasr_select_set';
                    YasrPhpFieldsHelper::printSelectMultiset(
                            $multi_set,
                            $title,
                            $id,
                            '',
                            'nonce-settings-edit-form'
                    );
                }

                echo '<hr style="border-top: 1px solid #ddd;">';

                //get the first set id
                $set_id     = $multi_set[0]->set_id;
                $this->formEditMultiset($set_id);
            ?>
        </div>
        <?php
    }

    /**
     * Print the form to edit the multi set
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $set_id
     *
     * @since  3.1.7
     * @return void
     */
    public function formEditMultiset($set_id) {
        $set_fields = YasrDB::multisetFieldsAndID((int)$set_id);
        ?>
        <form action=" <?php echo esc_url(admin_url('options-general.php?page=yasr_settings_page&tab=manage_multi')); ?>"
              id="form_edit_multi_set" method="post">

            <input type="hidden" name="yasr_edit_multi_set_form" value="<?php echo esc_attr($set_id); ?>"/>

            <div id="yasr-table-form-edit-multi-set">
                <?php
                    $this->formEditMultisetPrintFields($set_fields, $set_id);
                ?>
            </div>
            <?php
                wp_nonce_field('edit-multi-set', 'add-nonce-edit-multi-set')
            ?>
        </form>
        <?php
    }

    /**
     * Print the all the fields for form edit multiset
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $set_fields
     * @param $set_id
     *
     * @since  refactor in 3.1.7
     * @return void
     */
    private function formEditMultisetPrintFields($set_fields, $set_id) {
        //this mean that set name exists, but has no fields (this could happen if manually deleted in db)
        if(!is_array($set_fields)) {
            //delete the empty set
            $this->deleteMultisetName(false, $set_id);
            return;
        }

        $i = 1;
        echo '<div id="edit-set-criteria-container">';
            foreach ($set_fields as $field) {
                $id_container  = 'edit-form-criteria-row-container-'.$i;
                $input_name    = 'edit-multi-set-element-'.$i;
                $input_id      = 'edit-form-multi-set-name-element-'.$i;
                //required if $i < 3, empty otherwise
                $required = ($i < 3) ? 'required' : '';

                $this->outputCriteria($id_container, $i, $input_id, $input_name, '', $required, 'edit-form', $field['name']);

                $this->formEditMultisetPrintRowHiddenValues($field, $i);

                $i ++;
            }
        echo '</div>';

        echo  '<div>
                <button class="button-secondary" id="yasr-add-field-edit-multiset">
                    <span class="dashicons dashicons-insert" style="line-height: 1.4"></span>'
                    .esc_html__('Add new Criteria', 'yet-another-stars-rating').'
                </button>
            </div>';

        //print row to remove entire multiset
        $this->formEditMultisetPrintRemoveMultiset($i-1, $set_id);

        //Print buttons "add element" and "Save changes"
        $this->editFormPrintSave();
    }

    /**
     * Print the hidden values required for the edit form
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $field
     * @param $i
     *
     * @since  3.1.7
     * @return void
     */
    private function formEditMultisetPrintRowHiddenValues($field, $i) {
        $i             = (int)$i;
        $hidden_name   = 'db-id-for-element-'.$i;
        $checkbox_name = 'remove-element-'.$i;

        ?>
        <span style="display: none;">
            <!--This hidden field is needed to update the value-->
            <input type="hidden"
                   value="<?php echo esc_attr($field['id']) ?>"
                   name="<?php  echo esc_attr($hidden_name) ?>"
            />
            <?php
            //This hidden field is needed to delete the value, only if i > 2
            if($i > 2) { ?>
                <label>
                    <input type="checkbox"
                           value="<?php echo esc_attr($field['id']) ?>"
                           name="<?php echo esc_attr($checkbox_name) ?>"
                           id="<?php echo esc_attr($checkbox_name) ?>"
                    >
                </label>
                <?php
            } ?>
        </span>
        <?php
    }

    /**
     * Return the row with the checkbox to remove the entire set
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $i
     * @param $set_id
     *
     * @since 3.1.7
     * @return void
     */
    private function formEditMultisetPrintRemoveMultiset($i, $set_id) {
        $i = (int)$i;
        $set_id = (int)$set_id;
        ?>
        <input type="hidden"
               name="yasr-edit-form-number-elements"
               id="yasr-edit-form-number-elements"
               value="<?php echo esc_attr($i)?>"
        >
        <div class="yasr-edit-form-remove-entire-set" id="yasr-edit-form-remove-entire-set">
            <span>
                <?php echo esc_html__('Remove whole set?', 'yet-another-stars-rating')?>
            </span>

            <span>
                <label>
                    &nbsp;
                    <input type="checkbox"
                           name="yasr-remove-multi-set"
                           value="<?php echo esc_attr($set_id)?>"
                </label>
            </span>
        </div>

        <div>
            <?php
                esc_html_e('If you remove something you will remove all the votes for that set or field.',
            'yet-another-stars-rating');
                echo '<br />';
                printf(
                        esc_html__('This operation %s can\'t be %s undone.', 'yet-another-stars-rating'),
                        '<strong>', '</strong>');
            ?>
        </div>
        <?php
    }

    /**
     * Print edit forms buttons
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    private function editFormPrintSave () {
        ?>
        <div>
            <br />
            <div>
                <input type="submit"
                       value="<?php esc_attr_e('Save changes', 'yet-another-stars-rating') ?>"
                       class="button-primary">
            </div>
        </div>
        <div id="yasr-element-limit" style="display:none; color:red">
            <span>
                <?php esc_html_e('You can use up to 9 elements', 'yet-another-stars-rating') ?>
            </span>
        </div>
        <?php
    }

    /**
     * Ajax Callback to print the edit form
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    public function editFormAjax() {
        if(!current_user_can('manage_options')) {
            die('Not Allowed');
        }

        if(!wp_verify_nonce($_POST['nonce'], 'nonce-settings-edit-form')) {
            die('Not Allowed');
        }

        $set_id = (int)$_POST['set_id'];
        $this->formEditMultiset($set_id);
        die();
    } //End function

    /**
     * Show option to show/hide average
     *
     * @author Dario Curvino <@dudo>
     * @since 3.1.3
     * @param $option_multiset
     */
    public function hideAverage($option_multiset) {
        ?>

        <div class="yasr-onoffswitch-big">
            <input type="checkbox" name="yasr_multiset_options[show_average]" class="yasr-onoffswitch-checkbox"
                   id="yasr-multiset-options-show-average-switch" <?php if ($option_multiset['show_average'] === 'yes') {
                echo " checked='checked' ";
            } ?> >
            <label class="yasr-onoffswitch-label" for="yasr-multiset-options-show-average-switch">
                <span class="yasr-onoffswitch-inner"></span>
                <span class="yasr-onoffswitch-switch"></span>
            </label>
        </div>

        <?php

    }

    /**
     * Sanitize
     *
     * @author Dario Curvino <@dudo>
     * @since 3.1.3
     * @param $option_multiset
     *
     * @return mixed
     */
    public function sanitize($option_multiset) {
        $option_multiset['show_average'] = YasrSettings::whitelistSettings($option_multiset, 'show_average', 'no', 'yes');

        return $option_multiset;
    }

    /****************************** METHODS THAT RUN ON $_POST FROM HERE *******************************/


    /**
     * Save a new multi set
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    public function saveNewMultiSet() {
        if (!isset($_POST['multi-set-name'])) {
            return;
        }

        $this->checkPermissions('add-multi-set', 'add-nonce-new-multi-set');

        $multi_set_name = $this->validateMandatoryFields();

        if($multi_set_name === false) {
            return;
        }

        $fields_name = array();
        $elements_filled = 0;

        //@todo increase number of element that can be stored
        for ($i = 1; $i <= 9; $i ++) {
            if (isset($_POST["multi-set-name-element-$i"]) && $_POST["multi-set-name-element-$i"] !== '') {
                $fields_name[$i] = $_POST["multi-set-name-element-$i"];

                $length_ok = $this->checkStringLength($fields_name[$i], $i);

                if($length_ok === 'ok') {
                    $elements_filled ++;
                } else {
                    YasrSettings::printNoticeError($length_ok);
                }
            }
        }

        $this->insertMultiset($multi_set_name, $elements_filled, $fields_name);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return false|string
     */
    private function validateMandatoryFields() {
        //If these fields are not empty go ahead
        if ($_POST['multi-set-name'] === ''
            || $_POST['multi-set-name-element-1'] === ''
            || $_POST['multi-set-name-element-2'] === '') {
            YasrSettings::printNoticeError(
                __('Multi Set\'s name and first 2 elements can\'t be empty',
                    'yet-another-stars-rating')
            );
            return false;
        }

        // Check nonce field
        check_admin_referer('add-multi-set', 'add-nonce-new-multi-set');

        $multi_set_name        = ucfirst(strtolower($_POST['multi-set-name']));
        $multi_set_name_exists = $this->multisetNameExists($multi_set_name);

        if($multi_set_name_exists !== false) {
            YasrSettings::printNoticeError($multi_set_name_exists);
            return false;
        }

        //If multi set name is shorter than 3 chars return error
        if (mb_strlen($multi_set_name) < 3) {
            YasrSettings::printNoticeError(__('Multi Set name must be longer than 3 chars', 'yet-another-stars-rating'));
            return false;
        }

        if (mb_strlen($multi_set_name) > 40) {
            YasrSettings::printNoticeError(__('Multi Set name must be shorter than 40 chars', 'yet-another-stars-rating'));
            return false;
        }

        return $multi_set_name;
    }

    /**
     * Save Multi Set data
     *
     * @author Dario Curvino <@dudo>
     *
     * @param string $multi_set_name
     * @param int    $elements_filled
     * @param array  $fields
     *
     * @since  refactor 3.1.7
     * @return void
     */
    private function insertMultiset($multi_set_name, $elements_filled, $fields) {
        $error_message = __('Something goes wrong trying insert set field name. Please report it',
            'yet-another-stars-rating');

        $insert_multi_name_success = $this->saveMultisetName($multi_set_name);

        //If multi set name has been inserted, now we're going to insert elements
        if ($insert_multi_name_success !== false) {
            $insert_set_value = $this->saveMultisetFields($elements_filled, $fields);

            //Everything is ok
            if ($insert_set_value) {
                YasrSettings::printNoticeSuccess(__('Settings Saved', 'yet-another-stars-rating'));
            }
            //If there was an error saving the fields, delete the set name and print error
            else {
                $this->deleteMultisetName($multi_set_name);
                YasrSettings::printNoticeError($error_message);
            }
        }  else {
            YasrSettings::printNoticeError($error_message);
        }
    }

    /**
     * Save Multiset name and return query result
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $set_name
     *
     * @since  3.1.7
     * @return bool|int|\mysqli_result|resource|null
     */
    private function saveMultisetName ($set_name) {
        global $wpdb;

        return $wpdb->replace(
            YASR_MULTI_SET_NAME_TABLE,
            array(
                'set_name' => $set_name
            ),
            array('%s')
        );
    }

    /**
     * Call this when a new multiset is being created
     *
     * @author Dario Curvino <@dudo>
     *
     * @param int   $elements_filled
     * @param array $fields
     *
     * @since  3.1.7
     * @return bool|int|\mysqli_result|resource|null
     */
    private function saveMultisetFields ($elements_filled, $fields) {
        global $wpdb;

        //Here, I'm sure that the last id of YASR_MULTI_SET_NAME_TABLE is the set I'm saving now
        $parent_set_id = $wpdb->get_results(
            "SELECT MAX(set_id) as id
                   FROM " . YASR_MULTI_SET_NAME_TABLE,
            ARRAY_A);

        $parent_set_id      = $parent_set_id[0]['id'];
        $insert_set_value   = false; //avoid undefined

        for ($i = 1; $i <= $elements_filled; $i ++) {
            $insert_set_value = $this->saveField($parent_set_id, $fields[$i], $i);
        }

        return $insert_set_value;
    }

    /**
     * Save the single set field
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $set_id
     * @param $field_name
     * @param $field_id
     *
     * @since  3.1.7
     * @return bool|int|\mysqli_result|resource|null
     */
    private function saveField($set_id, $field_name, $field_id) {
        global $wpdb;

        //since version 2.0.9 id is auto_increment by default, still doing this to compatibility for
        //existing installs where auto_increment didn't work because set_id=1 already exists
        $existing_id = $wpdb->get_results("SELECT MAX(id) as id FROM " . YASR_MULTI_SET_FIELDS_TABLE, ARRAY_A);

        $new_id      =  $existing_id[0]['id']+1;

        //default where, I need to insert the id even if is auto_insert. to keep compatibility with
        ///to keep compatibility with versions INSTALLED before 2.0.9
        $where_array = array(
            'id'            => $new_id,
            'parent_set_id' => $set_id,
            'field_name'    => $field_name,
            'field_id'      => $field_id
        );
        $format_array =  array('%d', '%d', '%s', '%d');

        //do the replacement
        return $wpdb->replace(
            YASR_MULTI_SET_FIELDS_TABLE,
            $where_array,
            $format_array
        );
    }

    /**
     * Return error if multiset with give name already exists
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $multi_set_name
     *
     * @since  3.1.7
     * @return false|string
     */
    private function multisetNameExists($multi_set_name) {
        //Get all multiset names
        $check_name_exists = YasrDB::returnMultiSetNames();

        foreach ($check_name_exists as $set_name) {
            if ($multi_set_name === $set_name->set_name) {
                return __('You already have a set with this name.', 'yet-another-stars-rating');
            }
        }

        return false;
    }


    /**
     * Called in yasr-settings-multiset, is run when $_POST['yasr_edit_multi_set_form'] isset
     *
     * @author Dario Curvino <@dudo>
     * @since  refactor 3.1.7
     * @return void
     */
    public function editMultiset() {
        if (!isset($_POST['yasr_edit_multi_set_form'])) {
            return;
        }

        $this->checkPermissions('edit-multi-set', 'add-nonce-edit-multi-set');

        $set_id                    = (int)$_POST['yasr_edit_multi_set_form'];
        $number_of_stored_elements = (int)$_POST['yasr-edit-form-number-elements'];

        //If is checked to remove all the set, delete set and return
        if($this->editMultisetRemoveSetChecked($set_id) !== false) {
            return;
        }

        for ($i = 0; $i <= 9; $i ++) {
            //find if exists some fields to delete, WITHOUT RETURN if true
            if($this->editMultisetRemoveFieldChecked($i, $set_id) === 'error') {
                return;
            }

            if($this->editMultisetFieldUpdated($i, $number_of_stored_elements, $set_id) === 'error') {
                return;
            }

            if($this->editMultisetNewFieldAdded($i, $number_of_stored_elements, $set_id) === 'error') {
                return;
            }
        } //End for

        YasrSettings::printNoticeSuccess(__('Settings Saved'));

    } //End function

    /**
     * Find if the checkbox yasr-remove-multi-set is checked
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $set_id
     *
     * @since  3.1.7
     * @return bool|string
     */
    private function editMultisetRemoveSetChecked($set_id) {
        $this->checkPermissions('edit-multi-set', 'add-nonce-edit-multi-set');

        //Check if user want to delete entire set
        if (isset($_POST["yasr-remove-multi-set"])) {
            $remove_set = $this->deleteAllMultisetData($set_id);
            if ($remove_set === false) {
                YasrSettings::printNoticeError(
                    __('Something goes wrong trying to delete a Multi Set . Please report it',
                        'yet-another-stars-rating'));
                return 'error';
            }
            return true;
        }

        return false;
    }


    /**
     * Find if a checkbox with prefix remove-element- is checked
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $i
     * @param $set_id
     *
     * @since 3.1.7
     * @return bool|string|void
     */
    private function editMultisetRemoveFieldChecked($i, $set_id) {
        $i = (int)$i;
        $element = 'remove-element-'.$i;

        //If checkbox is not checked, return
        if(!isset($_POST[$element])) {
            return;
        }

        $this->checkPermissions('edit-multi-set', 'add-nonce-edit-multi-set');

        //Then, check if the user want to remove some field
        $field_to_remove = $_POST[$element];
        $field_removed   = $this->deleteMultisetField($set_id, $field_to_remove);

        if ($field_removed === false) {
            YasrSettings::printNoticeError(__("Something goes wrong trying to delete a Multi Set's element. Please report it",
                'yet-another-stars-rating'));

            return 'error';
        }

        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     *
     * @param $i
     * @param $number_of_stored_elements
     * @param $set_id
     *
     * @since  3.1.7
     * @return string|void|false
     */
    private function editMultisetFieldUpdated ($i, $number_of_stored_elements, $set_id) {
        global $wpdb;

        if(!isset($_POST["edit-multi-set-element-$i"]) || $i > $number_of_stored_elements) {
            return;
        }

        $this->checkPermissions('edit-multi-set', 'add-nonce-edit-multi-set');

        //update the stored elements with the new ones
        $field_name = $_POST["edit-multi-set-element-$i"];
        $field_id   = $_POST["db-id-for-element-$i"];

        $length_ok = $this->checkStringLength($field_name, $i);

        if($length_ok !== 'ok') {
            YasrSettings::printNoticeError($length_ok);
            return;
        }

        //Check if field name is changed
        $field_name_in_db = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT field_name FROM "
                . YASR_MULTI_SET_FIELDS_TABLE .
                " WHERE field_id=%d AND parent_set_id=%d",
                $field_id, $set_id));

        $field_name_in_database = null; //avoid undefined
        foreach ($field_name_in_db as $field_in_db) {
            $field_name_in_database = $field_in_db->field_name;
        }

        //if field name in db is different from field name in form update it
        if ($field_name_in_database !== $field_name) {
            $field_updated = $this->updateMultisetField($field_name, $set_id, $field_id);

            if ($field_updated === false) {
                YasrSettings::printNoticeError(__("Something goes wrong trying to update a Multi Set's element. Please report it",
                    'yet-another-stars-rating'));
                return 'error';
            }
        }

        return false;

    }

    /**
     * @author Dario Curvino <@dudo>
     *
     * @param $i
     * @param $number_of_stored_elements
     * @param $set_id
     *
     * @return false|void
     */
    private function editMultisetNewFieldAdded($i, $number_of_stored_elements, $set_id) {
        if(!isset($_POST["edit-multi-set-element-$i"])
            || $_POST["edit-multi-set-element-$i"] === ''
            || $i <= $number_of_stored_elements) {
            return;
        }

        $this->checkPermissions('edit-multi-set', 'add-nonce-edit-multi-set');

        //If $i > number of stored elements, user is adding new elements, so we're going to insert the new ones
        $field_name   = $_POST["edit-multi-set-element-$i"];

        global $wpdb;

        //if elements name is shorter than 3 chars return error.
        //I don't want return error if a user add an empty field here.
        //An empty field will be just ignored
        $length_ok = $this->checkStringLength($field_name, $i, true);

        if($length_ok !== 'ok') {
            YasrSettings::printNoticeError($length_ok);
            return;
        }

        //get the new field id
        $highest_field_id = $wpdb->get_results(
            "SELECT field_id FROM " . YASR_MULTI_SET_FIELDS_TABLE . " 
                            ORDER BY field_id 
                            DESC LIMIT 1",
            ARRAY_A);

        $new_field_id =  $highest_field_id[0]['field_id']+1;

        $insert_set_value = $this->saveField(
            $set_id,
            $field_name,
            $new_field_id
        );

        if ($insert_set_value === false) {
            YasrSettings::printNoticeError(__('Something goes wrong trying to insert set field name in edit form. Please report it',
                'yet-another-stars-rating'));

            return 'error';
        }

        return false;
    }

    /**
     * Here is safe to use set_name, instead of id, because a set name is saved only if doesn't exist another with the
     * same name
     *
     * @author Dario Curvino <@dudo>
     *
     * @param bool $set_name
     * @param bool $set_id
     *
     * @since  3.1.7
     * @return int|false|void
     */
    private function deleteMultisetName($set_name=false, $set_id=false) {
        global $wpdb;

        if($set_name) {
            return $wpdb->delete(
                YASR_MULTI_SET_NAME_TABLE,
                array(
                    'set_name' => $set_name
                ),
                array('%s')
            );
        }

        if($set_id) {
            return $wpdb->delete(
                YASR_MULTI_SET_NAME_TABLE,
                array(
                    'set_id' => $set_id,
                ),
                array('%d')
            );
        }

    }

    /**
     * Remove a specific field from a multiset along with the data
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return int|false
     */
    private function deleteMultisetField($set_id, $field_to_remove) {
        global $wpdb;

        //remove field
        $field_removed = $wpdb->delete(
            YASR_MULTI_SET_FIELDS_TABLE,
            array(
                'parent_set_id' => $set_id,
                'field_id'      => $field_to_remove
            ),
            array('%d', '%d')
        );

        //if field is removed, delete all the data
        if($field_removed !== false) {
            $wpdb->delete(
                YASR_LOG_MULTI_SET,
                array(
                    'set_type' => $set_id,
                    'field_id' => $field_to_remove
                ),
                array('%d', '%d')
            );
        }

        return $field_removed;

    }

    /**
     * Remove *ALL* multiset data
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $set_id
     *
     * @since  3.1.7
     * @return false|int|null
     */
    private function deleteAllMultisetData($set_id) {
        global $wpdb;

        $remove_set_name = $this->deleteMultisetName(false, $set_id);

        //if the set name has been removed, delete all the data
        if($remove_set_name !== false) {
            $wpdb->delete(
                YASR_MULTI_SET_FIELDS_TABLE, array(
                    'parent_set_id' => $set_id,
                ), array('%d')
            );

            $wpdb->delete(
                YASR_LOG_MULTI_SET, array(
                    'set_type' => $set_id,
                ), array('%d')
            );
        }

        return $remove_set_name;
    }

    /**
     * Update a field
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $field_name
     * @param $set_id
     * @param $field_id
     *
     * @since  3.1.7
     * @return bool|int|\mysqli_result|resource|null
     */
    private function updateMultisetField($field_name, $set_id, $field_id) {
        global $wpdb;

        return $wpdb->update(
            YASR_MULTI_SET_FIELDS_TABLE,

            //value to update
            array(
                'field_name' => $field_name,
            ),
            //where
            array(
                'parent_set_id' => $set_id,
                'field_id'      => $field_id
            ),

            array('%s'),
            array('%d', '%d')

        );
    }


    /**
     * Return 'ok' if string is of the correct length, or an error otherwise
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $string
     * @param $i
     * @param bool $empty_allowed
     *
     * @since  3.1.7
     * @return string
     */
    private function checkStringLength($string, $i, $empty_allowed=false) {
        $i = (int)$i;
        $length = mb_strlen($string);

        if($empty_allowed === true) {
            if ($length>1 && $length < 3) {
                return sprintf(
                    __('Field # %d must be at least 3 chars', 'yet-another-stars-rating'),
                    $i
                );
            }
        }

        if ($length < 3) {
            return sprintf(
                __('Field # %d must be at least 3 chars', 'yet-another-stars-rating'),
                $i
            );
        }

        if ($length > 40) {
            return sprintf(
                __('Field # %d must be shorter than 40 chars', 'yet-another-stars-rating'),
                $i
            );
        }

        return 'ok';
    }

    /**
     * Run current user can and check_admin_referer
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $action_name
     * @param $query_arg
     * @param $capability
     *
     * @since  3.1.8
     * @return void
     */
    private function checkPermissions ($action_name, $query_arg, $capability='manage_options') {
        if(!current_user_can($capability)) {
            /** @noinspection ForgottenDebugOutputInspection */
            wp_die('Not Allowed');
        }

        // Check nonce field
        check_admin_referer($action_name, $query_arg);
    }

}