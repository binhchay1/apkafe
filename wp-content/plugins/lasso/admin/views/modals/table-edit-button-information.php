<!-- EDIT TABLE BUTTON FIELD  -->
<div class="modal fade" id="table-edit-button-field-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow p-4 rounded">
			<form action="#" method="get" id="table-edit-button-field-form" autocomplete="off">
				<input type="hidden" name="table_edit_field_group_detail_id" />
				<h4 id="modal-title" class="text-center mb-4"></h4>
				<div class="form-group mb-4">
					<label for="table-edit-button-text"><strong>Button Text</strong></label>
					<input id="table-edit-button-text" name="table_edit_btn_text" type="text" class="form-control" required />
				</div>
				<div class="form-group mb-4">
					<label for="table-edit-button-url"><strong>Button URL</strong></label>
					<input id="table-edit-button-url" name="table_edit_btn_url" type="text" class="form-control" required disabled />
				</div>

				<p id="table-edit-button-error" class="text-danger my-3 text-center"></p>

				<div class="text-center">
					<button type="button" class="btn cancel-btn mx-1" data-dismiss="modal">Cancel</button>
					<button class="btn save-tb">Save</button>
				</div>
			</form>
        </div>
    </div>
</div>
