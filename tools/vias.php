<?php defined('C5_EXECUTE') or die("Access Denied.");

$c = Page::getByPath('/dashboard/api/manage_routes');
$cp = new Permissions($c);
if(!$cp->canRead()) {
	die(t('Access Denied'));
}

$valt = Loader::helper('validation/token');
$id = $_REQUEST['ID'];
$token = $_REQUEST['token'];
if(!$valt->validate('api_via', $token)) {
	die($valt->getErrorMessage());
}
if(!intval($id)) {
	die(t('Invalid Route ID'));
}
Loader::model('api_register', C5_API_HANDLE);
$api = ApiRegister::getByID($id);
if(!is_object($api)) {
	die(t('Invalid Route ID'));
}
if(isset($_POST['ID'])) {
	$re = $_POST['methods'];
	//print_r($re);
	if(!is_array($re)) {
		$re = array();
	}
	$api->setViaEnabled($re);
	echo '1';
	exit;
}
//print_r($api);
$via = $api->getVia();
$enabled = $api->getViaEnabled();
$urls = Loader::helper('concrete/urls');
echo '<div class="ccm-ui">';
echo '<form id="request_type_form" method="post" action="'.$urls->getToolsURL('vias', C5_API_HANDLE).'">';
echo '<input type="hidden" name="ID" value="'.$id.'"/>';
echo '<input type="hidden" name="token" value="'.$valt->generate('api_via').'"/>';
echo '<div id="stat"></div>';
echo '<table>';
foreach($via as $type) {
	echo '<tr>';
	if(in_array($type, $enabled)) {
		$checked = ' checked="checked"';
	} else {
		$checked = '';
	}
	echo '<td><input name="methods[]" id="'.strtoupper($type).'" type="checkbox" value="'.strtoupper($type).'"'.$checked.'/></td><td><label for="'.strtoupper($type).'">'.strtoupper($type).'</label></td>';
	echo '</tr>';
}
?>
</table>
<div class="dialog-buttons">
	<input type="button" onclick="jQuery.fn.dialog.closeTop()" class="btn" value="<?php echo t('Cancel')?>"/>
	<a href="javascript:void(0);" onclick="submit_request_form()" class="btn accept primary ccm-button-v2-right"><?php echo t('Save')?></a>
</div>
</form>
</div>
<script type="text/javascript">
function submit_request_form() {
	jQuery.fn.dialog.showLoader();
	var form = $('#request_type_form');
	$.post(form.attr('action'), form.serialize(), function(data) {
		if(data == 1) {
			jQuery.fn.dialog.hideLoader();
			$('#stat').addClass('alert-message success').html('<?php echo t('Methods Saved.')?>');
			setTimeout("jQuery.fn.dialog.closeTop()", 1500);
		} else {
			jQuery.fn.dialog.hideLoader();
			$('#stat').addClass('alert-message error').html('<?php echo t('An Unknown Error Occured.')?>');
			console.log(data);
		}
	});
}
</script>