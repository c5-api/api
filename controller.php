<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * concrete5 API
 * This is the basic package class extended into the api package
 *
 * @category Api
 * @package  ApiCore
 * @author   Michael Krasnow <mnkras@gmail.com>
 * @author   Lucas Anderson <lucas@lucasanderson.com>
 * @copyright 2011-2012 Michael Krasnow and Lucas Anderson
 * @license  See License.txt
 * @link     http://c5api.com
 */
class ApiPackage extends Package {

	/**
	 * @var string The handle of the package
	 */
	protected $pkgHandle = 'api';

	/**
	 * @var string Minimum version of concrete5 required
	 */
	protected $appVersionRequired = '5.5.0';

	/**
	 * @var string Version of the package
	 */
	protected $pkgVersion = '1.0';

    /**
     * Returns the package name
     *
     * @return string
     */
	public function getPackageName() {
		return t("Api");
	}

    /**
     * Returns the package description
     *
     * @return string
     */
	public function getPackageDescription() {
		return t("Provides an external API for remote management of your site. Allows integration into 3rd party applications.");
	}

    /**
     * Fired by concrete5
     *
     * The constant "BASE_API_PATH" is defined here and is by default set to "-/api".
     * We then use events and call the parseRequest method of the ApiRequest model
     *
     * @return void
     */
	public function on_start() {
		if(!defined('BASE_API_PATH')) {
			define('BASE_API_PATH', '-/api');
		}
		define('C5_API_HANDLE', 'api');
		Loader::model('api_routes', 'api');
		//possibly on_start if we are using a db
		Events::extend('on_before_render', 'ApiRequest', 'parseRequest', DIR_PACKAGES.'/'.$this->pkgHandle.'/'.DIRNAME_MODELS.'/api_routes.php');
	}

    /**
     * Called by concrete5 to install the package
     *
     * We generate the auth key (24 chars long).
     * We add all dashboard singlepages
     *
     * @return void
     */
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
		$p3 = SinglePage::add('/dashboard/api/settings',$pkg);
		$p3->update(array('cName'=>t('Settings')));
		$p4 = SinglePage::add('/dashboard/api/settings/core/',$pkg);
		$p4->update(array('cName'=>t('Core API')));
		$p5 = SinglePage::add('/dashboard/api/settings/core/on_off',$pkg);
		$p5->update(array('cName'=>t('Enable & Disable the API')));
		$pkg->saveConfig('ENABLED', 1);
		
	}

    /**
     * Called by concrete5 to uninstall the package
     *
     * We check if any other packages are installed that are using the api, if not uninstall.
     *
     * @return void
     */
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
		$db = Loader::db();
		$db_sql = 'DROP TABLE IF EXISTS ApiRouteRegistry';
		$db->Execute($db_sql);
		$pkg = parent::uninstall();
	}

}