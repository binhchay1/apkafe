var interval_time = 2500;
var scan_lasso_interval = false;

function print_process_popup() {
    let str = `<div class="modal fade" id="scan-lasso-link-modal" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-config modal-content" style="width: -webkit-fill-available;">
                    <div class="modal-body">
                        <div>
                            <h2 class="title-color progress-title">Updating Lasso Links</h2>
                        </div>
                        <div class="progress progress-bar">
                            <div id="progress-loading" class="progress-bar progress-bar-striped progress-bar-animated active" role="progressbar"
                                aria-valuenow="40" aria-valuemin="0" 
                                aria-valuemax="100" style="width: 0%">
                            </div>
                        </div>
                        <div class="modal-button-container text-center">
                            <a id="link_db_button" class="btn page-title-action modal-button wizard-processing">Processing</a>
                        </div>
                    </div>
                </div>
            </div>
    </div>`;
    jQuery(str).insertAfter('input#laso_id');
}

jQuery(document).ready(function() {
    print_process_popup();
});

