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
	use rocket\ei\manage\gui\ui\DisplayStructure;
	use rocket\ei\util\gui\EiuHtmlBuilder;
	use rocket\ei\util\gui\EiuEntryGui;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$displayStructure = $view->getParam('displayStructure');
	$view->assert($displayStructure instanceof DisplayStructure);
	
	$eiuEntryGui = $view->getParam('eiuEntryGui');
	$view->assert($eiuEntryGui instanceof EiuEntryGui);

	$renderToolbar = $view->getParam('renderToolbar', false, true);
	
	$eiuHtml = new EiuHtmlBuilder($view);
	
	$entryOpen = $eiuHtml->meta()->isEntryOpen($eiuEntryGui);
	
	$renderInnerToolbar = false;
	if ($renderToolbar && $displayStructure->size() == 1 && $displayStructure->getDisplayItems()[0]->hasDisplayStructure()) {
		$renderToolbar = false; 
		$renderInnerToolbar = true;
	}
?>

<?php if (!$entryOpen): ?>
	<?php $eiuHtml->entryOpen('div', $eiuEntryGui) ?>
<?php endif ?>

<?php if ($renderToolbar): ?>
	<?php $eiuHtml->toolbar(false, $displayStructure, null) ?>
	
	<?php $eiuHtml->entryMessages() ?>
<?php else: ?>
	<?php $eiuHtml->toolbar(false, $displayStructure, false) ?>
<?php endif ?>

<div class="rocket-control">
	<?php foreach ($displayStructure->getDisplayItems() as $displayItem): ?>
		<?php if ($displayItem->hasDisplayStructure()): ?>
			<?php $eiuHtml->displayItemOpen('div', $displayItem) ?>
				<?php if (null !== ($labelLstr = $displayItem->getLabelLstr())): ?>
					<label><?php $html->out($labelLstr->t($view->getN2nLocale())) ?></label>
				<?php endif ?>
				
				<?php $view->import('bulkyEntry.html', $view->mergeParams(array(
						'displayStructure' => $displayItem->getDisplayStructure(), 
						'renderToolbar' => $renderInnerToolbar))) ?>
			<?php $eiuHtml->displayItemClose() ?>
		<?php elseif ($eiuHtml->meta()->containsGuiFieldPath($displayItem->getGuiFieldPath())): ?>
			<?php $eiuHtml->fieldOpen('div', $displayItem->getGuiFieldPath(), $displayItem->getType(), $displayItem->getAttrs()) ?>
				<?php $eiuHtml->fieldLabel() ?>
				
				<?php $eiuHtml->toolbar(true, false, false) ?>
				
				<div class="rocket-control">
					<?php $eiuHtml->fieldContent() ?>
					<?php $eiuHtml->fieldMessage() ?>
				</div>
			<?php $eiuHtml->fieldClose() ?>
		<?php endif ?>
	<?php endforeach ?>
</div>

<?php if (!$entryOpen): ?>
	<?php $eiuHtml->entryClose()?>
<?php endif ?>