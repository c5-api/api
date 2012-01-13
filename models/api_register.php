<?php  defined('C5_EXECUTE') or die('Access Denied');

/**
 * concrete5 API
 * This class gives the ability to add, edit, remove, and get information about routes
 *
 * @category Api
 * @package  ApiCore
 * @author   Michael Krasnow <mnkras@gmail.com>
 * @author   Lucas Anderson <lucas@lucasanderson.com>
 * @copyright 2011-2012 Michael Krasnow and Lucas Anderson
 * @license  See License.txt
 * @link     http://c5api.com
 */
class ApiRegister extends Object {

    /**
     * Used to add new apis.
     * 
     * Will return a false on failure, or an object if it succeedes
     * 
     * <code>
     * $api = array();
     * $api['pkgHandle'] = 'my_pkg';
     * $api['route'] = 'hello/:id';
     * $api['routeName'] = 'Says "Hello" to you!';
     * $api['class'] = 'hello_world';
     * $api['method'] = 'hello';
     * $api['filters']['id'] = '(\d+)';
     * $api['via'][] = 'get';
     * $api['enabled'] = true;
     * ApiRegister::add($api);
     * </code>
     *
     * @return bool|ApiRegiser
     */
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
			if(!is_array($api['via']) || count($api['via']) == 0) {
				$api['via'] = array('get', 'post', 'delete', 'put');
			}
			if(!is_array($api['filters'])) { //don't want stupid ppl adding strings
				$api['filters'] = array();
			}
			$api['viaEnabled'] = $api['via'];
			$db = Loader::db();
			$db->Execute('insert into ApiRouteRegistry (route, pkgHandle, routeName, aclass, method, via, afilter, enabled, viaEnabled) values (?,?,?,?,?,?,?,?,?)', array($api['route'], $api['pkgHandle'], $api['routeName'], $api['class'], $api['method'], serialize($api['via']), serialize($api['filters']), $api['enabled'], serialize($api['viaEnabled'])));
			$ID = $db->Insert_ID();
			Events::fire('on_api_add', self::getByID($ID));
			return self::getByID($ID);
		}
		return false;
		//throw new Exception(t('An API with that route already exists!')); //possibly just return false
	}

	/**
	 * Gets an API by it's ID in the database
	 *
	 * @param int $ID Api ID
	 * @return void|ApiRegister
	 */
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
	 * Checks if a route already exists
	 * 
	 * @deprecated
	 * 
	 * @param string $api Route like: users/:id
	 * @return bool
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
	public function getViaEnabled() {return unserialize($this->viaEnabled);}

	public function setPackageHandle($data) {
		$db = Loader::db();
		$db->Execute('UPDATE ApiRouteRegistry SET pkgHandle = ? WHERE ID = ?', array($data, $this->ID));
		return self::getByID($this->ID);
	}
	
	public function setRoute($data) {
		$db = Loader::db();
		$db->Execute('UPDATE ApiRouteRegistry SET route = ? WHERE ID = ?', array($data, $this->ID));
		return self::getByID($this->ID);
	}

	public function setEnabled($data = true) {
		$db = Loader::db();
		if($data) {
			$data = true;
		} else {
			$data = false;
		}
		$db->Execute('UPDATE ApiRouteRegistry SET enabled = ? WHERE ID = ?', array($data, $this->ID));
		return self::getByID($this->ID);
	}
	
	public function setName($data) {
		$db = Loader::db();
		$db->Execute('UPDATE ApiRouteRegistry SET routeName = ? WHERE ID = ?', array($data, $this->ID));
		return self::getByID($this->ID);
	}
	
	public function setMethod($data) {
		$db = Loader::db();
		$db->Execute('UPDATE ApiRouteRegistry SET method = ? WHERE ID = ?', array($data, $this->ID));
		return self::getByID($this->ID);
	}
	
	public function setClass($data) {
		$db = Loader::db();
		$db->Execute('UPDATE ApiRouteRegistry SET aclass = ? WHERE ID = ?', array($data, $this->ID));
		return self::getByID($this->ID);
	}
	
	public function setFilters(array $data) {
		$db = Loader::db();
		$db->Execute('UPDATE ApiRouteRegistry SET pkgHandle = ? WHERE ID = ?', array(serialize($data), $this->ID));
		return self::getByID($this->ID);
	}
	
	public function setVia(array $data) {
		$db = Loader::db();
		$db->Execute('UPDATE ApiRouteRegistry SET via = ? WHERE ID = ?', array(strtolower(serialize($data)), $this->ID));
		return self::getByID($this->ID);
	}

	public function setViaEnabled(array $data) {
		$db = Loader::db();
		$db->Execute('UPDATE ApiRouteRegistry SET viaEnabled = ? WHERE ID = ?', array(strtolower(serialize($data)), $this->ID));
		return self::getByID($this->ID);
	}

	/**
	 * Gets an array of all APIs
	 * 
	 * @return array
	 */
	public static function getApiRouteList() {
		$db = Loader::db();
		$apis = array();
		$r = $db->Execute('select ID from ApiRouteRegistry order by ID asc');
		while ($row = $r->FetchRow()) {
			$apis[] = self::getByID($row['ID']);
		}
		return $apis;
	}

	/**
	 * Gets an array of all APIs by package handle
	 * 
	 * @param string $pkgHandle Package Handle
	 * @return array
	 */	
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

	/**
	 * Removes all APIs from the database for a specific package
	 * 
	 * @param string $pkgHandle Package Handle
	 * @return void
	 */	
	public static function removeByPackage($pkgHandle) {
		$apis = self::getApiListByPackage($pkgHandle);
		foreach($apis as $api) {
			$api->remove();
		}
	}

	/**
	 * Gets an array of all APIs by path
	 * 
	 * @param string $route route path: users/:id
	 * @return array
	 */		
	public static function getRoutesFromPath($route) {
		$db = Loader::db();
		$r = $db->Execute("select ID from ApiRouteRegistry where route = ?", array($route));
		$arr = array();
		while($row = $r->FetchRow()) {
			$arr[] = self::getByID($row['ID']);
		}
		return $arr;
	}
	
	/**
	 * Removes an api from the database
	 * 
	 * @return void|bool
	 */	
	public function remove() {
		$db = Loader::db();
		$ret = Events::fire('on_api_before_remove', $this);
		if($ret !== false) {
			return false;
		}
		$db->Execute('delete from ApiRouteRegistry where ID = ?', array($this->ID));
	}
	
	/**
	 * Gets an array of all packages that have installed apis
	 * 
	 * @return array
	 */	
	public static function getPackageList() {
		$db = Loader::db();
		$r = $db->Execute('select distinct pkgHandle from ApiRouteRegistry where pkgHandle <> ? order by pkgHandle asc', array(C5_API_HANDLE));
		$pkg = array();
		while($row = $r->FetchRow()) {
			$pkg[] = $row['pkgHandle'];
		}
		return $pkg;
	}

	/**
	 * Checks that a package's routes can be refreshed.
	 *
	 * @param string $pkg Package Handle
	 * @return bool
	 */	
	public static function canRefresh($pkg) {
		$obj = Loader::package($pkg);
		if(is_object($obj)) {
			if(is_callable(array($obj, 'refreshRoutes'))) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Drops all routes then re-installs them from the refreshRoutes method of the package controller.
	 *
	 * @param string $pkg Package Handle
	 * @return bool
	 */
	public function refreshRoutes($pkg) {
		if(self::canRefresh($pkg)) {
			self::removeByPackage($pkg);
			$obj = Loader::package($pkg);
			$obj->refreshRoutes();
			return true;
		}
		return false;
	}

}