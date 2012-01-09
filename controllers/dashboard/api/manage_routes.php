<?php defined('C5_EXECUTE') or die('Access Denied.');
class DashboardApiManageRoutesController extends DashboardBaseController {

	public function view() {
		$valt = Loader::helper('validation/token');
		$html = Loader::helper('html');
		$this->addFooterItem($html->javascript('http://static.jstree.com/v.1.0pre/jquery.jstree.js'));
		$this->addFooterItem('<script type="text/javascript">
			$(function () {
    			$("#api_list").jstree({
    				"themes" : {
            			"theme" : "default",
            			"dots" : false,
            			"icons" : true
        			},
    				"plugins" : [ "themes", "html_data", "checkbox", "sort", "ui" ]
   				});
   				$("#api_list_ul").show();
			});
			$("#api_list").bind("select_node.jstree", function (e, data) {
				var via = data.rslt.obj.children("a");
				if(via.attr("data-pkg")) {
					$("#api_list").jstree("toggle_node", via);
					return;
				}
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
				if(en.attr("data-pkg")) {
					data = {
						pkg : en.attr("data-pkg"),
						enabled : 1,
						token : "'.$valt->generate('api_enable').'"
					}
				} else {
					data = {
						ID : en.attr("data-id"),
						enabled : 1,
						token : "'.$valt->generate('api_enable').'"
					}
				}
				$.post("'.Loader::helper('concrete/urls')->getToolsURL('enable', C5_API_HANDLE).'", data, function(data) {
					if(data == 1) {
						jQuery.fn.dialog.hideLoader();
						$("#status").addClass("alert-message success").removeClass("error").html("'.t('Route successfully updated.').'");
					} else {
						jQuery.fn.dialog.hideLoader();
						$("#status").addClass("alert-message error").removeClass("success").html(data);
						console.log(data);
					}
				});
			});
			$("#api_list").bind("uncheck_node.jstree", function(e, data) {
				var en = data.rslt.obj.children("a");
				jQuery.fn.dialog.showLoader();
				if(en.attr("data-pkg")) {
					data = {
						pkg : en.attr("data-pkg"),
						enabled : 0,
						token : "'.$valt->generate('api_enable').'"
					}
				} else {
					data = {
						ID : en.attr("data-id"),
						enabled : 0,
						token : "'.$valt->generate('api_enable').'"
					}
				}
				$.post("'.Loader::helper('concrete/urls')->getToolsURL('enable', C5_API_HANDLE).'", data, function(data) {
					if(data == 1) {
						jQuery.fn.dialog.hideLoader();
						$("#status").addClass("alert-message success").removeClass("error").html("'.t('Route successfully updated.').'");
					} else {
						jQuery.fn.dialog.hideLoader();
						$("#status").addClass("alert-message error").removeClass("success").html(data);
						console.log(data);
					}
				});
			});
	</script>');
	}

}