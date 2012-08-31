<?php defined('C5_EXECUTE') or die('Access Denied.');
	echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('API Keys'), t('Api Keys allow external programs to access the site.'));
	$ih = Loader::helper('concrete/interface');
	$valt = Loader::helper('validation/token');?>
		<div class="clearfix">
			<h3><?php echo t('Api Keys')?></h3>
			<?php echo '<a class="btn info" href="'.$this->action('generate').'/'.$valt->generate('generate').'">'.t('Generate New API Key').'</a>';?>
			<table border="0" cellspacing="1" cellpadding="0" class="table table-striped">
				<thead>
					<tr>
						<th class="subheader"><?php echo t('App ID')?></th>
						<th class="subheader"><?php echo t('Public Key')?></th>
						<th class="subheader"><?php echo t('Private Key')?></th>
						<th class="subheader"><?php echo t('Enable/Disable')?></th>
						<th class="subheader"><?php echo t('Delete')?></th>
					</tr>
				</thead>
				<?php if (count($list) == 0) { ?>
					<tr>
						<td colspan="5">
							<?php echo t('No API Keys Found.')?>
						</td>
					</tr>
				<?php } else { 
					foreach ($list as $p) { 
						if($p->active) {
							$button = '<a class="btn warning" href="'.$this->action('disable').'/'.$p->appID.'/'.$valt->generate('disable').'">'.t('Disable').'</a>';
						} else {
							$button = '<a class="btn success" href="'.$this->action('enable').'/'.$p->appID.'/'.$valt->generate('enable').'">'.t('Enable').'</a>';
						}
						$delete = '<a class="btn danger" href="'.$this->action('delete').'/'.$p->appID.'/'.$valt->generate('delete').'">'.t('Delete').'</a>';
						?>
						<tr>
							<td><?php echo $p->appID?></td>
							<td><?php echo $p->publicKey?></td>
							<td><?php echo $p->privateKey?></td>
							<td><?php echo $button?></td>
							<td><?php echo $delete?></td>

						</tr>
					<?php }
				} ?>
			</table>
			
		</div>
<?php echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();