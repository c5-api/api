<?php defined('C5_EXECUTE') or die('Access Denied');

class DashboardApiCoreKeyController extends DashboardBaseController {
	
	public function view($updated = false, $id = false) {
		if($updated) {
			switch ($updated) {
				case 'new':
					$obj = ApiAuthKeyModel::getByAppID($id);
					$succ = t('New Key Generated.')."\n";
					$succ .= t('App ID: %s', $obj->appID)."\n";
					$succ .= t('Public Key: %s', $obj->publicKey)."\n";
					$succ .= t('Private Key: %s', $obj->privateKey)."\n";
					$this->set('success', $succ);
					break;
				case 'deleted':
					$this->set('success', t('Key Successfully Deleted.'));
					break;
				case 'disabled':
					$this->set('success', t('Key Successfully Disabled.'));
					break;
				case 'enabled':
					$this->set('success', t('Key Successfully Enabled.'));
					break;
				case 'invalid_key':
					$this->set('error', t('Invalid Key!'));
					break;
				case 'invalid_token':
					$this->set('error', Loader::helper('validation/token')->getErrorMessage());
					break;
			}
		}
		$list = new ApiAuthKeyList();
		$list = $list->get();
		$this->set('list', $list);
	}

	public function delete($key = false, $token = false) {
		if(!$key || !$token) { //if this happens they someone is trying to hack it so no error message for them.
			$this->redirect('/dashboard/api/core/key');
		}
		$obj = ApiAuthKeyModel::getByAppID($key);
		if(!is_object($obj) || !$obj->appID) {
			$this->redirect('/dashboard/api/core/key', 'invalid_key');
		}
		$valt = Loader::helper('validation/token');
		if(!$valt->validate('delete', $token)) {
			$this->redirect('/dashboard/api/core/key', 'invalid_token');
		}
		$obj->delete();
		$this->redirect('/dashboard/api/core/key', 'deleted');

	}

	public function generate($token = false) {
		$valt = Loader::helper('validation/token');
		if(!$valt->validate('generate', $token)) {
			$this->redirect('/dashboard/api/core/key', 'invalid_token');
		}
		$obj = ApiAuthKeyModel::add();
		$id = $obj->appID;
		$this->redirect('/dashboard/api/core/key', 'new', $id);
	}

	public function disable($key = false, $token = false) {
		if(!$key || !$token) { //if this happens they someone is trying to hack it so no error message for them.
			$this->redirect('/dashboard/api/core/key');
		}
		$obj = ApiAuthKeyModel::getByAppID($key);
		if(!is_object($obj) || !$obj->appID) {
			$this->redirect('/dashboard/api/core/key', 'invalid_key');
		}
		$valt = Loader::helper('validation/token');
		if(!$valt->validate('disable', $token)) {
			$this->redirect('/dashboard/api/core/key', 'invalid_token');
		}
		$db = Loader::db();
		$obj->active = 0;
		$obj->save();
		$this->redirect('/dashboard/api/core/key', 'disabled');

	}

	public function enable($key = false, $token = false) {
		if(!$key || !$token) { //if this happens they someone is trying to hack it so no error message for them.
			$this->redirect('/dashboard/api/core/key');
		}
		$obj = ApiAuthKeyModel::getByAppID($key);
		if(!is_object($obj) || !$obj->appID) {
			$this->redirect('/dashboard/api/core/key', 'invalid_key');
		}
		$valt = Loader::helper('validation/token');
		if(!$valt->validate('enable', $token)) {
			$this->redirect('/dashboard/api/core/key', 'invalid_token');
		}
		$obj->active = 1;
		$obj->save();
		$this->redirect('/dashboard/api/core/key', 'enabled');

	}

}