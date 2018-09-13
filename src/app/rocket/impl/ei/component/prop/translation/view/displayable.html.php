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
	use rocket\ei\manage\FieldEiHtmlBuilder;
	use rocket\ei\manage\entry\EiFieldValidationResult;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$request = HtmlView::request($this);

	$displayables = $view->getParam('displayables');
	$label = $view->getParam('label');
	$localeDefs = $view->getParam('localeDefs');
	
	$fieldEiHtml = new FieldEiHtmlBuilder($view);
?>

<div class="rocket-impl-translatable" 
		data-rocket-impl-languages-label="<?php $html->text('ei_impl_languages_label') ?>"
		data-rocket-impl-languages-view-tooltip="<?php $html->text('ei_impl_languages_view_tooltip') ?>"
		data-rocket-impl-visible-label="<?php $html->text('ei_impl_visible_label') ?>">
	<?php foreach ($displayables as $n2nLocaleId => $displayable): ?>
		<?php $n2nLocale = N2nLocale::create($n2nLocaleId) ?>
				
		<?php $fieldEiHtml->openOutputField('div', $displayable, new EiFieldValidationResult(null),  array(
				'class' => 'rocket-impl-translation', 
				'data-rocket-impl-locale-id' => $n2nLocaleId,
				'data-rocket-impl-activate-label' => $view->getL10nText('ei_impl_activate_translation', array(
						'locale' => $localeDefs[(string) $n2nLocale]->buildLabel($request->getN2nLocale()),
						'field' => $label)))) ?>
			<?php $fieldEiHtml->label(
					array('title' => $n2nLocale->getName($request->getN2nLocale()), 'class' => 'rocket-impl-locale-label'), 
					$n2nLocale->toPrettyId()) ?>
			<div class="rocket-control">
				<?php $fieldEiHtml->field() ?>
				<?php $fieldEiHtml->message() ?>
			</div>
		<?php $fieldEiHtml->closeField() ?>
	<?php endforeach ?>
</div>
