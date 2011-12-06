<?php defined('C5_EXECUTE') or die("Access Denied.");

class ApiPackage extends Package {

	protected $pkgHandle = 'api';
	protected $appVersionRequired = '5.5.0b1';
	protected $pkgVersion = '1.0';
	
	public function getPackageName() {
		return t("Api");
	}
	
	public function getPackageDescription() {
		return t("Provides an external api.");
	}
	
	public function install() {
		$pkg = parent::install();
	}

}