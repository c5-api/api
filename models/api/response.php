<?php defnied('C5_EXECUTE') or die('Access Denied');

class ApiResponse {
	
	public static function setHeader($code) {
		header(':', true, $code);//in php 5.4 http_response_code() is added, but we use this for older versions.
	}

	public static function generateJSON($data) {
		header('Content-type: application/json');
		$json = Loader::helper('json');
		return $json->encode($data);
	}

	public static function generateXML($data) {
		if(class_exists('XMLWriter')) {
			header ("Content-Type:text/xml");
			$xml = new XMLWriter();
			$xml->openMemory();
			$xml->startDocument('1.0', 'UTF-8');
			self::generateXml($xml, $data);
			return $xml->outputMemory(true);
		} else {
			throw new Exception('XMLWriter class does not exist!');
		}
	}
	
}