<?php defined('C5_EXECUTE') or die('Access Denied');

class ForbiddenApiRouteController extends ApiRouteController {
	
	public function run() {
		$this->setCode(403);
		$this->respond();
	}

}