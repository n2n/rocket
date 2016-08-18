<?php 
	use n2n\ui\Raw;
	use rocket\user\model\UserScriptGrantForm;

	$userScriptGrantForm = $view->getParam('userScriptGrantForm'); 
	$view->assert($userScriptGrantForm instanceof UserScriptGrantForm);
 
	$view->useTemplate('core\view\template.html', array('title' => $view->getL10nText('user_grant_title')));
?>

<?php $formHtml->open($userScriptGrantForm, 'post')?>
	<div class="rocket-panel">
		<h3><?php $html->l10nText('common_properties_title') ?></h3>
		
		<ul class="rocket-properties">
							
			<?php if ($userScriptGrantForm->areAccessOptionsAvailable()): ?>
				<li>
					<label><?php $html->l10nText('user_group_access_config_label')?></label>
					<ul class="rocket-controls">
						<?php $formHtml->objectProps('accessOptionForm', function() use ($formHtml) { ?>
							<?php $formHtml->openOption('li', null, array('class' => 'rocket-editable')) ?>
								<?php $formHtml->optionLabel() ?>
								<div class="rocket-controls">
									<?php $formHtml->optionField() ?>
								</div>
							<?php $formHtml->closeOption() ?>
						<?php }) ?>
					</ul>
				</li>
			<?php endif ?>
			
			<li class="rocket-control-group">
				<label>Privileges Grants</label>
				
				<div class="rocket-controls">
					<ul class="rocket-option-array">
						<?php $formHtml->arrayProps('userPrivilegesGrantForms', function () 
								use ($view, $html, $formHtml, $request) { ?>
							<li>
								<ul class="rocket-properties">	
									<li class="rocket-editable">
										<div class="rocket-controls">
											<?php $formHtml->objectOptionalCheckbox()  ?>
										</div>
									</li>
									
									<li class="rocket-editable">
										<label><?php $html->l10nText('user_group_privileges_label')?></label>
										<ul class="rocket-controls">
											<li><input type="checkbox" disabled="disabled" checked="checked" /><label>Read</label></li>
											<?php foreach ($formHtml->getValue()->getObject()->getPrivilegeOptions($request->getLocale()) as $key => $label): ?>
												<li>
													<?php $formHtml->inputCheckbox('privileges[' . $key . ']', $key, null, $label) ?>
												</li>
											<?php endforeach ?>
										</ul>
									</li>
									
									<?php if ($formHtml->getValue()->getObject()->areRestrictionsAvailable()): ?>
										<li class="rocket-editable">
											<?php $formHtml->label('restricted', $html->getL10nText('user_access_restricted_label')) ?>
											<div class="rocket-controls">
												<?php $formHtml->inputCheckbox('restricted') ?>
											</div>
										</li>
									
										<li>	
											<label><?php $html->l10nText('user_group_access_restrictions_label')?></label>
											<div class="rocket-controls">
												<?php $view->import('script\entity\filter\view\filterForm.html', 
														array('propertyPath' => $formHtml->createPropertyPath('restrictionFilterForm'))) ?>
											</div>
										</li>
									<?php endif ?>
								</ul>
							</li>
						<?php }, sizeof($formHtml->getValue('userPrivilegesGrantForms')) + 1) ?>
					</ul>		
				</div>
			</li>
		</ul>
	</div>
	<div id="rocket-page-controls">	
		<ul>
			<li>
				<?php $formHtml->buttonSubmit('save', new Raw('<i class="fa fa-save"></i><span>' 
								. $html->getL10nText('common_save_label') . '</span>'),
						array('class' => 'rocket-control-warning rocket-important')) ?>
			</li>
		</ul>
	</div>
<?php $formHtml->close() ?>