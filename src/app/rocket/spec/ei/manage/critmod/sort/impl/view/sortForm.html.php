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
	use rocket\spec\ei\manage\critmod\sort\impl\form\SortForm;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\persistence\orm\criteria\Criteria;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	$request = HtmlView::request($this);
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
		
	$sortForm = $formHtml->meta()->getMapValue($propertyPath)->getObject();
	$view->assert($sortForm instanceof SortForm);
	
	$sortFieldIdOptions = array();
	foreach ($sortForm->getSortDefinition()->getSortFields() as $id => $sortField) {
		$sortFieldIdOptions[$id] = $sortField->getLabel($view->getN2nLocale());
	}
	
	$directionsOptions = array(
			Criteria::ORDER_DIRECTION_ASC => $view->getL10nText('ei_sort_asc_label'),
			Criteria::ORDER_DIRECTION_DESC => $view->getL10nText('ei_sort_desc_label'));
?>

<ul class="nav rocket-sort" data-add-sort-label="<?php $html->l10nText('ei_impl_add_sort_label') ?>"
		data-sort-fields="<?php $html->out(json_encode($sortFieldIdOptions)) ?>">
	<?php foreach ($formHtml->meta()->getMapValue($propertyPath->ext('directions')) as $key => $direction): ?>
		<li class="nav-item">
			<?php $formHtml->select($propertyPath->ext('sortFieldIds')->fieldExt($key), $sortFieldIdOptions, array('class' => 'form-control')) ?>
			<?php $formHtml->select($propertyPath->ext('directions')->fieldExt($key), $directionsOptions, array('class' => 'form-control')) ?>
		</li>
	<?php endforeach ?>
	<li class="nav-item rocket-empty-sort-constraint">
		<?php $formHtml->select($propertyPath->ext('sortFieldIds[]'), $sortFieldIdOptions, array('class' => 'form-control')) ?>
		<?php $formHtml->select($propertyPath->ext('directions[]'), $directionsOptions, array('class' => 'form-control')) ?>
	</li>
</ul>
