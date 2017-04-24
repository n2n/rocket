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

	use rocket\spec\ei\manage\EiHtmlBuilder;
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\spec\ei\manage\EntryEiHtmlBuilder;
	use rocket\spec\ei\manage\ControlEiHtmlBuilder;
	use rocket\spec\config\mask\model\EntryListViewModel;
	use rocket\spec\config\mask\model\EntryGuiTree;
	use rocket\spec\config\mask\model\EiuEntryGuiTree;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$entryListViewModel = $view->getParam('entryListViewModel');
	$view->assert($entryListViewModel instanceof EntryListViewModel);
	
	$orderItems = $entryListViewModel->getDisplayStructure()->getDisplayItems();
	$eiuFrame = $entryListViewModel->getEiuFrame();
	
	$eiHtml = new EiHtmlBuilder($view, $entryListViewModel->getGuiDefinition());
	$entryEiHtml = new EntryEiHtmlBuilder($view, $eiuFrame, $entryListViewModel->getEiuEntryGuis());
	$controlEiHtml = new ControlEiHtmlBuilder($view, $eiuFrame);
	
	$entryGuiTree = $view->getParam('entryGuiTree', false);
	$view->assert($entryGuiTree === null || $entryGuiTree instanceof EiuEntryGuiTree);
?>
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<?php $eiHtml->generalEntrySelector('th') ?>
			<?php foreach ($orderItems as $orderItem): ?>
				<th><?php $eiHtml->simpleLabel($orderItem->getGuiIdPath()) ?></th>
			<?php endforeach ?>
			<th><?php $html->l10nText('common_list_tools_label') ?></th>
		</tr>
	</thead>
	<tbody class="rocket-overview-content">
		<?php while ($entryEiHtml->meta()->next()): ?>
			<?php $entryEiHtml->entryOpen('tr', array('class' => ($entryGuiTree === null ? null : ' class="rocket-tree-level-' 
					. $entryGuiTree->getLevelByIdRep($entryEiHtml->meta()->getCurrentIdRep()) . '"'))) ?>
				<?php $entryEiHtml->selector('td') ?>
				
				<?php foreach ($orderItems as $orderItem): ?>
					<?php $entryEiHtml->openOutputField('td', $orderItem->getGuiIdPath()) ?>
						<?php $entryEiHtml->field() ?>
					<?php $entryEiHtml->closeField(); ?>
				<?php endforeach ?>
				<?php $view->out('<td>') ?>
					<?php $controlEiHtml->entryGuiControlList($entryEiHtml->meta()->getCurrentEiuEntryGui(), true) ?>
				<?php $view->out('</td>') ?>
			<?php $entryEiHtml->entryClose() ?>
		<?php endwhile ?>
	</tbody>
</table>
