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
	protected $appVersionRequired = '5.6.0';

	/**
	 * @var string Version of the package
	 */
	protected $pkgVersion = '0.9';

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
			define('BASE_API_PATH', '-/api'); // -/api
		}
		if(!defined('C5_API_DEBUG')) {
			Config::getandDefine('C5_API_DEBUG', true);
		}

		define('C5_API_DIRNAME_AUTH', 'api/auth');
		define('C5_API_DIRNAME_FORMATS', 'api/formats');
		define('C5_API_DIRNAME_ROUTES', 'api/routes');

		define('C5_API_DEFAULT_FORMAT', 'json');

		define('C5_API_DEFAULT_KEY_LENGTH', 40);
		define('C5_API_KEY_TIMEOUT', 120);//in seconds

		define('C5_API_FILENAME_ROUTES_CONTROLLER', 'controller.php');
		
		define('C5_API_HANDLE', 'api');

		self::registerAutoload();

		Events::extend('on_start', 'ApiRouter', 'get', DIR_PACKAGES.'/'.$this->pkgHandle.'/'.DIRNAME_MODELS.'/api/router.php');
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
		$this->on_start();
		$pkg = parent::install();
		
		Loader::model('single_page');
		
		$p = SinglePage::add('/dashboard/api',$pkg);
		$p->update(array('cName'=>t('concrete5 API'), 'cDescription'=>t('Remote management of your site.')));
		$p1 = SinglePage::add('/dashboard/api/core/',$pkg);
		$p1->update(array('cName'=>t('Core API')));
		$p2 = SinglePage::add('/dashboard/api/core/manage_routes',$pkg);
		$p2->update(array('cName'=>t('Manage Routes'), 'cDescription'=>t('Managed installed API routes.')));
		$p3 = SinglePage::add('/dashboard/api/core/on_off',$pkg);
		$p3->update(array('cName'=>t('Enable & Disable the API')));
		$p4 = SinglePage::add('/dashboard/api/core/formats',$pkg);
		$p4->update(array('cName'=>t('Enable & Disable the API Formats')));
		$p5 = SinglePage::add('/dashboard/api/core/key',$pkg);
		$p5->update(array('cName'=>t('Api Keys')));
		
		$pkg->saveConfig('ENABLED', 1);

		$this->installRoutes();
		$this->installFormats();
		$this->installAuth();
		
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
			/*Loader::model('api_register', 'api');
			$pkgs = ApiRegister::getPackageList();
			if(count($pkgs) > 0) {
				throw new Exception(t('Please uninstall all addons that register routes with the API, before uninstalling this addon.'));
			}*/
		}
		$db = Loader::db();
		$sql1 = 'DROP TABLE IF EXISTS ApiRouteRegistry';
		$sql2 = 'DROP TABLE IF EXISTS ApiFormats';
		$sql3 = 'DROP TABLE IF EXISTS ApiAuth';
		$db->Execute($sql1);
		$db->Execute($sql2);
		$db->Execute($sql3);
		$pkg = parent::uninstall();
	}

	public function installRoutes() {
		$pkg = Package::getByHandle(C5_API_HANDLE);
		ApiRoute::add('bad_request', t('Bad Request'), $pkg, true, false, true);
		ApiRoute::add('forbidden', t('Forbidden'), $pkg, true, false, true);
		ApiRoute::add('server_error', t('Server Error'), $pkg, true, false, true);
	}

	public function installFormats() {
		$pkg = Package::getByHandle(C5_API_HANDLE);
		ApiFormatModel::add('json', $pkg, true, true)->setDefault();
	}

	public function installAuth() {
		$pkg = Package::getByHandle(C5_API_HANDLE);
		ApiAuthModel::add('key', t('Key'), $pkg);
	}

	public static function registerAutoload() {
		$classes = array();
		$classes['ApiRouter'] = array('model', 'api/router', C5_API_HANDLE);
		$classes['ApiRoute,ApiRouteList'] = array('model', 'api/route', C5_API_HANDLE);
		$classes['ApiRouteController'] = array('model', 'api/controller', C5_API_HANDLE);
		$classes['ApiResponse'] = array('model', 'api/response', C5_API_HANDLE);
		$classes['ApiFormatModel'] = array('model', 'api/format/model', C5_API_HANDLE);
		$classes['ApiFormatList'] = array('model', 'api/format/list', C5_API_HANDLE);
		$classes['ApiAuthModel'] = array('model', 'api/auth/model', C5_API_HANDLE);
		$classes['ApiAuthKeyModel,ApiAuthKeyList'] = array('model', 'api/auth/key', C5_API_HANDLE);

		$classes['ApiAuthKey'] = array('apiAuth', 'key', C5_API_HANDLE);

		$classes['JsonApiFormat'] = array('apiFormat', 'json', C5_API_HANDLE);

		$classes['BadRequestApiRouteController'] = array('apiRoute', 'bad_request');
		$classes['ForbiddenApiRouteController'] = array('apiRoute', 'forbidden');
		$classes['ServerErrorApiRouteController'] = array('apiRoute', 'server_error');

		ApiLoader::registerAutoload($classes);
		spl_autoload_register(array('ApiLoader', 'autoload'));

	}

}

class ApiLoader extends Loader {

	static $ApiClasses = array();

	public static function autoload($class) {
		$classes = self::$ApiClasses;
		$cl = $classes[$class];
		if ($cl) {
			if(is_callable(array('ApiLoader', $cl[0]))) {
				call_user_func_array(array('ApiLoader', $cl[0]), array($cl[1], $cl[2]));
			}
		} else {
			parent::autoload($class);
		}
	}

	public static function registerAutoload($classes) {
		foreach($classes as $class => $data) {	
			if (strpos($class, ',') > -1) {
				$subclasses = explode(',', $class);
				foreach($subclasses as $subclass) {
					self::$ApiClasses[$subclass] = $data;
				}
			} else {
				self::$ApiClasses[$class] = $data;
			}
		}				
	}

	public static function apiAuth($path, $pkg) {
		$env = Environment::get();
		require_once($env->getPath(C5_API_DIRNAME_AUTH . '/' . $path . '.php', $pkg));
	}

	public static function apiFormat($path, $pkg) {
		$env = Environment::get();
		require_once($env->getPath(C5_API_DIRNAME_FORMATS . '/' . $path . '.php', $pkg));
	}

	public static function apiRoute($route) {
		if(!is_object($route)) {
			$routee = ApiRouteList::getRouteByPath($route);
			if(!$routee) {
				throw new Exception(t('Invalid route: %s', $route));
			}
			$route = $routee;
		}
		$env = Environment::get();
		require_once($env->getPath(C5_API_DIRNAME_ROUTES.'/'.$route->file, Package::getByID($route->pkgID)));
	}
}