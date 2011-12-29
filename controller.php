<?php defined('C5_EXECUTE') or die("Access Denied.");

class ApiPackage extends Package {

	protected $pkgHandle = 'api';
	protected $appVersionRequired = '5.5.0';
	protected $pkgVersion = '1.0';
	
	public function getPackageName() {
		return t("Api");
	}
	
	public function getPackageDescription() {
		return t("Provides an external api.");
	}
	
	public function on_start() {
		if(!defined('BASE_API_PATH')) {
			define('BASE_API_PATH', 'api');
		}
		Loader::model('api_routes', 'api');
		//possibly on_start if we are using a db
		Events::extend('on_before_render', 'ApiRequest', 'parseRequest', DIR_PACKAGES.'/'.$this->pkgHandle.'/'.DIRNAME_MODELS.'/api_routes.php');
	}
	
	public function install() {
		$pkg = parent::install();
	}

}