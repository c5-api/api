<?php defined('C5_EXECUTE') or die("Access Denied.");

$pkgs = ApiRouteList::getPackagesList();
//print_r($pkgs);
$canremove = true;
if(count($pkgs) > 0) {
	$canremove = false;
}

if(!$canremove) {
	$names = array();
	foreach($pkgs as $pkg) {
		if(is_object($pkg)) {
			$names[] = '<li>'.$pkg->getPackageName().' - '.$pkg->getPackageDescription().'</li>';
		}
	}
	
	$names = implode('<br/>', $names);
	echo '<hr>';
	echo '<div class="alert-message error">'.t('If you do not uninstall the below packages before uninstalling this addon, it could potentially break your site.').'</div>';
	echo '<ul>'.$names.'</ul>';
	
	echo t('To procced with uninstallation, please type in "%s" below.', t('remove'));
	echo '<br/><input type="text" name="force"/>';
	
}