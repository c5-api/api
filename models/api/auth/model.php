<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiAuthModel extends ADOdb_Active_Record {

	public $_table = 'ApiAuth';

	public function __construct() {
		parent::__construct('ApiAuth', array('aID'));
	}

	public static function getByID($ID) {
		$route = new ApiAuthModel();
		$route->Load('aID = ?', array($ID));
		return $route;
	}

	public static function getByHandle($ID) {
		$route = new ApiAuthModel();
		$route->Load('handle = ?', array($ID));
		return $route;
	}

	public static function add($handle, $pkg, $enabled = true) {
		if(is_string($pkg)) {
			$pkg = Package::getByHandle($pkg);
		}
		$rt = new ApiAuthModel();
		$rt->pkgID = $pkg->getPackageID();
		$rt->handle = $handle;
		$rt->enabled = $enabled;
		$rt->save();
		return $rt;
	}
	
	public function authorize() {
		return true;
	}

}