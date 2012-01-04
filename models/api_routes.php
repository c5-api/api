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
		$resp = ApiResponse::getInstance();
		$resp->setFormat($_REQUEST['format']);
		if ($r->hasRoute()) {
			Events::fire('on_api_found_route', $r->getRoute());
			extract($r->getRoute());
			$txt = Loader::helper('text');
			Loader::model('api_controller', C5_API_HANDLE);
			Loader::model('api/'.$txt->handle($controller), $pkgHandle);
			try {
				$auth = Events::fire('on_api_auth', $r->getRoute()); //custom auth possibly, need to test, should throw error and send to end execution
				//comment the below line for auth
				$auth = true;
				if($auth === false) {
					$key = $_REQUEST['key'];
					$res = self::authorize($key);
					if(!$res) {
						$resp->setMessage('ERROR_UNAUTHORIZED');
						$resp->setError(true);
						$resp->setCode(401);
						$resp->send();
					}
				}

				$ret = call_user_func_array(array('Api'.$controller, $action), $params);
			} catch(Exception $e) {
				if($resp->debug) {
					throw new Exception($e->getMessage(), 500);
				} else {
					throw new Exception('ERROR_INTERAL_ERROR', 500);
				}
			}
			$resp->setData($ret);
			$resp->send();
			//herp
		} else {
			throw new Exception('ERROR_INVALID_ROUTE', 501);
		}
	}
	
	public function handleException(Exception $e) {
		//print_r($e);
		$resp = ApiResponse::getInstance();
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
	private $format = 'json';
	private $debug = true;//enables html responses
	private $logging = false;//does nothing so far, log requests and data returned?
	
	public static function getInstance() {
			static $instance;
			if (!isset($instance)) {
				$v = __CLASS__;
				$instance = new $v;
			}
			return $instance;
	}

	public function setFormat($data = 'json') {
		$this->format = $data;
	}

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
		if($this->format == 'xml' && class_exists('XMLWriter')) {
			echo $this->sendXml();
		} else if($this->format == 'html' && $this->debug) {
			echo $this->sendHtml();
		} else {
			echo $this->sendJson();
		}
		exit;
	}
	
	public function sendJson() {
		$json = Loader::helper('json');
		header('Content-type: application/json');
		$response = array();
		$response['response']['status']['code'] = intval($this->code);
		$response['response']['status']['error'] = $this->error;
		$response['response']['status']['message'] = $this->message;
		$response['response']['data'] = $this->data;
		return $json->encode($response);
	}
	
	public function sendXml() {
		header ("Content-Type:text/xml");
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->startDocument('1.0', 'UTF-8');
			$xml->startElement('response');
				$xml->startElement('status');
					$xml->writeElement('code', intval($this->code));
					$xml->writeElement('error', $this->error);
					$xml->writeElement('message', $this->message);
				$xml->endElement();
				$xml->startElement('data');
					$this->generateXml($xml, $this->data);
				$xml->endElement();
			$xml->endElement();
		return $xml->outputMemory(true);
	}
	
	public function generateXml($xml, $data) {
		foreach($data as $key => $value){
	        if(preg_match('/(\d+)/',$key)) { //if its a number
	        	$key = 'key_'.$key;
	        }
			if(is_array($value)){
	            $xml->startElement($key);
	            $this->generateXml($xml, $value);
	            $xml->endElement();
	            continue;
	        }
	        $xml->writeElement($key, $value);
	    }
	}
	
	public function sendHtml() {
		$html = '';
		$html .= '<h2>%s</h2>';
		$html .= '<code>%s</code>';
		$html .= '<hr>';
		$html .= '<h2>%s</h2>';
		$html .= '<code>%s</code>';
		$html .= '<hr>';
		$html .= '<h2>%s</h2>';
		$html .= '<code>%s</code>';
		$html .= '<hr>';
		$html .= '<h2>%s</h2>';
		$html .= '<code>%s</code>';
		
		$txt = Loader::helper('text');
		$html = sprintf($html, t('Code:'), intval($this->code), t('Error:'), $this->error, t('Message'), $txt->entities($this->message), t('Data:'), nl2br($txt->entities(print_r($this->data, true))));
		return $html;
		//return nl2br(print_r($this,true));//super debug O.o
	
	}
}