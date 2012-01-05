<?php defined('C5_EXECUTE') or die("Access Denied.");

$c = Page::getByPath('/dashboard/api/manage_routes');
$cp = new Permissions($c);
if(!$cp->canRead()) {
	die(t('Access Denied'));
}

$id = $_REQUEST['ID'];
if(!intval($id)) {
	die(t('Invalid Route ID'));
}
Loader::model('api_register', C5_API_HANDLE);
$api = ApiRegister::getByID($id);
if(!is_object($api)) {
	die(t('Invalid Route ID'));
}
if(isset($_POST['ID'])) {
	$re = $_POST['enabled'];
	//print_r($_POST);
	$api->setEnabled($re);
	echo '1';
	exit;
}