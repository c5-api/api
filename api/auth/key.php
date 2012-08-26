<?php defined('C5_EXECUTE') or die('Access Denied');

class KeyApiAuth extends ApiAuthModel {

	public function authorize() {
		if($_REQUEST['key'] == '12345') {
			return true;
		}
		return false;
	}

	public function getKeys() {

	}
	
}