
<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Manage Routes'))?>

<h3><?php echo t('Installed Routes'); ?></h3>

<?php
	Loader::model('api_register', 'api');
	$pkgs = ApiRegister::getPackageList();
			if(count($pkgs) === 0) {
			?>
				<p><?php echo t('There are no API routing packages installed on your site.'); ?> <a href="http://c5api.com/get"><?php echo t('Get some!'); ?></a></p>
			<?php
			}
			else {
			?>
				<p><?php echo t('These API routes are available on your site:'); ?></p>
			<?php
				foreach($pkgs as $pkg) {

				}
			}
?>
<div id="demo1" class="demo">
	<ul>
		<li id="phtml_1">
			<a href="#">API Routing Package</a>
			<ul>
				<li id="phtml_2" class="jstree-checked">
					<a href="#">API Route</a>
				</li>
				<li id="phtml_3">
					<a href="#">API Route</a>
				</li>
			</ul>
		</li>
		<li id="phtml_4">
			<a href="#">API Routing Package</a>
		</li>

	</ul>

</div>

<script type="text/javascript" src="http://static.jstree.com/v.1.0pre/jquery.jstree.js"></script>
<script>
$(function () {
    $("#demo1").jstree({"plugins" : [ "themes", "html_data", "checkbox", "sort", "ui" ]
    });
});
</script>
<div class="ccm-spacer"></div>

<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();?>