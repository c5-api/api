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
$re = ($_POST['enabled']) ? 1 : 0;
//print_r($_POST);
if(is_string($pkg)) {
	$pkg = Package::getByHandle($pkg);
	if(!is_object($pkg)) {
		die(t('Invalid Package'));
	}
	$list = ApiRouteList::getListByPackage($pkg);
	foreach($list as $api) {
		$api->enabled = $re;
		$api->save();
	}
	echo '1';
	exit;
}

if(!intval($id)) {
	die(t('Invalid Route ID'));
} else {
	$api = ApiRoute::getByID($id);
	if(!is_object($api) || !$api->ID) {
		die(t('Invalid Route ID'));
	}
	$api->enabled = $re;
	$api->save();
	echo '1';
	exit;
}