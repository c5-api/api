<?php defined('C5_EXECUTE') or die('Access Denied');

class KeyApiAuth extends ApiAuthModel {

	public static function authorize() {
		$public = $_REQUEST['key'];
		$hash = $_REQUEST['hash'];
		$time = $_REQUEST['time'];
		return ApiAuthKeyModel::validateRequest($public, $time, $hash);
	}
	
}