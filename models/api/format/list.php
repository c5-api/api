<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiFormatList {

	public static function getList($all = false) {
		$where = '';
		if(!$all) {
			$where = ' where enabled = 1';
		}
		$db = Loader::db();
		$r = $db->Execute('select fID from ApiFormats'.$where);
		$d = array();
		while ($row = $r->FetchRow()) {
			$d[] = ApiFormatModel::getByID($row['fID']);
		}
		return $d;
	}

}