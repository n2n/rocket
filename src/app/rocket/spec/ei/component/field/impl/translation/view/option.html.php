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

	use n2n\ui\view\impl\html\HtmlView;
	use n2n\l10n\N2nLocale;
	use rocket\spec\ei\manage\FieldEiHtmlBuilder;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	$request = HtmlView::request($this);

	$propertyPaths = $view->getParam('propertyPaths');
	$view->assert(is_array($propertyPaths));
	
	$fieldErrorInfos = $view->getParam('fieldErrorInfos');
	$view->assert(is_array($fieldErrorInfos));
	
	$fieldEiHtml = new FieldEiHtmlBuilder($view);
?>

<div class="rocket-properties rocket-translatable-content"
		data-languages-label="<?php $html->text('ei_impl_languages_label') ?>">
	<?php foreach ($propertyPaths as $n2nLocaleId => $propertyPath): ?>
		<?php $n2nLocale = N2nLocale::create($n2nLocaleId) ?>
		<?php $hasError = $formHtml->meta()->hasErrors($propertyPath) ?>
	
		<?php $fieldEiHtml->openInputField('div', $propertyPath, $fieldErrorInfos[$n2nLocaleId], 
				array('data-locale-id' => $n2nLocaleId, 
						'data-pretty-locale-id' => $n2nLocale->toPrettyId())) ?>
			<?php $fieldEiHtml->label(array('title' => $n2nLocale->getName($request->getN2nLocale()), 
					'class' => 'rocket-locale-label'), $n2nLocale->toPrettyId()) ?>
			<div class="rocket-controls rocket-locale-controls">
				<?php $fieldEiHtml->field() ?>
				<?php $fieldEiHtml->message() ?>
			</div>
		<?php $fieldEiHtml->closeField() ?>
	<?php endforeach ?>
</div>
