<?php
	use n2n\dispatch\PropertyPath;
	use rocket\script\entity\field\impl\relation\option\ToOneForm;
	use rocket\script\entity\manage\model\EntryFormViewModel;

	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$toOneForm = $formHtml->getValue($propertyPath)->getObject();
	$view->assert($toOneForm instanceof ToOneForm);
	
	$itemLabel = $view->getParam('itemLabel');
?>
<div class="rocket-to-one" data-required="<?php $html->out($toOneForm->isRequired()) ?>" 
		data-text-add-item="<?php $html->text('script_impl_to_one_add_item_label', array('item' => $itemLabel)) ?>" 
		data-text-replace-item="<?php $html->text('script_impl_to_one_replace_item_label', array('item' => $itemLabel)) ?>">
	<?php if (null !== ($currentEntryForm = $toOneForm->getCurrentEntryForm())): ?>
		<div class="rocket-current">
			<?php if ($toOneForm->isUnsetCurrentAllowed()): ?>
				<?php $formHtml->objectOptionalCheckbox($propertyPath->ext('currentEntryForm'), array('class' => 'rocket-object-enabler'), 'Keep') ?>
			<?php endif ?>
	
			<?php $view->import('script\entity\manage\view\entryForm.html', 
					array('entryFormViewModel' => new EntryFormViewModel($currentEntryForm, 
							$propertyPath->ext('currentEntryForm')))) ?>
		</div>
	<?php elseif (null !== ($entryIdOptions = $toOneForm->getEntryIdOptions())): ?>
		<div class="rocket-existing">
			<?php $formHtml->select($propertyPath->ext('entryId'), $entryIdOptions) ?>
		</div>
	<?php elseif ($toOneForm->hasFrozen()): ?>
		<!-- Thomas: Wie besprochen habe ich diese Checkbox nicht mehr genutzt. Ich lasse sie aber vorerst mal im HTML-Code -->
		<div class="rocket-current">
			<?php $formHtml->inputCheckbox($propertyPath->ext('keepFrozen'), true, null, $toOneForm->getFrozenLabel()) ?>
		</div>
	<?php endif ?>
	
	<?php if ($toOneForm->isNewEntryFormAvailable()): ?>	
		<div class="rocket-new">
			<?php $formHtml->objectOptionalCheckbox($propertyPath->ext('newEntryForm'),
					array('class' => 'rocket-object-enabler'), 'Add') ?>
			<?php $view->import('script\entity\manage\view\entryForm.html', 
					array('entryFormViewModel' => new EntryFormViewModel(
							$formHtml->getValue($propertyPath->ext('newEntryForm'))->getObject(), 
							$propertyPath->ext('newEntryForm')))) ?>
		</div>
	<?php endif ?>
</div>