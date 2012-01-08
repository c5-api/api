<?php defined('C5_EXECUTE') or die('Access Denied.');

	echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Manage Routes'), t('This area allows you to enable or disable remote access to API routes and modify the allowed request methods for each route.'), 'span10 offset3');
?>
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
				<div id="status"></div>
				<p><?php echo t('These API routes are available on your site:'); ?></p>
				<div id="api_list" class="demo">
					<ul id="api_list_ul" style="display:none">
					<?php foreach($pkgs as $pkg) {
							$pkgRts = ApiRegister::getApiListByPackage($pkg);
							$p = Package::getByHandle($pkg);
							$pName = $p->getPackageName();
							$pDesc = $p->getPackageDescription();
							$pHandle = $p->getPackageHandle();
					?>
					<li id="p<?php echo $pkg; ?>">
						<a data-pkg="<?php echo $pHandle?>" href="javascript:void(0);" title="<?php echo $pDesc; ?>"><?php echo $pName; ?> | <?php echo $pDesc?></a>
						<ul>
							<?php $urls = Loader::helper('concrete/urls');
							foreach($pkgRts as $pkgRt) { ?>

							<li id="r<?php echo $pkgRt->getID(); ?>" class="jstree<?php if ($pkgRt->isEnabled() == '1') { echo "-checked"; } ?>">
								<a class="vias" data-ID="<?php echo $pkgRt->getID(); ?>" dialog-title="<?php echo t('Allowed Request Methods')?>" dialog-append-buttons="true" dialog-width="250" dialog-height="250" href="<?php echo $urls->getToolsURL('vias', C5_API_HANDLE).'?ID='.$pkgRt->getID(); ?>"><?php echo $pkgRt->getName(); ?></a>
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