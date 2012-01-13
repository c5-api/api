<?php defined('C5_EXECUTE') or die('Access Denied.');
$form = Loader::helper('form');
echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Refresh Routes'), false, 'span6 offset5');?>

	<table class="zebra-striped">
		<tr>
			<td class="subheader"><strong><?php echo t('Package');?></strong></td>
			<td></td>
		</tr>
		<?php
		$token = $this->controller->token->generate('ref_routes');
		foreach($pkgs as $pkg) {
			$pkg = Package::getByHandle($pkg);
			echo '<tr>';
				echo '<td>';
					echo $pkg->getPackageName();
				echo '</td><td style="width:20%"><a href="'.$this->action('ref', $pkg->getPackageHandle(), $token).'" class="btn info">'.t('Refresh').'</a></td>';
			echo '</tr>';
		}
		?>
	</table>
	
<?php
echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();
?>