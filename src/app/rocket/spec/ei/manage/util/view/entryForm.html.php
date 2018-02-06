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

	use rocket\spec\ei\manage\util\model\EntryFormViewModel;
	use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\map\PropertyPath;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$entryFormViewModel = $view->getParam('entryFormViewModel');
	$view->assert($entryFormViewModel instanceof EntryFormViewModel);
	
// 	$entryFormViewModel->initFromView($view);
	
	$efPropertyPath = $entryFormViewModel->getEntryForm()->getContextPropertyPath();
	if ($efPropertyPath === null) {
		$efPropertyPath = new PropertyPath(array());
	}
	$selectedTypeIdPropertyPath = $efPropertyPath->ext('chosenId');
	
	$typeChoicesMap = $entryFormViewModel->getTypeChoicesMap();
	$iconTypesMap = $entryFormViewModel->getIconTypeMap();
?>
	
<?php if (!$entryFormViewModel->isTypeChangable()): ?>
	<div class="rocket-entry-form" 
			data-rocket-ei-type-id="<?php $html->out(key($typeChoicesMap)) ?>"
			data-rocket-generic-label="<?php $html->out(current($typeChoicesMap)) ?>"
			data-rocket-generic-icon-type="<?php $html->out(current($iconTypesMap)) ?>">
		<?php $view->import($entryFormViewModel->createEditView($view))?>
	</div>
<?php else: ?>
	<div class="rocket-entry-form rocket-multi-ei-type<?php $html->out($entryFormViewModel->isGroupRequired() ? 'rocket-group rocket-simple-group' : '') ?>">
		<div class="rocket-ei-type-selector">
			<?php $formHtml->label($selectedTypeIdPropertyPath) ?>
			<div>
				<?php $formHtml->select($selectedTypeIdPropertyPath, $entryFormViewModel->getTypeChoicesMap(),
						array('class' => 'form-control', 'data-rocket-generic-icon-types' => json_encode($iconTypesMap))) ?>
			</div>
		</div>
	
		<?php foreach ($entryFormViewModel->createEditViews($view) as $id => $editView): ?>
			<div class="rocket-ei-type-entry-form rocket-ei-type-<?php $html->out($id) ?>">
				<?php $view->import($editView) ?>
			</div>
		<?php endforeach ?>
	</div>
<?php endif ?>
