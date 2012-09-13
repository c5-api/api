<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiResponse {

	/**
	 * The current format
	 * @var array
	 */
	public $format = array();

	/**
	 * Current Status Code
	 * @var int
	 */
	private $code = 200;

	/**
	 * Get the current ApiResponse object
	 * @return ApiResponse
	 */
	public static function get() {
		static $instance;
		if (!isset($instance)) {
			$v = __CLASS__;
			$instance = new $v;
			$instance->getFormatObject();
		}
		return $instance;
	}

	/**
	 * Set the status code for current request
	 * @param bool $code
	 * @return void
	 */
	public function setCode($code = false) {
		$this->code = $code;
	}

	/**
	 * Set response headers based on status code
	 * @param void
	 */
	public function sendHeaders() {
		header(':', true, $this->code);//in php 5.4 http_response_code() is added, but we use this for older versions.
	}

	/**
	 * Get the requested format or use the default format
	 * @return ApiFormatModel
	 */
	public function getFormatObject() {
		if(isset($_REQUEST['format'])) {
			if(in_array($_REQUEST['format'], ApiFormatModel::getHandles())) {
				$fo = ApiFormatModel::getByHandle($_REQUEST['format']);
				ApiLoader::apiFormat($fo->handle, Package::getByID($fo->pkgID));
				$this->format = $fo;
				return $this->format;
			}
		}
		$fo = ApiFormatModel::getDefault();
		$this->format = $fo;
		return $this->format;
	}

	/**
	 * Encode the data from the route
	 * @param mixed
	 * @return string
	 */
	public function encodeData($data) {
		$format = $this->format;
		$class = $format->getClass();
		$class->setHeaders();
		return $class->display($data);

	}
	
}