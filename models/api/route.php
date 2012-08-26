<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiRoute extends ADOdb_Active_Record {

	public $_table = 'ApiRouteRegistry';

	public function __construct() {
		parent::__construct('ApiRouteRegistry', array('ID'))
	}

	public static function getByID($ID) {
		$route = new ApiRoute();
		$route->Load('ID = ?', array($ID))
		return $route;
	}

	public static function add($route, $name, $pkg, $enabled = true, $auth = true, $internal = false) {
		if(is_string($pkg)) {
			$pkg = Package::getByHandle($pkg);
		}
		$rt = new ApiRoute();
		$rt->pkgID = $pkg->getPackageID();
		if(self::routeExists($route)) {
			return false;
		}
		$rt->route = $route;
		$file = self::getApiPath($route);
		if(!$file) {
			return false;
		}
		$rt->file = $file;
		$rt->name = $name;
		$rt->auth = $auth;
		$rt->enabled = $enabled;
		$rt->internal = $internal;
		$rt->save();
		return $rt;
	}

	public function delete() {
		parent::delete();
	}

	private static function routeExists($route) {
		$route = new ApiRoute();
		$route->load('route = ?', array($route));
		if($route) {
			return true;
		}
	}

	private static function getApiPath($route) {
		$pkgHandle = Package::getByID($route->pkgID)->getPackageHandle();
		$env = Environment::get();
		$path = $env->getPath(C5_API_DIRNAME_ROUTES.'/'.$route->route, $pkgHandle); ///derp/thing.php
		$path2 = $env->getPath(C5_API_DIRNAME_ROUTES.'/'.$route->route.'/'.C5_API_FILENAME_ROUTES_CONTROLLER, $pkgHandle);///derp/thing/controller.php
		if (file_exists($path)) {
			return $path;
		} else if(file_exists($path2)) {
			return $path2;
		} else {
			return false;
		}
	}
}

class ApiRouteList {

	public static function getList() {
		$list = array();
		$db = Loader::db();
		$r = $db->Execute('SELECT ID FROM ApiRouteRegistry where internal = 0');
		while ($row = $r->FetchRow()) {
			$list[] = ApiRoute::getByID($row['ID']);
		}
		return $list;
	}

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
		$r = $db->Execute('SELECT ID FROM ApiRouteRegistry where pkgID = ?', array($pkg));
		while ($row = $r->FetchRow()) {
			$list[] = ApiRoute::getByID($row['ID']);
		}
		return $list;
	}

	public static function getRouteByPath($path) {
		$db = Loader::db();
		$ID = false;
		$rt = false;
		while ((!$ID) && $path) {
			$ID = $db->GetOne('SELECT ID from ApiRouteRegistry where route = ?', $path);
			if($ID) {
				$rt = $path;
				break;
			}
			$path = substr($path, 0, strrpos($path, '/'));
		}
		if($rt) {
			return ApiRoute::geyByID($rt);
		}
		return false;//not found
	}
}