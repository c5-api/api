<?php defined('C5_EXECUTE') or die('Access Denied.');
$form = Loader::helper('form');
echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Refresh Routes'), false, 'span6 offset5');?>
	<p><span class="label warning"><?php echo t('Warning')?></span><br/><?php echo t('Refreshing Routes will remove all settings associated with the route.')?></p>
	<table class="zebra-striped">
		<tr>
			<td class="subheader"><strong><?php echo t('Package');?></strong></td>
			<td></td>
		</tr>
		<?php
		$token = $this->controller->token->generate('ref_routes');
		foreach($pkgs as $pkg) {
			$pkg = Package::getByHandle($pkg);
			$dis = '';
			$class = 'btn info';
			$href = $this->action('ref', $pkg->getPackageHandle(), $token);
			if(!ApiRegister::canRefresh($pkg->getPackageHandle())) {
				$dis = ' disabled="disabled" title="'.t('Unable to Refresh Routes.').'"';
				$href = '#';
				$class = 'btn info disabled';
			}
			echo '<tr>';
				echo '<td>';
					echo $pkg->getPackageName();
				echo '</td><td style="width:20%"><a data-placement="above" href="'.$href.'" class="'.$class.'"'.$dis.'>'.t('Refresh').'</a></td>';
			echo '</tr>';
		}
		?>
	</table>
	
<?php
echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();
?>