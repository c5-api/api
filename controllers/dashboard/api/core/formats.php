<?php defined('C5_EXECUTE') or die('Access Denied');

class DashboardApiCoreFormatsController extends DashboardBaseController {
	
	public function view($updated = false) {
		if($updated) {
			switch ($updated) {
				case 'updated':
					$this->set('message', t('Settings Updated.'));
					break;
				case 'none_enabled':
					$this->set('error', t('Atleast one format must be enabled.'));
					break;
				case 'default_disabled':
					$this->set('error', t('The Default Format must be enabled.'));
					break;
			}
		}
		echo $updated;
		$list = ApiFormatList::getList(true);
		$this->set('list', $list);
	}

	public function save() {
		$enabled = $this->post('enabled');
		if(!count($enabled)) {
			$this->redirect('/dashboard/api/core/formats', 'none_enabled');
		}
		$default = $this->post('default');
		if(!in_array($default, $enabled)) {
			$this->redirect('/dashboard/api/core/formats', 'default_disabled');
		}
		foreach($enabled as $handle) {
			$obj = ApiFormatModel::getByHandle($handle);
			if($obj->fID) {
				$obj->enable();
			}
		}
		$def = ApiFormatModel::getByHandle($default);
		if(!$def->fID) {
			$def = ApiFormatModel::getByHandle(C5_API_DEFAULT_FORMAT);
		}
		$def->setDefault();
		$this->redirect('/dashboard/api/core/formats', 'updated');

	}
}