<?php 
	use rocket\module\model\ScriptElementsManageForm;
use n2n\ui\Raw;

	$configForm = $view->getParam('configForm');
	$view->assert($configForm instanceof ScriptElementsManageForm);

	$view->useTemplate('core\view\template.html', 
			array('title' => $view->getL10nText('module_manage_script_elements_title')));
	$module = $configForm->getModule();
?>
<?php $formHtml->open($configForm, null, null) ?>
	<div class="rocket-grouped-panels">
		<section class="rocket-panel">
			<h2><?php $html->text('module_field_classes_title')?></h2>
		
			<ul>
				<?php $formHtml->arrayProps('scriptFieldClassNames', function() use ($formHtml) { ?>
					<li>
						<?php $formHtml->inputField() ?>
					</li>
				<?php }) ?>
				<li><?php $formHtml->inputField('scriptFieldClassNames[]')?></li>
			</ul>
		</section>
		
		<section class="rocket-panel">
			<h2><?php $html->text('module_command_classes_title')?></h2>
			
			<ul>
				<?php $formHtml->arrayProps('scriptCommandClassNames', function() use ($formHtml) { ?>
					<li>
						<?php $formHtml->inputField(null) ?>
					</li>
				<?php }) ?>
				<li><?php $formHtml->inputField('scriptCommandClassNames[]')?></li>
			</ul>
		</section>	
		
		<section class="rocket-panel">
			<h2><?php $html->text('module_command_class_groups_title')?></h2>
			
			<ul>
				<?php $formHtml->arrayProps('scriptCommandGroupModels', function() use ($formHtml, $html) { ?>
					<li>
						<ul class="rocket-properties">
							<li>
								<label><?php $html->text('common_name_label') ?></label>
								<div class="rocket-controls">
									<?php $formHtml->inputField('name') ?>
								</div>
							</li>
							<li class="rocket-control-group">
								<label><?php $html->text('module_command_classes_title') ?></label>
								<div class="rocket-controls">
									<ul>
										<?php $formHtml->arrayProps('scriptCommandClassNames', function() use ($formHtml) { ?>
											<li>
												<?php $formHtml->inputField(null) ?>
											</li>
										<?php }) ?>
										<li><?php $formHtml->inputField('scriptCommandClassNames[]')?></li>
									</ul>
								</div>
							</li>
						</ul>
						<ul class="rocket-edit-content-entries">
							
						</ul>
					</li>
				<?php }, sizeof($formHtml->getValue('scriptCommandGroupModels')) + 1) ?>
			</ul>
		</section>
		
		<section class="rocket-panel">
			<h2><?php $html->text('module_constraint_classes_title')?></h2>
			
			<ul>
				<?php $formHtml->arrayProps('scriptModificatorClassNames', function() use ($formHtml) { ?>
					<li class="rocket-config-list-item">
						<?php $formHtml->inputField(null) ?>
					</li>
				<?php }) ?>
				<li><?php $formHtml->inputField('scriptModificatorClassNames[]')?></li>
			</ul>
		</section>	
		
		<section class="rocket-panel">
			<h2><?php $html->text('module_listener_classes_title')?></h2>
			
			<ul>
				<?php $formHtml->arrayProps('scriptListenerClassNames', function() use ($formHtml) { ?>
					<li class="rocket-config-list-item">
						<?php $formHtml->inputField(null) ?>
					</li>
				<?php }) ?>
				<li><?php $formHtml->inputField('scriptListenerClassNames[]')?></li>
			</ul>
		</section>	
	</div>
	
	<div id="rocket-page-controls">
		<ul>
			<li>
				<?php $formHtml->buttonSubmit('save' , new Raw('<i class="fa fa-save"></i>' 
								. $html->getL10nText('common_save_label')), 
						array('class' => 'rocket-control-warning rocket-important')) ?>
			</li>
			<li>
				<?php $html->linkToController(null, new Raw('<i class="fa fa-times-circle"></i>' 
						. $html->getL10nText('common_cancel_label')), array('class' => 'rocket-control')) ?>
			</li>
		</ul>
	</div>
<?php $formHtml->close() ?>