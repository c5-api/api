<?php defined('C5_EXECUTE') or die('Access Denied.');
class DashboardApiController extends DashboardBaseController {

	public function view() {
		$this->redirect('/dashboard/api/manage_routes');
	}
}