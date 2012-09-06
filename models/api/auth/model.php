<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiAuthModel extends ADOdb_Active_Record {

	public $_table = 'ApiAuth';

	public function __construct() {
		$db = Loader::db();
		parent::__construct('ApiAuth');
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

	public static function add($handle, $name, $pkg, $enabled = false) {
		if(is_string($pkg)) {
			$pkg = Package::getByHandle($pkg);
		}
		$rt = new ApiAuthModel();
		$rt->pkgID = $pkg->getPackageID();
		$rt->handle = $handle;
		$rt->name = $name;
		$rt->enabled = $enabled;
		$rt->save();
		return $rt;
	}
	
	public static function authorize() {
		return true;
	}

	public function setEnabled() {
		$db = Loader::db();
		$db->Execute('UPDATE ApiAuth SET enabled = 0');
		$db->Execute('UPDATE ApiAuth SET enabled = 1 WHERE aID = ?', array($this->aID));
	}

	public function getClass() {
		$txt = Loader::helper('text');
		$class = $txt->camelcase($this->handle).'ApiAuth';
		if(!class_exists($class)) {
			ApiLoader::apiAuth($this->handle, Package::getByID($this->pkgID));
		}
		return $class;
	}

}