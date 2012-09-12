<?php defined('C5_EXECUTE') or die('Access Denied.');
	$valt = Loader::helper('validation/token');
	echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Manage Routes'), t('This area allows you to enable or disable remote access to API routes and modify the allowed request methods for each route.'), 'span10 offset1');
?>
	<h3><?php echo t('Installed Routes'); ?></h3>

	<?php
		$pkgs = ApiRouteList::getPackagesList();
		if(count($pkgs) === 0) { ?>
			<p><?php echo t('There are no API routing packages installed on your site.'); ?> <a href="http://c5api.com/get"><?php echo t('Get some!'); ?></a></p>
			<?php
		} else { ?>
			<div id="status"></div>
			<p><?php echo t('These API routes are available on your site:'); ?></p>
			<div id="api_list" class="demo">
				<ul id="api_list_ul" style="display:none">
					<?php foreach($pkgs as $pkg) {
						$pkgRts = ApiRouteList::getListByPackage($pkg);
						$pName = $pkg->getPackageName();
						$pDesc = $pkg->getPackageDescription();
						$pHandle = $pkg->getPackageHandle();
					?>
					<li id="p<?php echo $pkg->getPackageID(); ?>">
						<a data-pkg="<?php echo $pHandle?>" href="javascript:void(0);" title="<?php echo $pDesc; ?>"><?php echo $pName; ?> | <?php echo $pDesc?></a>
						<ul>
							<?php $urls = Loader::helper('concrete/urls');
							foreach($pkgRts as $pkgRt) { ?>

							<li id="r<?php echo $pkgRt->ID; ?>" class="jstree<?php if ($pkgRt->enabled == '1') { echo "-checked"; } ?>">
								<a data-ID="<?php echo $pkgRt->ID; ?>" href="javascript:void(0);"><?php echo $pkgRt->name; ?> : <em>/<?php echo $pkgRt->route;?></em></a>
							</li>

							<?php } ?>

						</ul>

						<?php
						}
			}
			?>
					</ul>
				</div>
<?php
echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();
?>