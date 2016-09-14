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
	use rocket\spec\ei\component\field\impl\relation\model\mag\MappingForm;
	use rocket\spec\ei\component\field\impl\relation\model\mag\ToManyForm;

	/**
	 * @var \n2n\web\ui\view\View $view
	 */
	$view = HtmlView::view($view);
	$html = HtmlView::html($view);
	$formHtml = HtmlView::formHtml($view);
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$toManyForm = $formHtml->meta()->getMapValue($propertyPath)->getObject();
	$view->assert($toManyForm instanceof ToManyForm);
	
	$entryLabeler = $toManyForm->getEntryLabeler();
	
	$newMappingFormUrl = $view->getParam('newMappingFormUrl');
	$view->assert($newMappingFormUrl === null || $newMappingFormUrl instanceof Url);
?>
<div class="rocket-to-many" data-min="<?php $html->out($toManyForm->getMin()) ?>"
		data-max="<?php $html->out($toManyForm->getMax()) ?>"
		data-remove-item-label="<?php $html->text('ei_impl_relation_remove_item_label', 
				array('item' => $entryLabeler->getGenericLabel())) ?>"
		data-move-up-label="<?php $html->text('common_move_up_label') ?>"
		data-move-down-label="<?php $html->text('common_move_down_label') ?>"
		data-item-label="<?php $html->out($entryLabeler->getGenericLabel()) ?>"
		data-ei-spec-labels="<?php $html->out(json_encode($entryLabeler->getEiSpecLabels())) ?>">
	<?php if (count($toManyForm->getCurrentMappingForms()) > 0): ?>
			
		<div class="rocket-option-array rocket-current">
			<?php $formHtml->meta()->arrayProps($propertyPath->ext('currentMappingForms'), function () use ($view, $html, $formHtml) { ?>
				<?php $currentMappingForm = $formHtml->meta()->getMapValue()->getObject(); ?>
				<?php $view->assert($currentMappingForm instanceof MappingForm) ?>
			
				<div class="rocket-current" 
						data-item-label="<?php $html->out($currentMappingForm->getEntryLabel()) ?>"
						data-remove-item-label="<?php $html->text('ei_impl_relation_remove_item_label', 
								array('item' => $currentMappingForm->getEntryLabel())) ?>">
					<?php $formHtml->optionalObjectEnabledHidden() ?>
					
					<?php if ($currentMappingForm->isAccessible()): ?>
						<?php $view->import('~\spec\ei\manage\util\view\entryForm.html', array('entryFormViewModel' 
								=> new EntryFormViewModel($formHtml->meta()->propPath('entryForm')))) ?>
					<?php else: ?>
						<span class="rocket-inaccessible">
							<?php $html->out($currentMappingForm->getEntryLabel()) ?>
						</span>
					<?php endif ?>
					
					<?php $formHtml->input('orderIndex', array('class' => 'rocket-to-many-order-index')) ?>
				</div>
			<?php }) ?>
		</div>
	<?php endif ?>
		
	<?php if ($toManyForm->isSelectionModeEnabled()): ?>
		<div class="rocket-selector"
				data-original-id-reps="<?php $html->out(json_encode($toManyForm->getOriginalEntryIdReps())) ?>"
				data-identity-strings="<?php $html->out(json_encode($entryLabeler->getSelectedIdentityStrings())) ?>"
				data-overview-tools-url="<?php $html->out($view->getParam('selectOverviewToolsUrl')) ?>"
				data-add-label="<?php $html->text('common_add_label') ?>"
				data-reset-label="<?php $html->text('common_reset_label') ?>"
				data-clear-label="<?php $html->text('common_clear_label') ?>"
				data-generic-entry-label="<?php $html->out($entryLabeler->getGenericLabel()) ?>"
				data-base-property-name="<?php $html->out($formHtml->meta()->getForm()->getDispatchTargetEncoder()
						->buildValueParamName($propertyPath->ext('selectedEntryIdReps'), false))?>">
			<ul>
				<?php $formHtml->meta()->arrayProps($propertyPath->ext('selectedEntryIdReps'), function () use ($formHtml, $propertyPath) { ?> 
					<li><?php $formHtml->input($propertyPath->ext('selectedEntryIdReps[]')) ?></li>
				<?php }, null, null, true) ?>
				<li class="rocket-new-entry"><?php $formHtml->input($propertyPath->ext('selectedEntryIdReps[]')) ?></li>
			</ul>
		</div>
	<?php endif ?>
	
	<?php if ($toManyForm->isNewMappingFormAvailable()): ?>
		<div class="rocket-option-array rocket-new"
				data-new-entry-form-url="<?php $html->out((string) $newMappingFormUrl) ?>"
				data-property-path="<?php $html->out($formHtml->meta()
						->createRealPropertyPath($propertyPath->ext('newMappingForms'))) ?>"
				data-add-item-label="<?php $html->text('ei_impl_relation_add_item_label', 
						array('item' => $entryLabeler->getGenericLabel())) ?>">
			<?php $formHtml->meta()->arrayProps($propertyPath->ext('newMappingForms'), function () use ($html, $formHtml, $view) { ?>
				<div class="rocket-new">
					<?php $formHtml->optionalObjectEnabledHidden() ?>
					<?php $view->import('~\spec\ei\manage\util\view\entryForm.html', 
							array('entryFormViewModel' => new EntryFormViewModel($formHtml->meta()->propPath('entryForm')))) ?>
					<?php $formHtml->input('orderIndex', array('class' => 'rocket-to-many-order-index')) ?>
				</div>
			<?php }) ?>
		</div>
	<?php endif ?>
</div>
