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

	use rocket\ei\util\entry\view\EiuEntryFormViewModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\web\dispatch\map\PropertyPath;
	use rocket\ei\util\gui\EiuHtmlBuilder;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$eiuEntryFormViewModel = $view->getParam('eiuEntryFormViewModel');
	$view->assert($eiuEntryFormViewModel instanceof EiuEntryFormViewModel);
	
	$eiuHtml = new EiuHtmlBuilder($view);
	
// 	$eiuEntryFormViewModel->initFromView($view);
	
	$efPropertyPath = $eiuEntryFormViewModel->getEiuEntryForm()->getContextPropertyPath();
	if ($efPropertyPath === null) {
		$efPropertyPath = new PropertyPath(array());
	}
	$selectedTypeIdPropertyPath = $efPropertyPath->ext('chosenId');
	
	$typeChoicesMap = $eiuEntryFormViewModel->getTypeChoicesMap();
	$iconTypesMap = $eiuEntryFormViewModel->getIconTypeMap();
?>

<?php if (!$eiuEntryFormViewModel->isTypeChangable()): ?>
	<div class="rocket-entry-form" 
			data-rocket-ei-type-id="<?php $html->out(key($typeChoicesMap)) ?>"
			data-rocket-generic-label="<?php $html->out(current($typeChoicesMap)) ?>"
			data-rocket-generic-icon-type="<?php $html->out(current($iconTypesMap)) ?>">
		<?php $html->out($eiuEntryFormViewModel->createEditView($view)) ?>
	</div>
<?php else: ?>
	<?php if ($eiuEntryFormViewModel->hasDisplayContainer()): ?>
		<?php $eiuHtml->displayItemOpen('div', $eiuEntryFormViewModel->getDisplayContainerType(), 
				$eiuEntryFormViewModel->getDisplayContainerAttrs()) ?>
			<label><?php $html->out($eiuEntryFormViewModel->getDisplayContainerLabel()) ?></label>
			<div class="rocket-control">
	<?php endif ?>

	<div class="rocket-entry-form rocket-multi-ei-type">
		<div class="rocket-ei-type-selector">
			<?php $formHtml->label($selectedTypeIdPropertyPath) ?>
			<div>
				<?php $formHtml->select($selectedTypeIdPropertyPath, $eiuEntryFormViewModel->getTypeChoicesMap(),
						array('class' => 'form-control', 'data-rocket-generic-icon-types' => json_encode($iconTypesMap))) ?>
			</div>
		</div>
	
		<?php $html->out($eiuEntryFormViewModel->createEditViews($view)) ?>
	</div>
	<?php if ($eiuEntryFormViewModel->hasDisplayContainer()): ?>
			</div>
		<?php $eiuHtml->displayItemClose() ?>
	<?php endif ?>
<?php endif ?>