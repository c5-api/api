<?php defined('C5_EXECUTE') or die('Access Denied.');
$valt = Loader::helper('validation/token');
$form = Loader::helper('form');
echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Enable/Disable API'), false, 'span6 offset3', false);?>

<form method="post" action="<?php echo $this->action('save_api_enable'); ?>">
	<div class="ccm-pane-body">	
		<?php echo $this->controller->token->output('save_api_enable'); ?>
		<div class="clearfix inputs-list">
			<label for="api_enable">
				<?php echo $form->checkbox('api_enable', 1, $enable) ?>
			
				<span><?php echo t('Enable/Disable the API'); ?></span>
			</label>		
		</div>
	
	</div>
	<div class="ccm-pane-footer">	
		<?php echo $interface->submit(t('Save'), 'api-form', 'right', 'primary'); ?>
	</div>
</form>
	
<?php
echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);
?>