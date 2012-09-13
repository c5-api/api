<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiRouter {

	/**
	 * Contains the requested path
	 * e.g. /config/derp?some=param&other=stuff
	 * @var string
	 */
	public $requestedPath;

	/**
	 * Contains the requested route
	 * e.g config/derp
	 * @var string
	 */
	public $requestedRoute;

	/**
	 * Contains the found route
	 * e.g config
	 * @var string
	 */
	public $foundRoute;

	/**
	 * Get an ApiRouter object, if none exists, create one and then parse the current request.
	 * @return ApiRouter
	 */
	public static function get() {
		static $req;
		if (!isset($req)) {
			$req = new ApiRouter();
			$req->parseRequest();
		}
		return $req;
	}

	/**
	 * Parse the current request and set all our vars
	 * @return void;
	 */
	public function parseRequest() {

		$pk = Package::getByHandle(C5_API_HANDLE);
		if(!$pk->config('ENABLED')) {
			return; //if we arn't enabled, kill it. should we render an api response instead?
		}

		if(!defined('API_REQUEST_METHOD')) {
			define('API_REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
		} else {
			$_SERVER['REQUEST_METHOD'] = API_REQUEST_METHOD;
		}

		$req = Request::get();
		$path = $req->getRequestPath();

		$path = trim($path, '/');

		$basepath = trim(BASE_API_PATH, '/');

		if (substr($path, 0, strlen($basepath)) == $basepath) {
			$dirrel = strlen(DIR_REL.'/'.DISPATCHER_FILENAME);
			if(substr($_SERVER['REQUEST_URI'], 0, $dirrel) == DIR_REL.'/'.DISPATCHER_FILENAME) { //pretty url hack
				$path = DIR_REL.'/'.DISPATCHER_FILENAME.'/'.BASE_API_PATH;
			} else {
				$path = DIR_REL.'/'.BASE_API_PATH;
			}

			//This is a path like /derp/thing/ha?params=1
			$this->requestedPath =  trim(str_replace($path, '', $_SERVER['REQUEST_URI']), '/');

			$request = $this->requestedPath;
			if(($pos = strpos($request, '?')) !== false) {
            	$request =  trim(substr($request, 0, $pos), '/');
        	}
        	$this->requestedRoute = $request;
			$this->dispatch();
		}
	}

	/**
	 * Get the route, if it requires auth, then run it, then run the route
	 * @return void
	 */
	public function dispatch() {
		$txt = Loader::helper('text');
		$error = false;
		$route = ApiRouteList::getRouteByPath($this->requestedRoute);
		if(is_object($route) && $route->ID && !$route->internal) { //valid route
			$this->foundRoute = $route->route;
			if($route->auth) {
				$authobj = ApiAuthList::getEnabled();
				$class = $authobj->getClass();
				$auth = $class::authorize();

				if(!$auth) {
					$route = ApiRouteList::getRouteByPath('unauthorized');
					$class = $txt->camelcase($route->route).'ApiRouteController';
					$cl = new $class;
					$cl->setupAndRun();
				}
			}
			$class = $txt->camelcase($route->route).'ApiRouteController';
			try {
				$env = Environment::get();
				$pkg = Package::getByID($route->pkgID);
				$path = $env->getPath(C5_API_DIRNAME_ROUTES.'/'.$route->file, $pkg);
				require_once($path);
				if(class_exists($class)) {
					$cl = new $class;
					$data = $cl->setupAndRun();
					$cl->respond($data);
				} else {
					throw new Exception(t('Invalid Class Name: %s', $class));
				}
			} catch(Exception $e) {
				$error = 500;
			}

		} else {
			$error = 400;
		}

		if($error) {
			switch($error) {
				case 500:
					$route = ApiRouteList::getRouteByPath('server_error');
					$class = $txt->camelcase($route->route).'ApiRouteController';
					$cl = new $class;
					$cl->setupAndRun();
					break;
				default:
					$route = ApiRouteList::getRouteByPath('bad_request');
					$class = $txt->camelcase($route->route).'ApiRouteController';
					$cl = new $class;
					$cl->setupAndRun();
					break;
			}
		}
		
	}

}