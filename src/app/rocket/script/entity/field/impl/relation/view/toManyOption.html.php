<?php
	use rocket\script\entity\field\impl\relation\option\ToManyForm;
	use n2n\dispatch\PropertyPath;
	use rocket\script\entity\manage\model\EntryFormViewModel;
use rocket\script\entity\EntityScript;
use rocket\script\entity\mask\ScriptMask;

	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$toManyForm = $formHtml->getValue($propertyPath)->getObject();
	$view->assert($toManyForm instanceof ToManyForm);
	
	$availableNewEntryFormNum = $toManyForm->getAvailableNewEntryFormNum();
	
	$targetScriptMask = $view->getParam('targetScriptMask');
	$view->assert($targetScriptMask instanceof ScriptMask);
	$pluralLabel = $targetScriptMask->getPluralLabel();
	$label = $targetScriptMask->getLabel();
?>

<div class="rocket-to-many">
	<?php if (null !== ($entryIdOptions = $toManyForm->getEntryIdOptions())): ?>
		<ul class="rocket-existing"	
			data-assigned-items-title="<?php $html->text('script_impl_to_many_assigned_items_title', 
					array('plural_label' => $pluralLabel)) ?>" 
			data-unassigned-items-tile="<?php $html->text('script_impl_to_many_unassigned_items_title', 
					array('plural_label' => $pluralLabel)) ?>"
			data-assign-title="<?php $html->text('script_impl_to_many_assign_title', array('label' => $label)) ?>" 
			data-unassign-title="<?php $html->text('script_impl_to_many_unassign_title', array('label' => $label)) ?>"
			data-input-filter-placeholder="<?php $html->text('script_impl_to_many_input_filter_placeholder', 
					array('plural_label' => $pluralLabel)) ?>">
			<?php foreach ($entryIdOptions as $entryId => $optionLabel): ?>
				<li><?php $formHtml->inputCheckbox($propertyPath->ext('entryIds')->fieldExt($entryId), $entryId, 
						array('class' => 'rocket-object-enabler'), $optionLabel) ?></li>
			<?php endforeach ?>
		</ul>
	<?php endif ?>
	
	<?php if ($toManyForm->areEntryFormsAvailable()): ?>
		<ul class="rocket-option-array" data-min="<?php $html->out($toManyForm->getMin()) ?>"
				 data-max="<?php $html->out($toManyForm->getMax()) ?>" 
				 data-text-remove="<?php $html->text("Remove") ?>"
				 data-text-add-item="<?php $html->text("Add Item") ?>">
			<?php $formHtml->arrayProps($propertyPath->ext('currentEntryForms'), function ($value) use ($html, $view, $formHtml, $toManyForm) { ?>
				<?php $typeName = (count($typeOptions = $value->getObject()->getTypeOptions()) === 1) ? reset($typeOptions) : null?>
				<li class="rocket-controls rocket-current" 
						<?php $view->out((null !== $typeName) ? 'data-type-name="' . $typeName . '"' : '') ?>>
					<?php /* if ($toManyForm->isCurrentUnsetAllowed()): */ ?>
						<?php $formHtml->objectOptionalCheckbox(null, array('class' => 'rocket-object-enabler'), 'Keep') ?>
					<?php /* endif */ ?>
					<?php $view->import('script\entity\manage\view\entryForm.html', 
							array('entryFormViewModel' => new EntryFormViewModel($value->getObject(), 
									$formHtml->createPropertyPath()))) ?>
				</li>
			<?php }) ?>
				
			<?php $formHtml->arrayProps($propertyPath->ext('newEntryForms'), function () use ($view, $formHtml) { ?>
				<li class="rocket-controls rocket-new">
					<?php $formHtml->objectOptionalCheckbox(null, array('class' => 'rocket-object-enabler'), 'Add') ?>
					
					<?php $view->import('script\entity\manage\view\entryForm.html', 
							array('entryFormViewModel' => new EntryFormViewModel($formHtml->getValue()->getObject(), 
									$formHtml->createPropertyPath()))) ?>
				</li>
			<?php }, $availableNewEntryFormNum) ?>
		</ul>
	<?php endif ?>
</div>