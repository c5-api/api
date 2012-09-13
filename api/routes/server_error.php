<?php defined('C5_EXECUTE') or die('Access Denied');

class ServerErrorApiRouteController extends ApiRouteController {
	
	public function run() {
		$this->setCode(500);
		$this->respond('Server Error');
	}

}