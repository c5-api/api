<?php defined('C5_EXECUTE') or die('Access Denied.');
	echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Manage Auth'), t('Select the default authentication method.'), 'offset4 span4');
	$ih = Loader::helper('concrete/interface');
	$txt = Loader::helper('text');?>
	<form method="post" action="<?php echo $this->action('save')?>">
		<div class="clearfix">
			<table border="0" cellspacing="1" cellpadding="0" class="table table-striped">
				<thead>
					<tr>
						<th class="subheader"><?php echo t('Name')?></th>
						<th class="subheader"><?php echo t('Default')?></th>
					</tr>
				</thead>
				<?php if (count($list) == 0) { ?>
					<tr>
						<td colspan="3">
							<?php echo t('No Authentication Types Found.')?>
						</td>
					</tr>
				<?php } else { 
					foreach ($list as $p) { 
						$enabled = '';
						if($p->enabled) {
							$enabled = ' checked';
						}
						?>
						<tr>
							<td><?php echo $txt->unhandle($p->handle);?></td>
							<td><input type="radio" name="enabled" value="<?php echo $p->handle;?>"<?php echo $enabled;?> /></td>
						</tr>
					<?php }
				} ?>
			</table>
			
		</div>
		<?php echo $ih->submit(t('Save'), 'save', array(), 'primary');?>
	</form>
<?php echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();