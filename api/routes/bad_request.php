<?php defined('C5_EXECUTE') or die('Access Denied');

class BadRequestApiRouteController extends ApiRouteController {
	
	public function run() {
		$this->setCode(400);
		$this->respond();
	}

}