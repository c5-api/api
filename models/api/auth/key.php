<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiAuthKey extends ADOdb_Active_Record {

	public function __construct() {
		$db = Loader::db();
		parent::__construct('ApiAuthKey');
	}

	public static function getByAppID($ID) {
		$route = new ApiAuthKey();
		$route->Load('appID = ?', array($ID));
		return $route;
	}

	public static function getByPublicKey($ID) {
		$route = new ApiAuthKey();
		$route->Load('publicKey = ?', array($ID));
		return $route;
	}

	public static function add($active = true) {
		$val = Loader::helper('validation/identifier');
		$public = $val->generate('ApiAuthKey', 'publicKey', C5_API_DEFAULT_KEY_LENGTH, true);
		$private = $val->generate('ApiAuthKey', 'privateKey', C5_API_DEFAULT_KEY_LENGTH, true);
		$rt = new ApiAuthKey();
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

	public function validateRequest($request) {

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
			$keys[] = ApiAuthKey::getByAppID($row['appID']);	
		}
		return $keys;
	}
}