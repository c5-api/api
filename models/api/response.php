<?php defined('C5_EXECUTE') or die('Access Denied');

class ApiResponse {

	public $format = array();

	private $code = 200;

	public static function get() {
		static $instance;
		if (!isset($instance)) {
			$v = __CLASS__;
			$instance = new $v;
			$instance->getFormatObject();
		}
		return $instance;
	}

	public function setCode($code = false) {
		$this->code = $code;
	}

	public function sendHeaders() {
		header(':', true, $this->code);//in php 5.4 http_response_code() is added, but we use this for older versions.
	}

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

	public function encodeData($data) {
		$format = $this->format;
		$class = $format->getClass();
		$class->setHeaders();
		return $class->display($data);

	}
	
}