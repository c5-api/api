<?php  defined('C5_EXECUTE') or die('Access Denied');

class ApiRegister extends Object {

	public static function add(array $api) {
		
		$api['route'] = trim($api['route'], '/ ');
		if($api['route'] && !self::apiRouteExists($api['route'])) {
			if(!isset($api['enabled']) || $api['enabled']) { //if it is not set, or it is set and is enabled set it to true (prevents db errors)
				$api['enabled'] = true;
			} else {
				$api['enabled'] = false;
			}
			if(!$api['pkgHandle']) {
				$api['pkgHandle'] = 'api';//possibly change this to _error,
			}
			if(!$api['routeName']) {
				$api['routeName'] = t('Unkown Route Name'); //we could make the name into a handle and do reverse routing...?
			}
			if(!$api['isResource']) {
				$api['only'] = array();
				$api['isResource'] = false;
			} else {
				if(!is_array($api['only'])) { //we are only accepting "only" in the future possibly "except"
					$api['only'] = array();
				}
				$api['method'] = null;
				$api['class'] = null;
				$api['isResource'] = true;//prevent db errors
			}
			if(!is_array($api['via'])) {
				$api['via'] = array();
			}
			if(!is_array($api['filters'])) { //don't want stupid ppl adding strings
				$api['filters'] = array();
			}
			$db = Loader::db();
			$db->Execute('insert into ApiRouteRegistry (route, pkgHandle, routeName, aclass, method, via, afilter, enabled, isResource, only) values (?,?,?,?,?,?,?,?,?,?)', array($api['route'], $api['pkgHandle'], $api['routeName'], $api['class'], $api['method'], serialize($api['via']), serialize($api['filters']), $api['enabled'], $api['isResource'], serialize($api['only'])));
			$ID = $db->Insert_ID();
			return self::getByID($ID);
		}
		throw new Exception(t('An API with that route already exists!')); //possibly just return false
	}

	public static function getByID($ID) {
		$db = Loader::db();
		$row = $db->GetRow("select * from ApiRouteRegistry where ID = ?", array($ID));
		if ($row) {
			$et = new self();
			$et->setPropertiesFromArray($row);
			unset($et->error);//wtf
			return $et;
		}
	}

	private static function apiRouteExists($api) {
		$db = Loader::db();
		$r = $db->GetOne("select count(ID) from ApiRouteRegistry where route = ?", array($api));
		return $r > 0;
	}

	public function getID() {return $this->ID;}
	public function getPackageHandle() {return $this->pkgHandle;}
	public function getRoute() {return $this->route;}
	public function isEnabled() {return $this->enabled;}
	public function isResource() {return $this->isResource;}
	public function getName() {return $this->routeName;}
	public function getMethod() {return $this->method;}
	public function getClass() {return $this->aclass;}
	public function getFilters() {return unserialize($this->afilter);}
	public function getOnly() {return unserialize($this->only);}
	public function getVia() {return unserialize($this->via);}

	public static function getApiRouteList() {
		$db = Loader::db();
		$apis = array();
		$r = $db->Execute('select ID from ApiRouteRegistry order by ID asc');
		while ($row = $r->FetchRow()) {
			$apis[] = self::getByID($row['ID']);
		}
		return $apis;
	}
	
	public static function getApiListByPackage($pkgHandle) {
		$db = Loader::db();
		$apis = array();
		$r = $db->Execute('select ID from ApiRouteRegistry where pkgHandle = ? order by ID asc', array($pkgHandle));
		while ($row = $r->FetchRow()) {
			$apis[] = self::getByID($row['ID']);
		}
		return $apis;
	}
	
	public static function removeByPackage($pkgHandle) {
		$apis = self::getApiListByPackage($pkgHandle);
		foreach($apis as $api) {
			$api->remove();
		}
	}
	
	public function remove() {
		$db = Loader::db();
		$db->Execute('delete from ApiRouteRegistry where ID = ?', $this->ID);
	}


}