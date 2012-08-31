<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiAuthKeyModel extends ADOdb_Active_Record {

	static $whitelist = array('key', 'hash');

	public function __construct() {
		$db = Loader::db();
		parent::__construct('ApiAuthKey');
	}

	public static function getByAppID($ID) {
		$route = new ApiAuthKeyModel();
		$route->Load('appID = ?', array($ID));
		return $route;
	}

	public static function getByPublicKey($ID) {
		$route = new ApiAuthKeyModel();
		$route->Load('publicKey = ?', array($ID));
		return $route;
	}

	public static function add($active = true) {
		$val = Loader::helper('validation/identifier');
		$public = $val->generate('ApiAuthKey', 'publicKey', C5_API_DEFAULT_KEY_LENGTH, true);
		$private = $val->generate('ApiAuthKey', 'privateKey', C5_API_DEFAULT_KEY_LENGTH, true);
		$rt = new ApiAuthKeyModel();
		$rt->publicKey = $public;
		$rt->privateKey = $private;
		$rt->active = $active;
		$rt->save();
		$rt = self::getByPublicKey($public);
		return $rt;
	}

	public static function hash($str) {
		return sha1($str);
	}

	public function validateRequest($public, $hash) {
		$public = self::getByPublicKey($public);
		if(!$public->appID || !$public->active) {
			return false;//invalid public key
		}
		$route= ApiRouter::get()->requestedRoute;

		$request = array_merge($_GET, $_POST);
		foreach($request as $key => $val) {
			if(in_array($key, self::$whitelist)) {
				unset($request[$key]);
			}
		}
		$query = http_build_query($request);
		if($query) {
			$query = '?'.$query;
		}
		$url = $route.$query;
		$nhash = self::hash($url.':'.$public->privateKey);
		if($hash == $nhash) {
			return true;
		}
		return false;
	}
}

class ApiAuthKeyList extends DatabaseItemList{

	public function __construct() {
		$this->setQuery('select appID from ApiAuthKey');
		$this->sortBy('appID', 'asc');
	}

	public function filterByActive($active = 1) {
		$this->filter('active', $active, '=');
	}

	public function get() {
		$r = parent::get(0, 0);
		$keys = array();
		foreach($r as $row) {
			$keys[] = ApiAuthKeyModel::getByAppID($row['appID']);	
		}
		return $keys;
	}
}