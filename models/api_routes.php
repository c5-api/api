<?php defined('C5_EXECUTE') or die("Access Denied.");

Loader::library('3rdparty/api_router', C5_API_HANDLE);

/**
 * concrete5 API
 * Processes the request to see if the url is in the BASE_API_PATH and if it is a valid route
 *
 * @category Api
 * @package  ApiCore
 * @author   Michael Krasnow <mnkras@gmail.com>
 * @author   Lucas Anderson <lucas@lucasanderson.com>
 * @copyright 2011-2012 Michael Krasnow and Lucas Anderson
 * @license  See License.txt
 * @link     http://c5api.com
 */
class ApiRequest {

	/**
	 * Parses the requested path for the BASE_API_URL
	 *
	 * @return void
	 */
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
				$self = new self();
				$dirrel = strlen(DIR_REL.'/'.DISPATCHER_FILENAME);
				if(substr($_SERVER['REQUEST_URI'], 0, $dirrel) == DIR_REL.'/'.DISPATCHER_FILENAME) { //pretty url hack
					$path = DIR_REL.'/'.DISPATCHER_FILENAME.'/'.BASE_API_PATH;
				} else {
					$path = DIR_REL.'/'.BASE_API_PATH;
				}
				$self->dispatch($path);
			}
		}
	}

	/**
	 * Checks if the requested route and if so serve it up or make nice errors
	 *
	 * @param string $path base path for the api url
	 * @return void
	 */	
	public function dispatch($path = null) {
		if(!defined('API_REQUEST_METHOD')) {
			define('API_REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
		} else {
			$_SERVER['REQUEST_METHOD'] = API_REQUEST_METHOD;
		}
		$r = new ApiRouter($path);

		Loader::model('api_register', C5_API_HANDLE);
		$list = ApiRegister::getApiRouteList();
		foreach($list as $api) {
			//print_r($api);
			if(!$api->isEnabled()) {
				continue;
			}
			$ve = $api->getViaEnabled();

			if(!in_array(strtolower(API_REQUEST_METHOD), $ve)) {
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
					throw new Exception($e->getMessage(), 500);
				}
			} catch(Exception $e) {
				$resp->handleException($e);
			}
			$resp->setData($ret);
			$resp->send();
			//herp
		} else {
			try { //find a better way to do this
				throw new Exception('ERROR_INVALID_ROUTE', 501);
			} catch(Exception $e) {
				$resp->handleException($e);
			}
		}
	}

	/**
	 * Checks if the key attached to the request is the same as in the database
	 *
	 * @param string $key Key
	 * @return bool
	 */	
	public static function authorize($key = '') {
		$pkg = Package::getByHandle(C5_API_HANDLE);
		return $pkg->config('key') == $key;
	}
	
}

/**
 * concrete5 API
 * Used to send json, xml, and html responses from api calls
 *
 * @category Api
 * @package  ApiCore
 * @author   Michael Krasnow <mnkras@gmail.com>
 * @author   Lucas Anderson <lucas@lucasanderson.com>
 * @copyright 2011-2012 Michael Krasnow and Lucas Anderson
 * @license  See License.txt
 * @link     http://c5api.com
 */
class ApiResponse {

	/**
	 * @var array|object $data
	 */
	private $data = array();
	
	/**
	 * @var string $message
	 */
	private $message = 'OK';
	
	/**
	 * @var bool $error
	 */
	private $error = false;
	
	/**
	 * @var int $code
	 */
	private $code = 200;//OK
	
	/**
	 * @var string $format
	 */
	private $format = 'json';
	
	/**
	 * @var bool $debug
	 */
	private $debug = true;//enables html responses
	
	/**
	 * @var bool $logging
	 */
	private $logging = false;//does nothing so far, log requests and data returned?

	/**
	 * Gets the current api response object
	 *
	 * @return ApiResponse
	 */		
	public static function getInstance() {
			static $instance;
			if (!isset($instance)) {
				$v = __CLASS__;
				$instance = new $v;
			}
			return $instance;
	}

	/**
	 * Checks if the key attached to the request is the same as in the database
	 *
	 * @param Exception $e Caught exception
	 * @return void
	 */	
	public function handleException(Exception $e) {
		//print_r($e);
		$this->setData(array());
		if($this->debug) {
			$this->setMessage($e->getMessage());
		} else {
			$this->setMessage('ERROR_INTERAL_ERROR');
		}
		$this->setError(true);
		$this->setCode($e->getCode());
		$this->send();
	}

	/**
	 * Sets the response format, this is usually done automatically,
	 *
	 * @param string $data Format type, valid options are json, xml, and html if debug is enabled
	 * @return void
	 */	
	public function setFormat($data = 'json') {
		$this->format = $data;
	}

	/**
	 * Sets the response data
	 *
	 * @param array|object $data Data to be sent
	 * @return void
	 */	
	public function setData($data = null) {
		if(!is_array($data) && !is_object($data)) {
			$data = array($data);
		}
		$this->data = $data;
	}

	/**
	 * Sets the response message
	 *
	 * @param string $data Status message
	 * @return void
	 */		
	public function setMessage($data = 'OK') {
		$this->message = $data;
	}

	/**
	 * Sets the response status code
	 *
	 * @param int $data Sets the http status code
	 * @return void
	 */		
	public function setCode($data = 200) {
		$this->code = $data;
	}

	/**
	 * Sets if the response is an error
	 *
	 * @param bool $data Is an error
	 * @return void
	 */		
	public function setError($data = false) {
		if($data) {
			$data = true;
		} else {
			$data = false;
		}
		$this->error = $data;
	}

	/**
	 * Sends the data, message, code, and error in the selected format.
	 *
	 * @return void
	 */	
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

	/**
	 * Encodes the data in json
	 *
	 * @return string
	 */		
	private function sendJson() {
		$json = Loader::helper('json');
		header('Content-type: application/json');
		$response = array();
		$response['response']['status']['code'] = intval($this->code);
		$response['response']['status']['error'] = $this->error;
		$response['response']['status']['message'] = $this->message;
		$response['response']['data'] = $this->data;
		return $json->encode($response);
	}

	/**
	 * Encodes the data in xml
	 *
	 * @return string
	 */			
	private function sendXml() {
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

	/**
	 * Function that generates most of the xml
	 *
	 * @return void
	 */			
	private function generateXml($xml, $data) {
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

	/**
	 * Encodes the data as html
	 *
	 * @return string
	 */			
	private function sendHtml() {
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