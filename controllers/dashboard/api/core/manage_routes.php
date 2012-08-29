<?php defined('C5_EXECUTE') or die('Access Denied.');
class DashboardApiCoreManageRoutesController extends DashboardBaseController {

	public function view() {
		$valt = Loader::helper('validation/token');
		$html = Loader::helper('html');

		//$this->addFooterItem($html->javascript('http://static.jstree.com/v.1.0pre/jquery.jstree.js'));
		$this->addFooterItem($html->javascript('jstree/jquery.jstree.js', C5_API_HANDLE));
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