<?php defined('C5_EXECUTE') or die("Access Denied.");

Loader::library('3rdparty/api_router', 'api');

class ApiRequest {

	public function parseRequest() { //have routes added on_start and run this on_before_render?
		$req = Request::get();
		$path = $req->getRequestPath();
		$path = trim($path, '/')
		$pathparts = explode($path, '/');
		
		$basepath = trim(BASE_API_PATH, '/')
		$match = explode($basepath, '/');
		$mparts = count($match);

		$i = 0;
		$combine = array_combine($match, $pathparts);
		foreach($combine as $m => $p) {
			if($i > $mparts) {
				//we made it!
				$base = str_replace($path, $basepath, '');
				try {
					$self = new self();
					$self->dispatch('/'.BASE_API_PATH);//we pass it to the actuall proccessing - not sure what I should be passing here
				} catch (Exception $e) {
					$self->handleException($e);
				}
			}
			if($m != $p) {
				return;//didn't match so lets end it.
			}
		}
	}
	
	public function dispatch($path = null) {
		$r = new ApiRouter($path);
		$r->match('/users/:id', 'users#show', array('filters' => array('id' => '(\d+)')));//got to load these from somewhere
		if ($r->hasRoute()) {
			extract($r->getRoute());
			//herp
		} else {
			throw new Exception('Invalid Route!');
		}
	}
	
	public function handleException(Exception $e) {
	
	}

}