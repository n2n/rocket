<?php 
	use rocket\script\config\model\EntityScriptForm;
	use rocket\script\config\model\ConfigTemplateModel;
	use n2n\ui\Raw;

	$entityScriptForm = $view->getParam('entityScriptForm');
	$view->assert($entityScriptForm instanceof EntityScriptForm);
	
	$configForm = $view->getParam('configTemplateModel'); 
	$view->assert($configForm instanceof ConfigTemplateModel);

	$view->useTemplate('script\config\view\configTemplate.html', 
			array('configTemplateModel' => $configForm,
					'title' => $view->getL10nText('script_edit_title', 
							array('script' => $entityScriptForm->getLabel()))));
	$html->addJs('autocomplete/js/autocomplete.js', 'util\jquery');
	$html->addJs('js/script/config/script.js');
	$html->addJs('js/script/config/script.edit.js');
	$html->addJs('js/script/config/jquery-ui-1.10.1.js');
?>

<?php $formHtml->open($entityScriptForm, null, null, array('class' => 'rocket-edit-form', 'id' => 'rocket-script-edit', 
		'data-save-label' => $html->getText('common_save_label'), 
		'data-cancel-label' => $html->getText('common_cancel_label'),
		'data-delete-label' => $html->getText('common_delete_label'))) ?>
	<div class="rocket-grouped-panels">
		<section id="rocket-script-config-general" class="rocket-panel">
			<h2><?php $html->l10nText('script_general_title') ?></h2>
			
			<ul class="rocket-properties">
				<li>
					<?php $formHtml->label('label', $html->getText('common_label_label')) ?>
					<div class="rocket-controls">
						<?php $formHtml->inputField('label') ?>
					</div>
				</li>
				<li>
					<?php $formHtml->label('pluralLabel', $html->getText('script_plural_label_label')) ?>
					<div class="rocket-controls">
						<?php $formHtml->inputField('pluralLabel') ?>
					</div>
				</li>
				<li>
					<?php $formHtml->label('typeChangeMode', $html->getText('script_type_change_mode_label')) ?>
					<div class="rocket-controls">
						<?php $formHtml->select('typeChangeMode', $entityScriptForm->getTypeChangeModeOptions()) ?>
					</div>
				</li>
				<li>
					<?php $formHtml->label('draftHistorySize', $html->getText('script_history_size_label')) ?>
					<div class="rocket-controls">
						<?php $formHtml->inputField('draftHistorySize', null, 'number') ?>
					</div>
				</li>
				<li>
					<?php $formHtml->label('dataSourceName', $html->getText('script_data_source_label')) ?>
					<div class="rocket-controls">
						<?php $formHtml->select('dataSourceName', $entityScriptForm->getDataSourceNameOptions()) ?>
					</div>
				</li>
				<li class="rocket-block">
					<?php $formHtml->label('previewControllerClassName', $html->getText('script_preview_class_label')) ?>
					<div class="rocket-controls">
						<?php $formHtml->inputField('previewControllerClassName')?>
					</div>
				</li>
			</ul>
		</section>
		
		<?php $view->import('script/config/view/manage/fields.html', array('entityScriptForm' => $entityScriptForm)) ?>
		
		<?php $view->import('script/config/view/manage/commands.html', array('entityScriptForm' => $entityScriptForm)) ?>
		
		<?php $view->import('script/config/view/manage/constraints.html', array('entityScriptForm' => $entityScriptForm)) ?>
		
		<?php $view->import('script/config/view/manage/listeners.html', array('entityScriptForm' => $entityScriptForm)) ?>
	</div>
	<div id="rocket-page-controls">
		<ul>
			<li class="rocket-control">
				<?php $formHtml->buttonSubmit('save', 
								new Raw('<i class="fa fa-save"></i><span>' . $view->getL10nText('common_save_label')), 
						array('class' => 'rocket-control-warning rocket-important rocket-navigation-hash-appender-submit')) ?>
			</li>
			<li class="rocket-control">
				<?php $formHtml->buttonSubmit('saveAndGoToOverview', 
								new Raw('<i class="fa fa-save"></i><span>' . $html->getL10nText('script_save_and_go_to_overview_label') . '</span>'), 
						array('class' => 'rocket-control-warning')) ?>
			</li>
			<li class="rocket-control">
				<?php $formHtml->buttonSubmit('saveAndConfig', 
								new Raw('<i class="fa fa-save"></i><span>' . $html->getL10nText('script_save_and_config_label') . '</span>'), 
						array('class' => 'rocket-control-warning rocket-navigation-hash-appender-submit')) ?>
			</li>
		</ul>
	</div>
<?php $formHtml->close() ?>
