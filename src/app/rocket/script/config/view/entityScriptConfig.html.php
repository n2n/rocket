<?php 
	use rocket\script\config\model\EntityScriptConfigForm;
	use rocket\script\config\model\ConfigTemplateModel;
	use n2n\ui\Raw;

	$configForm = $view->getParam('configForm');
	$view->assert($configForm instanceof EntityScriptConfigForm);
	
	$configTemplateModel = $view->getParam('configTemplateModel'); 
	$view->assert($configTemplateModel instanceof ConfigTemplateModel);
	
	$view->useTemplate('script\config\view\configTemplate.html', 
			array('configTemplateModel' => $configTemplateModel, 'title' => $view->getL10nText('script_config_title', 
					array('script' => $configForm->getEntityScript()->getLabel()))));
	$html->addJs('js/script/config/script.config.js');
?>
<?php $formHtml->open($configForm, null, null, array('id' => 'rocket-script-config')) ?>
	<div class="rocket-grouped-panels">	
		<section id="rocket-script-config-general" class="rocket-panel">
			<h2><?php $html->text('script_general_title') ?></h2>
			<?php $view->import('script\config\view\config\general.html',
					array('configForm' => $configForm)) ?>
		</section>
			
		<section id="rocket-script-config-fields">
			<h2><?php $html->l10nText('script_fields_title') ?></h2>
			<?php $view->import('script\config\view\config\configurable.html', 
					array('propertyName' => 'fieldConfigModels')) ?>
		</section>
			
		<section id="rocket-script-config-commands">
			<h2><?php $html->l10nText('script_commands_title') ?></h2>
			<?php $view->import('script\config\view\config\configurable.html', 
					array('propertyName' => 'commandConfigModels')) ?>
		</section>
		
		<section id="rocket-script-config-constraints">
			<h2><?php $html->l10nText('script_constraints_title') ?></h2>
			<?php $view->import('script\config\view\config\configurable.html', 
					array('propertyName' => 'constraintConfigModels')) ?>
		</section>
		
		<section id="rocket-script-config-listeners">
			<h2><?php $html->l10nText('script_listeners_title') ?></h2>
			<?php $view->import('script\config\view\config\configurable.html', 
					array('propertyName' => 'listenerConfigModels')) ?>
		</section>
	</div>
	
	<div id="rocket-page-controls">
		<ul>
			<li>
				<?php $formHtml->buttonSubmit('save', 
						new Raw('<i class="fa fa-save"></i>' . $html->getL10nText('common_save_label')),
						array('class' => 'rocket-control-warning rocket-important rocket-navigation-hash-appender-submit')) ?>
			</li>
			<li>
				<?php $formHtml->buttonSubmit('saveAndGoToOverview', 
						new Raw('<i class="fa fa-save"></i>' . $html->getL10nText('script_save_and_go_to_overview_label')),
						array('class' => 'rocket-control-warning rocket-navigation-hash-appender-submit')) ?>
			</li>
			<li>
				<?php $formHtml->buttonSubmit('saveAndBack', 
						new Raw('<i class="fa fa-save"></i>' . $html->getL10nText('script_save_and_edit_label')), 
						array('class' => 'rocket-control-warning rocket-navigation-hash-appender-submit')) ?>
			</li>
			<li>
				<?php $html->linkToController(array('mask', $configForm->getEntityScript()->getId()), 
						new Raw('<i class="fa fa-plus-circle"></i>' . $html->getL10nText('script_add_mask_label')),
						array('class' => 'rocket-control-success')) ?>
			</li>
		</ul>
	</div>
<?php $formHtml->close() ?>
