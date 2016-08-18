<?php 
	use rocket\script\config\model\ScriptAddForm;
use n2n\ui\Raw;
	
	$scriptForm = $view->getParam('scriptForm'); 
	$view->assert($scriptForm instanceof ScriptAddForm);

	$view->useTemplate('core\view\template.html', 
			array('title' => $view->getL10nText('script_title')));
?>
<?php $formHtml->open($scriptForm, null, null, array('id' => 'rocket-form-script')) ?>
	<section class="rocket-panel">
		<h2><?php $html->l10nText('script_add_title')?></h2>
		
		<ul class="rocket-properties">
			<li>
				<?php $formHtml->label('id', $view->getL10nText('common_id_label')) ?>
				<div class="rocket-controls">
					<span><?php $html->text('script_id_prefix_label') ?><?php $formHtml->inputField('id') ?></span>
				</div>
			</li>
			<li>		
				<?php $formHtml->label('label', $view->getL10nText('common_label_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('label') ?>
				</div>
			</li>
			<li>		
				<?php $formHtml->label('pluralLabel', $view->getL10nText('script_plural_label_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('pluralLabel') ?>
				</div>
			</li>
			<li>
				<?php $formHtml->label('moduleNamespace', $view->getL10nText('script_module_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->select('moduleNamespace', $scriptForm->getModuleNamespaceOptions()) ?>
				</div>
			</li>
			<li>
				<?php $formHtml->label('type', $view->getL10nText('script_type_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->select('type', $scriptForm->getTypeOptions()) ?>
				</div>
			</li>
			<li class="rocket-block">
				<?php $formHtml->label('controllerClassName', $view->getL10nText('script_custom_controller_class_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('controllerClassName')?>
				</div>
			</li>
			<li class="rocket-block">
				<?php $formHtml->label('entityClassName', $view->getL10nText('script_entity_class_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('entityClassName') ?>
				</div>
			</li>
		</ul>
		
		<div id="rocket-page-controls">
			<ul>
				<li class="rocket-control">
					<?php $formHtml->buttonSubmit('save', 
							new Raw('<i class="fa fa-save"></i>' . $html->getL10nText('common_save_label')),
							array('class' => 'rocket-control-success rocket-important')) ?>
				</li>
			</ul>
		</div>
	</section>
<?php $formHtml->close() ?>
