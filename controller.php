<?php defined('C5_EXECUTE') or die("Access Denied.");

class ApiPackage extends Package {

	protected $pkgHandle = 'api';
	protected $appVersionRequired = '5.5.0';
	protected $pkgVersion = '1.0';

	public function getPackageName() {
		return t("Api");
	}

	public function getPackageDescription() {
		return t("Provides an external API for remote management of your site. Allows integration into 3rd party applications.");
	}

	public function on_start() {
		if(!defined('BASE_API_PATH')) {
			define('BASE_API_PATH', '-/api');
		}
		define('C5_API_HANDLE', 'api');
		Loader::model('api_routes', 'api');
		//possibly on_start if we are using a db
		Events::extend('on_before_render', 'ApiRequest', 'parseRequest', DIR_PACKAGES.'/'.$this->pkgHandle.'/'.DIRNAME_MODELS.'/api_routes.php');
	}

	public function install() {
		$pkg = parent::install();
		$vh = Loader::helper('validation/identifier');
		$key = $vh->getString(24);
		$pkg->saveConfig('key', $key);
		Loader::model('single_page');
		$p = SinglePage::add('/dashboard/api',$pkg);
		$p->update(array('cName'=>t('concrete5 API'), 'cDescription'=>t('Remote management of your site.')));
		$p2 = SinglePage::add('/dashboard/api/manage_routes',$pkg);
		$p2->update(array('cName'=>t('Manage Routes'), 'cDescription'=>t('Managed installed API routes.')));
	}

	public function uninstall() {
		$force = $_POST['force'];
		if($force != t('remove')) {
			$force = false;
		}
		if(!$force) {
			Loader::model('api_register', 'api');
			$pkgs = ApiRegister::getPackageList();
			if(count($pkgs) > 0) {
				throw new Exception(t('Please uninstall all addons that register routes with the API, before uninstalling this addon.'));
			}
		}
		$pkg = parent::uninstall();
	}

}