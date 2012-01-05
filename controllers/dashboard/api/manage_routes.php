<?php defined('C5_EXECUTE') or die('Access Denied.');
class DashboardApiManageRoutesController extends DashboardBaseController {

	public function view() {
		$html = Loader::helper('html');
		$this->addFooterItem($html->javascript('http://static.jstree.com/v.1.0pre/jquery.jstree.js'));
		$this->addFooterItem('<script type="text/javascript">
			$(function () {
				//$(".vias").dialog();
    			$("#api_list").jstree({
    				"plugins" : [ "themes", "html_data", "checkbox", "sort", "ui" ]
   				});
			});
			$("#api_list").bind("select_node.jstree", function (e, data) {
				var via = data.rslt.obj.children("a");
				obj = {
					modal: true,
					href: via.attr("href"),
					width: via.attr("dialog-width"),
					height: via.attr("dialog-height"),
					title: via.attr("dialog-title"),
					appendButtons: via.attr("dialog-append-buttons"),
				}
				jQuery.fn.dialog.open(obj);
			})
			$("#api_list").bind("check_node.jstree", function(e, data) {
				var en = data.rslt.obj.children("a");
				jQuery.fn.dialog.showLoader();
				$.post("'.Loader::helper('concrete/urls')->getToolsURL('enable', C5_API_HANDLE).'", { ID:en.attr("data-id"), enabled:1 }, function(data) {
					if(data == 1) {
						jQuery.fn.dialog.hideLoader();
					} else {
						jQuery.fn.dialog.hideLoader();
						$("#status").addClass("alert-message error").html("'.t('An Unknown Error Occured.').'");
						console.log(data);
					}
				});
			});
			$("#api_list").bind("uncheck_node.jstree", function(e, data) {
				var en = data.rslt.obj.children("a");
				jQuery.fn.dialog.showLoader();
				$.post("'.Loader::helper('concrete/urls')->getToolsURL('enable', C5_API_HANDLE).'", { ID:en.attr("data-id"), enabled:0 }, function(data) {
					if(data == 1) {
						jQuery.fn.dialog.hideLoader();
					} else {
						jQuery.fn.dialog.hideLoader();
						$("#status").addClass("alert-message error").html("'.t('An Unknown Error Occured.').'");
						console.log(data);
					}
				});
			});

			/*function node_is_check(object) {

				if (object.inst.is_checked(object.rslt.obj)) {
					return "checked"
				} else {
					return "not checked";
				}
			}*/
	</script>');
	}

	public function vias() {
	Loader::model('api_register', 'api');
	$pkgs = ApiRegister::getPackageList();
	}

}