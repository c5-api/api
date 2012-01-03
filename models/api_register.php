<?php  defined('C5_EXECUTE') or die('Access Denied');

class ApiRegister extends Object {

	public static function add(array $api) {
		
		$api['route'] = trim($api['route'], '/ ');
		if($api['route']/* && !self::apiRouteExists($api['route'])*/) {
			if(!isset($api['enabled']) || $api['enabled']) { //if it is not set, or it is set and is enabled set it to true (prevents db errors)
				$api['enabled'] = true;
			} else {
				$api['enabled'] = false;
			}
			if(!$api['pkgHandle']) {
				$api['pkgHandle'] = C5_API_HANDLE;//possibly change this to _error,
			}
			if(!$api['routeName']) {
				$api['routeName'] = t('Unkown Route Name'); //we could make the name into a handle and do reverse routing...?
			}
			if(!is_array($api['via'])) {
				$api['via'] = array();
			}
			if(!is_array($api['filters'])) { //don't want stupid ppl adding strings
				$api['filters'] = array();
			}
			$db = Loader::db();
			$db->Execute('insert into ApiRouteRegistry (route, pkgHandle, routeName, aclass, method, via, afilter, enabled) values (?,?,?,?,?,?,?,?)', array($api['route'], $api['pkgHandle'], $api['routeName'], $api['class'], $api['method'], serialize($api['via']), serialize($api['filters']), $api['enabled']));
			$ID = $db->Insert_ID();
			Events::fire('on_api_add', self::getByID($ID));
			return self::getByID($ID);
		}
		return false;
		//throw new Exception(t('An API with that route already exists!')); //possibly just return false
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

	/**
	 * Unused now?
	 */
	private static function apiRouteExists($api) {
		$db = Loader::db();
		$r = $db->GetOne("select count(ID) from ApiRouteRegistry where route = ?", array($api));
		return $r > 0;
	}

	public function getID() {return $this->ID;}
	public function getPackageHandle() {return $this->pkgHandle;}
	public function getRoute() {return $this->route;}
	public function isEnabled() {return $this->enabled;}
	public function getName() {return $this->routeName;}
	public function getMethod() {return $this->method;}
	public function getClass() {return $this->aclass;}
	public function getFilters() {return unserialize($this->afilter);}
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
		if($pkgHandle == C5_API_HANDLE) { //safety
			return false;
		}
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
	
	public static function getRoutesFromPath($route) {
		$db = Loader::db();
		$r = $db->Execute("select ID from ApiRouteRegistry where route = ?", array($route));
		$arr = array();
		while($row = $r->FetchRow()) {
			$arr[] = self::getByID($row['ID']);
		}
		return $arr;
	}
	
	public function remove() {
		$db = Loader::db();
		$ret = Events::fire('on_api_before_remove', $this);
		if($ret !== false) {
			return false;
		}
		$db->Execute('delete from ApiRouteRegistry where ID = ?', array($this->ID));
	}
	
	public static function getPackageList() {
		$db = Loader::db();
		$r = $db->Execute('select distinct pkgHandle from ApiRouteRegistry where pkgHandle <> ? order by pkgHandle asc', array(C5_API_HANDLE));
		$pkg = array();
		while($row = $r->FetchRow()) {
			$pkg[] = $row['pkgHandle'];
		}
		return $pkg;
	}


}