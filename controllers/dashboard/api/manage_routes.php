<?php defined('C5_EXECUTE') or die('Access Denied.');
class DashboardApiManageRoutesController extends DashboardBaseController {

	public function view() {
		$html = Loader::helper('html');
		$this->addFooterItem($html->javascript('http://static.jstree.com/v.1.0pre/jquery.jstree.js'));
		$this->addFooterItem('<script type="text/javascript">
			$(function () {
    			$("#api_list").jstree({
    				"plugins" : [ "themes", "html_data", "checkbox", "sort", "ui" ]
   				});
			});
	</script>');
	}

}