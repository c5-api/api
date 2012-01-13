<?php defined('C5_EXECUTE') or die('Access Denied.');
Loader::model('api_register', C5_API_HANDLE);
class DashboardApiSettingsCoreRefreshController extends DashboardBaseController {

	public function view($message = null) {
		$this->addFooterItem('<script type="text/javascript">$(".disabled").twipsy();</script>');
		if($message) {
			switch($message) {
				case 'success':
					$this->set('message', t('Routes Successfully Refreshed.'));
					break;
				case 'token':
					$this->error->add($this->token->getErrorMessage());
					break;
				case 'error':
					$this->error->add(t('An Unknown Error Occured.'));
					break;
			}
		}
		$this->set('pkgs', ApiRegister::getPackageList());
	}
	
	public function ref($handle = '', $token = '') {
		if($this->token->validate('ref_routes', $token)) {
			if(ApiRegister::refreshRoutes($handle)) {
				$this->redirect('dashboard/api/settings/core/refresh','success');
				return;
			}
			$this->redirect('dashboard/api/settings/core/refresh','error');
		} else {
			$this->redirect('dashboard/api/settings/core/refresh','token');
		}
	}
}