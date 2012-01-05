<?php defined('C5_EXECUTE') or die('Access Denied.');
class DashboardApiManageRoutesController extends DashboardBaseController {

	public function view() {
		$html = Loader::helper('html');
		$this->addFooterItem($html->javascript('http://static.jstree.com/v.1.0pre/jquery.jstree.js'));
		$this->addFooterItem('<script type="text/javascript">
			$(function () {
				$(".vias").dialog();
    			$("#api_list").jstree({
    				"plugins" : [ "themes", "html_data", "checkbox", "sort", "ui" ]
   				});
			});
			$("#api_list").bind("select_node.jstree", function (e, data) {
				//var href = data.rslt.obj.children("a").attr("href");
				//$("#modify_vias").load(href);
				//$("#terms").dialog({modal:true});
			})
			$("#api_list").bind("check_node.jstree", function(e, data) {
				$("#status").html("<BR>clicked and " + node_is_check(data));
			});
			$("#api_list").bind("uncheck_node.jstree", function(e, data) {
				$("#status").html("<BR>clicked and " + node_is_check(data));
			});

			function node_is_check(object) {

				if (object.inst.is_checked(object.rslt.obj)) {
					return "checked"
				} else {
					return "not checked";
				}
			}
	</script>');
	}

	public function vias() {
	Loader::model('api_register', 'api');
	$pkgs = ApiRegister::getPackageList();
	}

}