<?php defined('C5_EXECUTE') or die('Access Denied.');

if ($this->controller->getTask() == 'vias') {
	echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Modify Request Methods'), false, 'span10 offset3');
?>
	<h3></h3>
<?php
}
else {
	echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Manage Routes'), false, 'span10 offset3');
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
					<ul>
					<?php foreach($pkgs as $pkg) {
							$pkgRts = ApiRegister::getApiListByPackage($pkg);
							$p = Package::getByHandle($pkg);
							$pName = $p->getPackageName();
							$pDesc = $p->getPackageDescription();
							$pHandle = $p->getPackageHandle();
					?>
					<li id="<?php echo $pkg; ?>">
						<a href="javascript:void(0);" title="<?php echo $pDesc; ?>"><?php echo $pName; ?></a>
						<ul>
							<?php $urls = Loader::helper('concrete/urls');
							foreach($pkgRts as $pkgRt) { ?>

							<li id="r<?php echo $pkgRt->getID(); ?>" class="jstree<?php if ($pkgRt->isEnabled() == '1') { echo "-checked"; } ?>">
								<a class="vias" dialog-title="<?php echo t('Allowed Request Methods')?>" dialog-append-buttons="true" dialog-width="250" dialog-height="200" href="<?php echo $urls->getToolsURL('vias', C5_API_HANDLE).'?ID='.$pkgRt->getID(); ?>"><?php echo $pkgRt->getName(); ?></a>
							</li>

							<?php } ?>

						</ul>

						<?php
						}
			}
			?>
					</ul>
				</div>
   <div id="terms" style="display:none;">
   		<h3>Allowed Request Methods</td>
   		<form>
       <table width="95%" align="center">
       <tr>
       <td colspan="2"><input type="checkbox" name="methods" value="all" /> ALL</td>
       </tr>
       <tr>
       <td><input type="checkbox" name="methods" value="get" /> GET</td>
       <td><input type="checkbox" name="methods" value="post" /> POST</td>
       </tr>
       <tr>
       <td><input type="checkbox" name="methods" value="put" /> PUT</td>
       <td><input type="checkbox" name="methods" value="delete" /> DELETE</td>
       </tr>
       </table>
       </form>
   </div>
<?php }
echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();
?>