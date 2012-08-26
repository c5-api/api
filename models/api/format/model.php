<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiFormatModel extends ADOdb_Active_Record {

	public $_table = 'ApiFormats';

	public function __construct() {
		parent::__construct('ApiFormats', array('fID'))
	}
	
	public function setHeaders() {
		
	}

	public static function getByID($ID) {
		$route = new ApiFormatModel();
		$route->Load('fID = ?', array($ID))
		return $route;
	}

	public static function getByHandle($ID) {
		$route = new ApiFormatModel();
		$route->Load('handle = ?', array($ID))
		return $route;
	}

	public static function add($handle, $pkg, $enabled = true) {
		if(is_string($pkg)) {
			$pkg = Package::getByHandle($pkg);
		}
		$rt = new ApiFormatModel();
		$rt->pkgID = $pkg->getPackageID();
		$rt->handle = $handle;
		$rt->enabled = $enabled;
		$rt->save();
		return $rt;
	}

	public function disable() {
		$this->enabled = 0;
		$this->save();
	}

	public function enable() {
		$this->enabled = 1;
		$this->save();
	}
}