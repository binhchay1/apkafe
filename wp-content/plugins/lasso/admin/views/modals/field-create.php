<?php
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;

?>
<!-- FIELD ADD -->
<div class="modal fade" id="field-create" tabindex="-1" role="dialog">
    <input id="total-posts" class="d-none" value="0" />
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow p-4 rounded">

            <!-- CHOOSE A FIELD TYPE -->
            <div id="lasso-field-type">
                <div class="row">
                    <div class="col-lg">
                        <h3 class="mb-1">Add Field</h3>
                    </div>
                    <div class="col-sm text-right">
                        <a class="nav-link purple px-0" id="manage_fields" href="/wp-admin/edit.php?post_type=lasso-urls&page=fields" target="_blank">Manage Fields <i class="far fa-external-link"></i></a>
                    </div>
                </div>
                <ul class="nav nav-tabs font-weight-bold mb-4" id="myTab" role="tablist">
                    <li class="nav-item mr-3">
                        <a class="nav-link purple hover-underline px-0 active border-0" id="create_new_tab" data-toggle="tab" href="#create-new-field" role="tab" aria-controls="create-new-field" aria-selected="true">Create new</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link purple hover-underline px-0 ml-3 border-0" id="create_from_library_tab" data-toggle="tab" href="#field-from-library" role="tab" aria-controls="field-from-library" aria-selected="false">Add from library</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="create-new-field" role="tabpanel" aria-labelledby="create-new-field-tab">
                        <form action="#" method="get" id="field-form" autocomplete="off">
                            <div class="row align-items-center">
                                <div class="col-7">
                                    <div class="form-group mb-4">
                                        <label data-tooltip="Ideal title for use in things like a comparison table header."><strong>Field Title</strong> <i class="far fa-info-circle light-purple"></i></label>
                                        <input id="field-title" type="text" class="form-control" placeholder="Size, Weight, Year, etc...">
                                    </div>
                                </div>
                                <div class="col-5">
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
                                            Create Field
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="field-from-library" role="tabpanel" aria-labelledby="field-from-library-tab">
                        <form action="#" method="get" id="search-links" autocomplete="off">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="form-group mb-3">
                                        <input type="text" class="form-control" placeholder="Find a field by searching here">
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <!-- FIELDS TABLE -->
                        <div class="white-bg">
                            <div id="report-content"></div>
                            <div id="field-from-library-loading"></div>
                            <!-- <div class="ls-loader"></div> -->
                        </div>     
                
                        <!-- PAGINATION -->
                        <div class="pagination row align-items-center no-gutters pb-1 pt-4"></div>
                    </div>                    
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