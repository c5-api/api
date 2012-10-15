<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiRoute extends ADOdb_Active_Record {

	public $_table = 'ApiRouteRegistry';

	public function __construct() {
		$db = Loader::db();
		parent::__construct('ApiRouteRegistry');
	}

	/**
	 * Get an ApiRoute by ID
	 * @param int $ID
	 * @return ApiRoute
	 */
	public static function getByID($ID) {
		$route = new ApiRoute();
		$route->Load('ID = ?', array($ID));
		return $route;
	}

	/**
	 * Add a new Route
	 * @param string $route The path of the route to be added e.g. pages/list
	 * @param string $name Name of the route
	 * @param mixed $pkg Package handle or object
	 * @param bool $enabled is the route enabled when added?
	 * @param bool $auth Does the route require authentication?
	 * @param bool $internal is the route internal?
	 * @return bool|ApiRoute
	 */
	public static function add($route, $name, $pkg, $enabled = true, $auth = true, $internal = false) {
		$route = trim($route, '/');
		if(is_string($pkg)) {
			$pkg = Package::getByHandle($pkg);
		}
		$rt = new ApiRoute();
		$rt->pkgID = $pkg->getPackageID();
		if(self::routeExists($route)) {
			return false;
		}
		$rt->route = $route;
		$file = self::getApiPath($route, $pkg);
		if(!$file) {
			return false;
		}
		$rt->file = $file;
		$rt->name = $name;
		$rt->auth = $auth;
		$rt->enabled = $enabled;
		$rt->internal = $internal;
		$ret = Events::fire('on_before_api_route_add', $rt);
		if($ret == '-1') {
			return false;
		}
		$rt->save();
		Events::fire('on_api_route_add', $rt);
		return $rt;
	}

	/**
	 * Delete a route
	 */
	public function delete() {
		$ret = Events::fire('on_before_api_route_delete', $this);
		if($ret == '-1') {
			return false;
		}
		parent::delete();
	}

	/**
	 * Check if a route exists
	 * @param string $route
	 * @return bool
	 */
	private static function routeExists($route) {
		$rt = new ApiRoute();
		$rt->load('route = ?', array($route));
		if($rt->ID) {
			return true;
		}
		return false;
	}

	/**
	 * Gets the path to a route in the filesystem
	 * @param string $route
	 * @param Package $pkg
	 * @return string|bool
	 */
	private static function getApiPath($route, $pkg) {
		$pkgHandle = $pkg->getPackageHandle();
		$env = Environment::get();
		$p1 = $route.'.php';
		$p2 = $route.'/'.C5_API_FILENAME_ROUTES_CONTROLLER;
		$path1 = $env->getPath(C5_API_DIRNAME_ROUTES.'/'.$p1, $pkgHandle); ///derp/thing.php
		$path2 = $env->getPath(C5_API_DIRNAME_ROUTES.'/'.$p2, $pkgHandle);///derp/thing/controller.php
		if (file_exists($path1)) {
			return $p1;
		} else if(file_exists($path2)) {
			return $p2;
		} else {
			return false;
		}
	}
}

class ApiRouteList {

	/**
	 * Get a list of all routes that are not internal as an array of ApiRoute objects
	 * @return array $list
	 */
	public static function getList() {
		$list = array();
		$db = Loader::db();
		$r = $db->Execute('SELECT ID FROM ApiRouteRegistry where internal = 0');
		while ($row = $r->FetchRow()) {
			$list[] = ApiRoute::getByID($row['ID']);
		}
		return $list;
	}

	/**
	 * Get an array of ApiRoutes by package
	 * @param mixed $pkg
	 * @return array $list
	 */
	public static function getListByPackage($pkg) {
		if(is_string($pkg)) {
			$pkg = Package::getByHandle($pkg);
		} else if (is_int($pkg)) {
			$pkg = Package::getByID($pkg);
		}
		if(is_object($pkg)) {
			$pkg = $pkg->getPackageID();
		} else {
			return array();
		}
		$list = array();
		$db = Loader::db();
		$r = $db->Execute('SELECT ID FROM ApiRouteRegistry where pkgID = ? and internal = 0', array($pkg));
		while ($row = $r->FetchRow()) {
			$list[] = ApiRoute::getByID($row['ID']);
		}
		return $list;
	}

	/**
	 * Remove routes by package
	 * @param mixed $pkg
	 * @return void
	 */
	public static function removeByPackage($pkg) {
		$list = self::getListByPackage($pkg);
		foreach($list as $route) {
			$route->delete();
		}
	}

	/**
	 * Get a route by path, if there is no route at /config/lol/haha, then it tries /config/lol, then /config
	 * @param string $path
	 * @return bool|ApiRoute
	 */
	public static function getRouteByPath($path) {
		$db = Loader::db();
		$ID = false;
		$rt = false;
		while ((!$ID) && $path) {
			$ID = $db->GetOne('SELECT ID from ApiRouteRegistry where route = ?', $path);
			if($ID) {
				$rt = $ID;
				break;
			}
			$path = substr($path, 0, strrpos($path, '/'));
		}
		if($rt) {
			return ApiRoute::getByID($rt);
		}
		return false;//not found
	}

	/**
	 * Get a list of all packages that have routes
	 * @return array $ar
	 */
	public static function getPackagesList() {
		$db = Loader::db();
		$pk = Package::getByHandle(C5_API_HANDLE);
		$r = $db->Execute('SELECT distinct pkgID FROM ApiRouteRegistry WHERE pkgID != ?', array($pk->getPackageID()));
		$ar = array();
		while ($row = $r->fetchRow()) {
			$ar[] = Package::getByID($row['pkgID']);
		}
		return $ar;
	}
}