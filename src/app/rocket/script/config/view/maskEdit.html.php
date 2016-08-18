<?php 
	use rocket\script\config\model\ScriptMaskForm;
	use rocket\script\entity\command\control\IconType;
use n2n\ui\Raw;
	
	$maskForm = $view->getParam('maskForm'); 
	$view->assert($maskForm instanceof ScriptMaskForm);

	$view->useTemplate('core\view\template.html',
			array('title' => $view->getL10nText('script_masks_title')));
	
	$html->addJs('js/script/config/script.js');
	$html->addJs('js/script/config/mask.js');
	$html->addJs('js/script/config/jquery-ui-1.10.1.js');
	
	$dataFieldAttrs = $maskForm->getFieldDataAttrs();
	$numFields = sizeof($dataFieldAttrs);
?>
<?php $formHtml->open($maskForm, null, null, array('class' => 'rocket-edit-form', 'id' => 'rocket-script-edit')) ?>
	<div class="rocket-grouped-panels">
		<section class="rocket-panel">
			<h2><?php $html->l10nText('script_general_title') ?></h2>
			
			<ul class="rocket-properties">
				<li class="rocket-required">
					<?php $formHtml->label('id') ?>
					<div class="rocket-controls">
						<?php $formHtml->inputField('id') ?>
					</div>
				</li>
				<li title="Leave empty to use label of EntityScript">
					<?php $formHtml->label('label') ?>
					<div class="rocket-controls">
						<?php $formHtml->inputField('label') ?>
					</div>
				</li>
				<li title="Leave empty to use plural label of EntityScript">
					<?php $formHtml->label('pluralLabel') ?>
					<div class="rocket-controls">
						<?php $formHtml->inputField('pluralLabel') ?>
					</div>
				</li>
				<li>
					<?php $formHtml->label('draftDisabled') ?>
					<div class="rocket-controls">
						<?php $formHtml->inputCheckbox('draftDisabled') ?>
					</div>
				</li>
				<li>
					<?php $formHtml->label('translationDisabled') ?>
					<div class="rocket-controls">
						<?php $formHtml->inputCheckbox('translationDisabled') ?>
					</div>
				</li>
				<li>
					<?php $formHtml->label('defaultSort') ?>
					<div class="rocket-controls">
						<?php $formHtml->inputCheckbox('defaultSortEnabled', true, null, 'Enabled')?>
						<?php $view->import('script\entity\filter\view\sortForm.html', 
								array('propertyPath' => $formHtml->createPropertyPath('defaultSort'))) ?>
					</div>
				</li>
			</ul>
		</section>
		
		<section id="rocket-script-mask-fields" 
				data-field-attrs="<?php $html->out(json_encode($dataFieldAttrs)) ?>">
			<h2><?php $html->text('script_field_order_title') ?></h2>
			<div class="rocket-panel">
				<h3><?php $html->l10nText('script_list_field_order_title') ?></h3>
				<?php $view->import('script\config\view\config\fieldOrder.html', 
						array('propertyPath' => $formHtml->createPropertyPath('listFieldOrderForm'),
								'numFields' => $numFields)) ?>
			</div>
			<div class="rocket-panel">
				<h3><?php $html->l10nText('script_entry_field_order_title') ?></h3>
				<?php $view->import('script\config\view\config\fieldOrder.html', 
						array('propertyPath' => $formHtml->createPropertyPath('entryFieldOrderForm'),
								'numFields' => $numFields)) ?>
			</div>
			<div class="rocket-panel">
				<h3><?php $html->l10nText('script_detail_field_order_title') ?></h3>
				<?php $view->import('script\config\view\config\fieldOrder.html', 
						array('propertyPath' => $formHtml->createPropertyPath('detailFieldOrderForm'),
								'numFields' => $numFields)) ?>
			</div>
			<div class="rocket-panel">
				<h3><?php $html->l10nText('script_edit_field_order_title') ?></h3>
				<?php $view->import('script\config\view\config\fieldOrder.html', 
						array('propertyPath' => $formHtml->createPropertyPath('editFieldOrderForm'),
								'numFields' => $numFields)) ?>
			</div>
			<div class="rocket-panel">
				<h3><?php $html->l10nText('script_add_field_order_title') ?></h3>
				<?php $view->import('script\config\view\config\fieldOrder.html', 
						array('propertyPath' => $formHtml->createPropertyPath('addFieldOrderForm'),
								'numFields' => $numFields)) ?>
			</div>
		</section>
		
		<section class="rocket-panel">
			<h2><?php $html->l10nText('script_commands_title') ?></h2>
			
			<ul class="rocket-properties">
				<li>
					<?php $formHtml->label('commandsRestricted') ?>
					<div class="rocket-controls">
						<?php $formHtml->inputCheckbox('commandsRestricted') ?>
					</div>
				</li>
				<li>
					<?php $formHtml->label('commandIds', $html->getText('script_commands_label')) ?>
					<div class="rocket-controls">
						<ul>
							<li>
								<?php foreach ($maskForm->getCommandIdOptions() as $id => $label): ?>
									<?php $formHtml->inputCheckbox('commandIds[' . $id . ']', $id, null, $label) ?>
								<?php endforeach ?>
							</li>
						</ul>
					</div>
				</li>
			</ul>
		</section>
		<section id="rocket-script-mask-controls">
			<h2><?php $html->l10nText('script_control_order_title') ?></h2>
			<?php $view->import('script\config\view\config\control.html', 
					array('maskForm' => $maskForm)) ?>
		</section>
		<section id="rocket-script-mask-filter" class="rocket-panel">
			<h2><?php $html->l10nText('script_filter_title') ?></h2>
			<?php $view->import('script\entity\filter\view\filterForm.html', 
					array('propertyPath' => $formHtml->createPropertyPath('filterForm'))) ?>
		</section>
	</div>

	<div id="rocket-page-controls">
		<ul>
			<li>
				<?php $formHtml->buttonSubmit('save', 
						new Raw('<i class="fa fa-save"></i>' . $html->getL10nText('common_save_label')), 
						array('class' => 'rocket-control-warning rocket-important rocket-navigation-hash-appender-submit')) ?>
			</li>
		</ul>
	</div>
<?php $formHtml->close() ?>