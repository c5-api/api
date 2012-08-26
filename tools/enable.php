<?php defined('C5_EXECUTE') or die("Access Denied.");

$c = Page::getByPath('/dashboard/api/core/manage_routes');
$cp = new Permissions($c);
if(!$cp->canRead()) {
	die(t('Access Denied'));
}
$token = $_REQUEST['token'];
$valt = Loader::helper('validation/token');
if(!$valt->validate('api_enable', $token)) {
	die($valt->getErrorMessage());
}
$id = $_POST['ID'];
$pkg = $_POST['pkg'];
$re = $_POST['enabled'];
//print_r($_POST);
if(is_string($pkg)) {
	if(is_object(Package::getByHandle($pkg))) {
		Loader::model('api_register', C5_API_HANDLE);
	} else {
		die(t('Invalid Package'));
	}
	$list = ApiRegister::getApiListByPackage($pkg);
	foreach($list as $api) {
		$api->setEnabled($re);
	}
	echo '1';
	exit;
}

if(!intval($id)) {
	die(t('Invalid Route ID'));
} else {
	Loader::model('api_register', C5_API_HANDLE);
	$api = ApiRegister::getByID($id);
	if(!is_object($api)) {
		die(t('Invalid Route ID'));
	}
	$api->setEnabled($re);
	echo '1';
	exit;
}