<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiFormatModel extends ADOdb_Active_Record {

	public $_table = 'ApiFormats';

	public function __construct() {
		parent::__construct('ApiFormats', array('fID'));
	}
	
	public function setHeaders() {
		
	}

	public static function getByID($ID) {
		$route = new ApiFormatModel();
		$route->Load('fID = ?', array($ID));
		return $route;
	}

	public static function getByHandle($ID) {
		$route = new ApiFormatModel();
		$route->Load('handle = ?', array($ID));
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
		$rt->isDefault = 0;
		$rt->save();
		return $rt;
	}

	public function setDefault() {
		$db = Loader::db();
		$db->Execute('UPDATE ApiFormats SET isDefault = 0');
		$db->Execute('UPDATE ApiFormats SET isDefault = 1 WHERE fID = ?', array($this->fID));
	}

	public function disable() {
		$this->enabled = 0;
		$this->save();
	}

	public function enable() {
		$this->enabled = 1;
		$this->save();
	}

	public static function getEnabled() {
		$db = Loader::db();
		$r = $db->Execute('SELECT fID FROM ApiFormats WHERE enabled = 1');
		$ar = array();
		while($row = $r->fetchRow()) {
			$ar[] = self::getByID($row['fID']);
		}
		return $ar;
	}

	public static function getHandles() {
		$db = Loader::db();
		$r = $db->Execute('SELECT handle FROM ApiFormats WHERE enabled = 1');
		$ar = array();
		while($row = $r->fetchRow()) {
			$ar[] = $row['handle'];
		}
		return $ar;
	}

	public static function getDefault() {
		$db = Loader::db();
		$row = $db->getOne('SELECT fID FROM ApiFormats WHERE isDefault = 1');
		return self::getByID($row);
	}

	public function getClass() {
		$txt = Loader::helper('text');
		$class = $txt->camelcase($this->handle).'ApiFormat';
		return new $class;
	}
}