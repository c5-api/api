<?php defined('C5_EXECUTE') or die('Access Denied.');
class DashboardApiSettingsCoreOnOffController extends DashboardBaseController {

	public function view() {
		$this->set('enable', Package::getByHandle('api')->config('ENABLED'));
	}
	
	public function save_api_enable() {
		if($this->token->validate('save_api_enable')){
			$pkg = Package::getByHandle('api');
			$pkg->saveConfig('ENABLED', $this->post('api_enable'));
		} else { 
			$this->set('error', array($this->token->getErrorMessage()));
		}
	}
}