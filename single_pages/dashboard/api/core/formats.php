<?php defined('C5_EXECUTE') or die('Access Denied.');
	echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Manage Formats'), t('Enable and Disable different response formats and select a default format.'));
	$ih = Loader::helper('concrete/interface');
	$txt = Loader::helper('text');?>
	<form method="post" action="<?php echo $this->action('save')?>">
		<div class="clearfix">
			<h3><?php echo t('Installed Formats')?></h3>
			<table border="0" cellspacing="1" cellpadding="0" class="table table-striped">
				<thead>
					<tr>
						<th class="subheader"><?php echo t('Name')?></th>
						<th class="subheader"><?php echo t('Enabled')?></th>
						<th class="subheader"><?php echo t('Default')?></th>
					</tr>
				</thead>
				<?php if (count($list) == 0) { ?>
					<tr>
						<td colspan="3">
							<?php echo t('No Formats Found.')?>
						</td>
					</tr>
				<?php } else { 
					foreach ($list as $p) { 
						$enabled = '';
						if($p->enabled) {
							$enabled = ' checked';
						}
						$default = '';
						if($p->isDefault) {
							$default = ' checked';
						}
						?>
						<tr>
							<td><?php echo $txt->unhandle($p->handle);?></td>
							<td><input type="checkbox" name="enabled[]" value="<?php echo $p->handle;?>"<?php echo $enabled;?> /></td>
							<td><input type="radio" name="default" value="<?php echo $p->handle;?>"<?php echo $default;?> /></td>
						</tr>
					<?php }
				} ?>
			</table>
			
		</div>
		<?php echo $ih->submit(t('Save'), 'save', array(), 'primary');?>
	</form>
<?php echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();