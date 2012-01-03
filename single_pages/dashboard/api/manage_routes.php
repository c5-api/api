
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
				<div id="api_list" class="demo">
					<ul>
					<?php foreach($pkgs as $pkg) {
							$pkgRts = ApiRegister::getApiListByPackage($pkg);
					?>
					<li id="<?php echo $pkg; ?>">
						<a href="#"><?php echo $pkg; ?></a>
						<ul>
							<?php foreach($pkgRts as $pkgRt) { ?>

							<li id="<?php echo $pkgRt->ID; ?>" class="jstree<?php if ($pkgRt->enabled === 1) { echo "-checked"; } ?>">
								<a href="#"><?php echo $pkgRt->routeName; ?></a>
							</li>

							<?php } ?>

						</ul>

						<?php

						}
			}
			?>
					</ul>
				</div>

<script type="text/javascript" src="http://static.jstree.com/v.1.0pre/jquery.jstree.js"></script>
<script>
$(function () {
    $("#api_list").jstree({"plugins" : [ "themes", "html_data", "checkbox", "sort", "ui" ]
    });
});
</script>
<div class="ccm-spacer"></div>

<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();?>