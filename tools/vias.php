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
print_r($api);
?>

<div class="dialog-buttons">
	<input type="button" onclick="jQuery.fn.dialog.closeTop()" class="btn" value="<?php echo t('Cancel')?>"/>
	<input type="button" class="btn primary ccm-button-v2-right" value="<?php echo t('Save')?>"/>
</div>