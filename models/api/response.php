<?php defnied('C5_EXECUTE') or die('Access Denied');

class ApiResponse {

	static $format;
	
	public function __construct() {
		self::getFormat();
	}

	public static function setCode($code = false) {
		if(!$code) {
			$code = 200;
		}
		header(':', true, $code);//in php 5.4 http_response_code() is added, but we use this for older versions.
	}

	public static function getFormat() {
		if(isset($_REQUEST['format'])) {
			if(in_array($_REQUEST['format'], ApiFormatModel::getHandles())) {
				$fo = ApiFormatModel::getByHandle($_REQUEST['format']);
				ApiLoader::apiFormat($fo->handle, Package::getByID($fo->pkgID));
				self::$format = $fo;
				return self::$format;
			}
		}
		$fo = ApiFormatModel::getDefault();
		self::$format = $fo;
		return self::$format;
	}
	
}