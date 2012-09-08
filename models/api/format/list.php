<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiFormatList {

	public static function getList($all = false) {
		$where = '';
		if(!$all) {
			$where = ' where enabled = 1';
		}
		$db = Loader::db();
		$r = $db->Execute('select fID from ApiFormats'.$where);
		$d = array();
		while ($row = $r->FetchRow()) {
			$d[] = ApiFormatModel::getByID($row['fID']);
		}
		return $d;
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
		$r = $db->Execute('SELECT fID FROM ApiFormats where pkgID = ?', array($pkg));
		while ($row = $r->FetchRow()) {
			$list[] = ApiFormatModel::getByID($row['fID']);
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