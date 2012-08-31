<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiAuthKey extends ApiAuthModel {

	public function authorize() {
		$public = $_REQUEST['key'];
		$hash = $_REQUEST['hash'];
		return ApiAuthKeyModel::validateRequest($public, $hash);
	}
	
}