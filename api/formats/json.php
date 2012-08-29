<?php defined('C5_EXECUTE') or die('Access Denied');

class JsonApiFormat extends ApiFormatModel {
	
	public function setHeaders() {
		header('Content-type: application/json');
	}

	public function display($data) {
		$json = Loader::helper('json');
		return $json->encode($data);
	}

}