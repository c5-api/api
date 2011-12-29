<?php defined('C5_EXECUTE') or die("Access Denied.");

Loader::library('3rdparty/api_router', 'api');

class ApiRequest {

	public function parseRequest() { //have routes added on_start and run this on_before_render?
		$req = Request::get();
		$path = $req->getRequestPath();
		$path = trim($path, '/');
		$pathparts = explode('/', $path);
		$pparts = count($pathparts);
		
		$basepath = trim(BASE_API_PATH, '/');
		$match = explode('/', $basepath);
		$mparts = count($match);

		$i = 0;
		$p = $mparts;
		
		if($mparts != $pparts) {
			$pathparts2 = array();
			while($p > 0) {
				$pathparts2[] = array_shift($pathparts);
				$p--;
			}
		} else {
			$pathparts2 = $pathparts;
		}
		$combine = array_combine($match, $pathparts2);
		foreach($combine as $m => $p) {
			$i++;
			if($m != $p) {
				return;
			} else if($m == $p && $i == $mparts) {
				try {
					$self = new self();
					$self->dispatch('/'.BASE_API_PATH);//we pass it to the actuall proccessing - not sure what I should be passing here
				} catch (Exception $e) {
					$self->handleException($e);
				}
			}
		}
	}
	
	public function dispatch($path = null) {
		$r = new ApiRouter($path);//what do I pass here?

		$r->match('/users/:id', 'users#show', array('filters' => array('id' => '(\d+)')));//got to load these from somewhere...db?
		if ($r->hasRoute()) {
			extract($r->getRoute());
			//herp
		} else {
			throw new Exception('Invalid Route!');
		}
	}
	
	public function handleException(Exception $e) {
		echo $e->getMessage();
		exit;
	}

}