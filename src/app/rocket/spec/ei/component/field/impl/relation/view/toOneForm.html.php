<?php
	/*
	 * Copyright (c) 2012-2016, Hofmänner New Media.
	 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
	 *
	 * This file is part of the n2n module ROCKET.
	 *
	 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
	 * GNU Lesser General Public License as published by the Free Software Foundation, either
	 * version 2.1 of the License, or (at your option) any later version.
	 *
	 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
	 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
	 *
	 * The following people participated in this project:
	 *
	 * Andreas von Burg...........:	Architect, Lead Developer, Concept
	 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
	 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
	 */

	use n2n\web\dispatch\map\PropertyPath;
	use rocket\spec\ei\manage\util\model\EntryFormViewModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\util\uri\Url;
	use rocket\spec\ei\component\field\impl\relation\model\mag\ToOneForm;
	use rocket\spec\ei\component\field\impl\relation\model\mag\MappingForm;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$toOneForm = $formHtml->meta()->getMapValue($propertyPath)->getObject();
	$view->assert($toOneForm instanceof ToOneForm);
		
	$entryLabeler = $toOneForm->getEntryLabeler();
	
	$newMappingFormUrl = $view->getParam('newMappingFormUrl');
	$view->assert($newMappingFormUrl === null || $newMappingFormUrl instanceof Url);
	
	$newMappingFormPropertyPath = $propertyPath->ext('newMappingForm');
?>
<div class="rocket-to-one" data-mandatory="<?php $html->out($toOneForm->isMandatory()) ?>"
		data-remove-item-label="<?php $html->text('ei_impl_relation_remove_item_label', 
				array('item' => $entryLabeler->getGenericLabel())) ?>"
		data-replace-item-label="<?php $html->text('ei_impl_to_one_replace_item_label', 
				array('item' => $entryLabeler->getGenericLabel())) ?>"
		data-item-label="<?php $html->out($entryLabeler->getGenericLabel()) ?>"
		data-ei-spec-labels="<?php $html->out(json_encode($entryLabeler->getEiSpecLabels())) ?>">
		
	<?php if ($toOneForm->isSelectionModeEnabled()): ?>
		<div class="rocket-selector" 
				data-original-id-rep="<?php $html->out($toOneForm->getOriginalEntryIdRep()) ?>"
				data-identity-strings="<?php $html->out(json_encode($entryLabeler->getSelectedIdentityStrings())) ?>"
				data-overview-tools-url="<?php $html->out($view->getParam('selectOverviewToolsUrl')) ?>"
				data-select-label="<?php $html->text('common_select_label') ?>"
				data-reset-label="<?php $html->text('common_reset_label') ?>">
			<?php $formHtml->input($propertyPath->ext('selectedEntryIdRep')) ?>
		</div>
	<?php endif ?>

	<?php if ($toOneForm->isMappingFormAvailable()): ?>
		<?php $currentPropertyPath = $propertyPath->ext('currentMappingForm') ?>
		<?php $currentMappingForm = $formHtml->meta()->getMapValue($currentPropertyPath)->getObject(); ?>
		<?php $view->assert($currentMappingForm instanceof MappingForm) ?>
		<?php $formHtml->optionalObjectActivator($currentPropertyPath) ?>
		
		<?php if (null === $formHtml->meta()->getMapValue($currentPropertyPath)->getAttrs()): ?>
			<div class="rocket-current"
					data-replace-confirm-msg="<?php $html->out('Text: The current Entry will be deleted. Are you sure?')?>"
					data-replace-ok-label="<?php $html->text('common_yes_label') ?>"
					data-replace-cancel-label="<?php $html->text('common_no_label') ?>"
					data-ei-spec-id="<?php $html->out($currentMappingForm->getEntryForm()->getChosenId()) ?>"
					data-remove-item-label="<?php $html->text('ei_impl_relation_remove_item_label', 
							array('item' => $currentMappingForm->getEntryLabel())) ?>"
					data-item-label="<?php $html->out($currentMappingForm->getEntryLabel()) ?>">
					
				<?php $formHtml->optionalObjectEnabledHidden($currentPropertyPath) ?>
				<div class="rocket-to-one-content">
					<?php if ($currentMappingForm->isAccessible()): ?>
						<?php $view->import('~\spec\ei\manage\util\view\entryForm.html', 
								array('entryFormViewModel' => new EntryFormViewModel($currentPropertyPath->ext('entryForm')))) ?>
					<?php else: ?>
						<span class="rocket-inaccessible">
							<?php $html->out($currentMappingForm->getEntryLabel()) ?>
						</span>
					<?php endif ?>
				</div>
			</div>
		<?php endif ?>
	<?php endif ?>

	<?php $formHtml->optionalObjectActivator($newMappingFormPropertyPath) ?>
	<?php if ($toOneForm->isNewMappingFormAvailable()): ?>
		<div class="rocket-new" data-new-entry-form-url="<?php $html->out((string) $newMappingFormUrl) ?>"
				data-property-path="<?php $html->out((string) $formHtml->meta()->createRealPropertyPath($newMappingFormPropertyPath)) ?>"
				data-prefilled=""
				data-add-item-label="<?php $html->text('ei_impl_relation_add_item_label', 
						array('item' => $entryLabeler->getGenericLabel())) ?>">
			<?php if (null === $formHtml->meta()->getMapValue($newMappingFormPropertyPath)->getAttrs()): ?>
				<?php $formHtml->optionalObjectEnabledHidden($newMappingFormPropertyPath) ?>
				
				<?php $view->import('~\spec\ei\manage\util\view\entryForm.html', 
						array('entryFormViewModel' => new EntryFormViewModel($newMappingFormPropertyPath->ext('entryForm')))) ?>
			<?php endif ?>
		</div>
	<?php endif ?>
</div>
