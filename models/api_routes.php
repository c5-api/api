<?php defined('C5_EXECUTE') or die("Access Denied.");

Loader::library('3rdparty/api_router', C5_API_HANDLE);

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

		Loader::model('api_register', C5_API_HANDLE);
		$list = ApiRegister::getApiRouteList();
		foreach($list as $api) {
			//print_r($api);
			if(!$api->isEnabled()) {
				continue;
			}
			$params = array();
			if($api->getClass() && $api->getMethod()) {
				$action = $api->getClass().'#'.$api->getMethod();//eg: User#listUsers
			} else {
				$action = '';
			}
			if($api->getFilters()) {
				$params['filters'] = $api->getFilters();
			}
			if($api->getVia()) {
				$params['via'] = implode(',', $api->getVia());
			}
			//var_dump('/'.$api->getRoute(), $api->getPackageHandle(), $action, $params);
			$r->match('/'.$api->getRoute(), $api->getPackageHandle(), $action, $params);
		}
		
		if ($r->hasRoute()) {
			Events::fire('on_api_found_route', $r->getRoute());
			extract($r->getRoute());
			$txt = Loader::helper('text');
			Loader::model('api_controller', C5_API_HANDLE);
			Loader::model('api/'.$txt->handle($controller), $pkgHandle);
			$resp = new ApiResponse();
			try {
				$auth = Events::fire('on_api_auth', $r->getRoute()); //custom auth possibly, need to test
				if($auth === null) {
					$key = $_REQUEST['key'];
					$res = self::authorize($key);
					if(!$res) {
						$resp = new ApiResponse();
						$resp->setMessage(t('Unauthorized'));
						$resp->setError(true);
						$resp->setCode(401);
						$resp->send();
					}
				}

				$ret = call_user_func_array(array('Api'.$controller, $action), $params);
			} catch(Exception $e) {
				throw new Exception($e->getMessage(), 500);
			}
			$resp->setData($ret);
			$resp->send();
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
	
	public static function authorize($key = '') {
		$pkg = Package::getByHandle(C5_API_HANDLE);
		return $pkg->config('key') == $key;
	}
	
}

class ApiResponse {

	private $data = array();
	private $message = 'OK';
	private $error = false;
	private $code = 200;//OK

	public function setData($data = null) {
		if(!is_array($data) && !is_object($data)) {
			$data = array($data);
		}
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
		header('Content-type: application/json');
		$response = array();
		$response['response']['code'] = intval($this->code);
		$response['response']['error'] = $this->error;
		$response['response']['message'] = $this->message;
		$response['response']['data'] = $this->data;
		echo $json->encode($response);
		exit;
	}

}