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

	use n2n\impl\web\ui\view\html\HtmlView;
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
	
	$eiuEntries = $view->getParam('eiuEntries');
	$view->assert(is_array($eiuEntries));
	
	$fieldEiHtml = new FieldEiHtmlBuilder($view);
?>

<div class="rocket-impl-translatable rocket-impl-translatable-<?php $html->out($view->getParam('markClassKey')) ?>"
		data-rocket-impl-languages-label="<?php $html->text('ei_impl_languages_label') ?>"
		data-rocket-impl-languages-view-tooltip="<?php $html->text('ei_impl_languages_view_tooltip') ?>"
		data-rocket-impl-visible-label="<?php $html->text('ei_impl_visible_label') ?>"
		data-rocket-impl-copy-urls="<?php $html->out(json_encode($view->getParam('copyUrlDefs'))) ?>">
	<?php foreach ($propertyPaths as $n2nLocaleId => $propertyPath): ?>
		<?php $n2nLocale = N2nLocale::create($n2nLocaleId) ?>
		<?php $hasError = $formHtml->meta()->hasErrors($propertyPath) ?>
	
		<div class="rocket-impl-translation"
				data-rocket-impl-locale-id="<?php $html->out($n2nLocaleId) ?>"
				data-rocket-impl-activate-label="<?php $html->text('ei_impl_activate_translation', array(
						'locale' => $n2nLocale->getName($view->getN2nLocale()),
						'field' => $view->getParam('label'))) ?>"
				data-rocket-impl-ei-id="<?php $html->out(isset($eiuEntries[$n2nLocaleId]) ? $eiuEntries[$n2nLocaleId]->getLiveEiId(false) : null) ?>"
				data-rocket-impl-property-path="<?php $html->out((string) $propertyPath->reduced(1)) ?>"
				data-rocket-impl-copy-tooltip="<?php $html->text('ei_impl_translation_copy_tooltip',
						array('field' => $view->getParam('label'), 'locale' => $n2nLocale->getName($view->getN2nLocale()))) ?>">
		
			<?php $fieldEiHtml->openInputField('div', $propertyPath, $fieldErrorInfos[$n2nLocaleId]) ?>
				<?php $fieldEiHtml->label(array('title' => $n2nLocale->getName($request->getN2nLocale()), 
						'class' => 'rocket-impl-locale-label'), $n2nLocale->toPrettyId()) ?>
				<div class="rocket-control">
					<?php $fieldEiHtml->field() ?>
					<?php $fieldEiHtml->message() ?>
				</div>
			<?php $fieldEiHtml->closeField() ?>
		</div>
	<?php endforeach ?>
</div>
