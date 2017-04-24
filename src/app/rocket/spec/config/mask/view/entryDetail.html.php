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
	use rocket\spec\config\mask\model\DisplayStructure;
	use rocket\spec\ei\manage\EntryGui;
	use rocket\spec\ei\manage\EntryEiHtmlBuilder;
	use rocket\spec\ei\manage\ControlEiHtmlBuilder;
use rocket\spec\ei\manage\util\model\EiuEntryGui;
use rocket\spec\ei\manage\util\model\Eiu;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
		
	$guiPropOrder = $view->getParam('guiPropOrder');
	$view->assert($guiPropOrder instanceof DisplayStructure);
	
	$eiu = $view->getParam('eiu');
	$view->assert($eiu instanceof Eiu);
		
	$entryEiHtml = new EntryEiHtmlBuilder($view, $eiu);
?>
<div class="rocket-properties">
	<?php foreach ($guiPropOrder->getDisplayItems() as $orderItem): ?>
		<?php if ($orderItem->isSection()): ?>
			<?php $guiSection = $orderItem->getGuiSection() ?>
			<div class="<?php $html->out(null !== ($type = $guiSection->getType()) ? 'rocket-group-' . $type : 'rocket-group') ?>">
				<label><?php $html->out($guiSection->getTitle()) ?></label>
				<div class="rocket-controls">
					<?php $view->import('entryDetail.html', array(
							'eiu' => $eiu, 'guiPropOrder' => $guiSection->getDisplayStructure())) ?>
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
