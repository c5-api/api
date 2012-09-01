<?php defined('C5_EXECUTE') or die('Access Denied');

class DashboardApiCoreAuthController extends DashboardBaseController {
	
	public function view($updated = false) {
		if($updated) {
			switch ($updated) {
				case 'updated':
					$this->set('success', t('Active Auth Updated.'));
					break;
			}
		}
		$list = ApiAuthList::getList(true);
		$this->set('list', $list);
	}

	public function save() {
		$enabled = $this->post('enabled');
		$def = ApiAuthModel::getByHandle($enabled);
		if(!$def->aID) {
			$def = ApiAuthModel::getByHandle(C5_API_DEFAULT_AUTH);
		}
		$def->setEnabled();
		$this->redirect('/dashboard/api/core/auth', 'updated');

	}
}