<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiRoute extends ADOdb_Active_Record {

	public $_table = 'ApiRouteRegistry';

	public function __construct() {
		$db = Loader::db();
		parent::__construct('ApiRouteRegistry', array('ID'));
	}

	public static function getByID($ID) {
		$route = new ApiRoute();
		$route->Load('ID = ?', array($ID));
		return $route;
	}

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
		$rt->save();
		return $rt;
	}

	public function delete() {
		parent::delete();
	}

	private static function routeExists($route) {
		$rt = new ApiRoute();
		$rt->load('route = ?', array($route));
		if($rt->ID) {
			return true;
		}
	}

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
		$r = $db->Execute('SELECT ID FROM ApiRouteRegistry where pkgID = ? and internal = 0', array($pkg));
		while ($row = $r->FetchRow()) {
			$list[] = ApiRoute::getByID($row['ID']);
		}
		return $list;
	}

	public static function removeByPackage($pkg) {
		$list = self::getListByPackage($pkg);
		foreach($list as $route) {
			$route->delete();
		}
	}

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