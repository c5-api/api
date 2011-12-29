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
					$dirrel = strlen(DIR_REL.'/'.DISPATCHER_FILENAME);
					if(substr($_SERVER['REQUEST_URI'], 0, $dirrel) == DIR_REL.'/'.DISPATCHER_FILENAME) { //pretty url hack
						$path = DIR_REL.'/'.DISPATCHER_FILENAME.'/'.BASE_API_PATH;
					} else {
						$path = DIR_REL.'/'.BASE_API_PATH;
					}
					$self->dispatch($path);//we pass it to the actuall proccessing - not sure what I should be passing here
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
			throw new Exception(t('Invalid Route!'), 501);
		}
	}
	
	public function handleException(Exception $e) {
		//print_r($e);
		$resp = new ApiResponse();
		$resp->setData(array());
		$resp->setMessage($e->getMessage());
		$resp->setError(true);
		$resp->setCode($e->getCode());
		$resp->send();
	}
	
}

class ApiResponse {

	private $data = array();
	private $message = 'OK';
	private $error = false;
	private $code = 200;//OK

	public function setData($data = null) {
		$this->data = $data;
	}
	
	public function setMessage($data = 'OK') {
		$this->message = $data;
	}
	
	public function setCode($data = 200) {
		$this->code = $data;
	}
	
	public function setError($data = false) {
		if($data) {
			$data = true;
		} else {
			$data = false;
		}
		$this->error = $data;
	}

	public function send() {
		$json = Loader::helper('json');
		$response = array();
		$response['response']['code'] = $this->code;
		$response['response']['error'] = $this->error;
		$response['response']['message'] = $this->message;
		$response['response']['data'] = $this->data;
		echo $json->encode($response);
		exit;
	}

}