<?php defined('C5_EXECUTE') or die('Access Denied.');
class DashboardApiSettingsCoreController extends DashboardBaseController {

	public function view() {
		$this->redirect('/dashboard/api/settings/core/on_off');
	}
}