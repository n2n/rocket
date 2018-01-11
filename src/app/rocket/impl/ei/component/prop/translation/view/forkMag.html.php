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
	use n2n\web\dispatch\map\PropertyPath;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	$request = HtmlView::request($this);
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$n2nLocaleDefs = $view->getParam('localeDefs');
	$view->assert(is_array($n2nLocaleDefs));
?>

<div class="rocket-impl-translation-menu"
		data-rocket-impl-locale-labels="<?php $html->text('ei_impl_languages_label') ?>"
		data-active-locales-label="<?php $html->text('ei_impl_active_locales_label') ?>"
		data-standard-label="<?php $html->text('ei_impl_standard_label') ?>"
		data-translations-only-label="<?php $html->text('ei_impl_translations_only_label') ?>">
	<ul>
		<?php foreach ($n2nLocaleDefs as $n2nLocaleDef): ?>
			<li data-rocket-impl-locale-id="<?php $html->out($n2nLocaleDef->getN2nLocaleId()) ?>" 
					data-rocket-impl-mandatory="<?php $html->out($n2nLocaleDef->isMandatory()) ?>">
				<label>
					<?php $formHtml->optionalObjectCheckbox($propertyPath->fieldExt($n2nLocaleDef->getN2nLocaleId())) ?>
					<?php $html->out($n2nLocaleDef->buildLabel($request->getN2nLocale())) ?>
				</label>
			</li>
		<?php endforeach ?>
	</ul>
	<div class="rocket-impl-tooltip"><?php $html->text('ei_impl_translation_manager_tooltip') ?></div>
</div>
