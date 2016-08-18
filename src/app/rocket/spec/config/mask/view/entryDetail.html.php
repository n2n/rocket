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

	use rocket\spec\ei\manage\model\EntryModel;
	use n2n\web\ui\view\impl\html\HtmlView;
	use rocket\spec\ei\manage\EiState;
	use rocket\spec\config\mask\model\GuiFieldOrder;
	use rocket\spec\ei\manage\EntryGui;
	use rocket\spec\ei\manage\EntryEiHtmlBuilder;
	use rocket\spec\ei\manage\ControlEiHtmlBuilder;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$eiState = $view->getParam('eiState');
	$view->assert($eiState instanceof EiState);
	
	$guiFieldOrder = $view->getParam('guiFieldOrder');
	$view->assert($guiFieldOrder instanceof GuiFieldOrder);
	
	$entryGui = $view->getParam('entryGui');
	$view->assert($entryGui instanceof EntryGui);
		
	$entryEiHtml = new EntryEiHtmlBuilder($view, $eiState, array($entryGui));
	$controlEiHtml = new ControlEiHtmlBuilder($view, $eiState);
?>
<div class="rocket-properties<?php $html->out($guiFieldOrder->containsAsideGroup() ? ' rocket-aside-container' : '') ?>">
	<?php foreach ($guiFieldOrder->getOrderItems() as $orderItem): ?>
		<?php if ($orderItem->isSection()): ?>
			<?php $guiSection = $orderItem->getGuiSection() ?>
			<div class="<?php $html->out(null !== ($type = $guiSection->getType()) ? 'rocket-control-group-' . $type : 'rocket-control-group') ?>">
				<label><?php $html->out($guiSection->getTitle()) ?></label>
				<div class="rocket-controls">
					<?php $view->import('entryDetail.html', array(
							'eiState' => $eiState, 'guiFieldOrder' => $guiSection->getGuiFieldOrder(), 
							'entryGui' => $entryGui)) ?>
				</div>
			</div>
		<?php else: ?>
			<?php $entryEiHtml->openOutputField('div', $orderItem->getGuiIdPath()) ?>
				<?php $entryEiHtml->label() ?>
				<?php $view->out('<div class="rocket-controls">') ?>
					<?php $entryEiHtml->field() ?>
				<?php $view->out('</div>') ?>
			<?php $entryEiHtml->closeField() ?>
		<?php endif ?>
	<?php endforeach ?>
</div>
