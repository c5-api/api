<?php defined('C5_EXECUTE') or die('Access Denied');

class UnauthorizedApiRouteController extends ApiRouteController {
	
	public function run() {
		$this->setCode(401);
		$this->respond(array('error' => 'Unauthorized'));
	}

}