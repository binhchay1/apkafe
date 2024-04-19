<?php

if ( !class_exists('Lasso_Puc_v4p4_DebugBar_ThemePanel', false) ):

	class Lasso_Puc_v4p4_DebugBar_ThemePanel extends Lasso_Puc_v4p4_DebugBar_Panel {
		/**
		 * @var Lasso_Puc_v4p4_Theme_UpdateChecker
		 */
		protected $updateChecker;

		protected function displayConfigHeader() {
			$this->row('Theme directory', htmlentities($this->updateChecker->directoryName));
			parent::displayConfigHeader();
		}

		protected function getUpdateFields() {
			return array_merge(parent::getUpdateFields(), array('details_url'));
		}
	}

endif;