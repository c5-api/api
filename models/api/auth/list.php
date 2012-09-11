<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiAuthList {
	
	public static function getList($all = false) {
		$where = '';
		if(!$all) {
			$where = ' where enabled = 1';
		}
		$db = Loader::db();
		$r = $db->Execute('select aID from ApiAuth'.$where);
		$d = array();
		while ($row = $r->FetchRow()) {
			$d[] = ApiAuthModel::getByID($row['aID']);
		}
		return $d;
	}

	public static function getEnabled() {
		$db = Loader::db();
		$ID = $db->getOne('SELECT aID FROM ApiAuth WHERE enabled = 1');
		return ApiAuthModel::getByID($ID);
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
		$r = $db->Execute('SELECT aID FROM ApiAuth where pkgID = ?', array($pkg));
		while ($row = $r->FetchRow()) {
			$list[] = ApiAuthModel::getByID($row['aID']);
		}
		return $list;
	}

	public static function removeByPackage($pkg) {
		$list = self::getListByPackage($pkg);
		foreach($list as $route) {
			$route->delete();
		}
	}

}