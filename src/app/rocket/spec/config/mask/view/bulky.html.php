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
	use rocket\spec\ei\manage\EiFrame;
	use rocket\spec\config\mask\model\GuiSection;
	use rocket\spec\ei\manage\gui\ui\DisplayStructure;
	use rocket\spec\ei\manage\EntryGui;
	use rocket\spec\ei\manage\EntryEiHtmlBuilder;
	use rocket\spec\ei\manage\util\model\Eiu;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$displayStructure = $view->getParam('displayStructure');
	$view->assert($displayStructure instanceof DisplayStructure);
	
	$eiu = $view->getParam('eiu');
	$view->assert($eiu instanceof Eiu);

	$entryEiHtml = new EntryEiHtmlBuilder($view, $eiu);
	
	$propertyPath = $eiu->entryGui()->getContextPropertyPath();
?>

<?php if ($view->getParam('renderForkMags', false, true) 
		&& !empty($forkMagPropertyPaths = $entryEiHtml->meta()->getForkMagPropertyPaths())): ?>
	<div class="rocket-tool-panel">
		<?php foreach ($forkMagPropertyPaths as $forkMagPropertyPath): ?>
			<?php $formHtml->magOpen('div', $propertyPath->ext($forkMagPropertyPath)) ?>
				<?php $formHtml->magLabel() ?>
				<div class="rocket-controls">
					<?php $formHtml->magField() ?>
				</div>
			<?php $formHtml->magClose() ?>
		<?php endforeach ?>
	</div>
<?php endif ?>

<div class="rocket-properties">
	<?php foreach ($displayStructure->getDisplayItems() as $displayItem): ?>
		<?php if ($displayItem->hasDisplayStructure()): ?>
			<?php $entryEiHtml->groupOpen() ?>
			<?php $guiSection = $displayItem->getGuiSection() ?>
			<div class="<?php $html->out('rocket-group-' . $guiSection->getType()) ?> 
					<?php $html->out($formHtml->meta()->hasErrors($propertyPath) ? 'rocket-has-error' : '') ?>">
				<label><?php $html->out($guiSection->getTitle()) ?></label>
				<div class="rocket-controls">
					<?php $view->import('entryEdit.html', array(
							'eiFrame' => $eiFrame, 'displayStructure' => $guiSection->getDisplayStructure(), 
							'entryGui' => $entryGui, 'renderForkMags' => false)) ?>
				</div>
			</div>
		<?php else: ?>
			<?php $entryEiHtml->openInputField('div', $displayItem->getGuiIdPath()) ?>
				<?php $entryEiHtml->label() ?>
				<?php $view->out('<div class="rocket-controls">') ?>
					<?php $entryEiHtml->field() ?>
					<?php $entryEiHtml->message() ?>
				<?php $view->out('</div>') ?>
			<?php $entryEiHtml->closeField() ?>
		<?php endif ?>
	<?php endforeach; ?>
</div>
