<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiAuthList {
	
	public static function getList($all = false) {
		$where = '';
		if(!$all) {
			$where = ' where enabled = 1';
		}
		$db = Loader::db();
		$r = $db->Execute('select aID from ApiAuth'.$where);
		$d = array();
		while ($row = $r->FetchRow()) {
			$d[] = ApiAuthModel::getByID($row['aID']);
		}
		return $d;
	}

}