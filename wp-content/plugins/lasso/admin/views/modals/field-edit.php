<?php
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
?>

<!-- FIELD EDIT -->
<div class="modal fade" id="field-edit" tabindex="-1" role="dialog">
    <input id="total-posts" class="d-none" value="0" />
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow p-4 rounded">

            <!-- CHOOSE A FIELD TYPE -->
            <div id="lasso-field-type">
                <h3 class="mb-1">Edit Field</h3>

                <div id="edit-a-field">
                    <form action="#" method="get" id="field-form" autocomplete="off">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="form-group mb-4">
                                    <label data-tooltip="Ideal title for use in things like a comparison table header."><strong>Field Title</strong> <i class="far fa-info-circle light-purple"></i></label>
                                    <input id="field-title" type="text" class="form-control" placeholder="Size, Weight, Year, etc...">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group mb-4">
                                    <label data-tooltip="Ideal title for use in things like a comparison table header."><strong>Field Type</strong> <i class="far fa-info-circle light-purple"></i></label>
                                    <?php echo Lasso_Html_Helper::render_field_types(); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center">   
                            <div class="col">
                                <div class="form-group mb-4">
                                    <label data-tooltip="Describe how you use this field."><strong>Description</strong> <i class="far fa-info-circle light-purple"></i></label>
                                    <textarea id="field-description" class="form-control" placeholder="Describe how you use this field." rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                                
                        <div class="row align-items-center">   
                            <div class="col">
                                <div class="form-group mb-1 text-right">
                                    <button type="button" class="btn btn-md green-bg js-create-field">
                                        Update Field
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
    $page = $_GET['page'] ?? '';
    $template_variables = array( 'page' => $page );
    echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/assets/js/view-js.php', $template_variables );
?>