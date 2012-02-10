<?php defined('C5_EXECUTE') or die('Access Denied.');
class DashboardApiCoreController extends DashboardBaseController {

	public function view() {
		$this->redirect('/dashboard/api/core/on_off');
	}
}