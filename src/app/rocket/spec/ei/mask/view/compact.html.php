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
	use rocket\spec\ei\manage\gui\ui\DisplayStructure;
	use rocket\spec\ei\manage\util\model\Eiu;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$eiu = $view->getParam('eiu');
	$view->assert($eiu instanceof Eiu);
	
	$displayStructure = $view->getParam('displayStructure');
	$view->assert($displayStructure instanceof DisplayStructure);
	
	$controlsAllowed = $view->getParam('controlsAllowed');
	
	$eiHtml = new EiHtmlBuilder($view);
?>

<table class="table table-hover rocket-table">
	<thead>
		<tr>
			<?php $eiHtml->generalEntrySelector('th') ?>
			<?php foreach ($displayStructure->getDisplayItems() as $displayItem): ?>
				<th><?php $eiHtml->label($eiu, $displayItem) ?></th>
			<?php endforeach ?>
			<?php if ($controlsAllowed): ?>
				<th><?php $html->l10nText('common_list_tools_label') ?></th>
			<?php endif ?>
		</tr>
	</thead>
	<?php $eiHtml->collectionOpen('tbody', $eiu, array('rocket-collection')) ?>
		<?php foreach ($eiu->gui()->entryGuis() as $eiuEntryGui): ?>
			<?php $eiHtml->entryOpen('tr', $eiuEntryGui) ?>
				<?php $eiHtml->entrySelector('td') ?>
				
				<?php foreach ($displayStructure->getDisplayItems() as $displayItem): ?>
					<?php $eiHtml->fieldOpen('td', $displayItem) ?>
						<?php $eiHtml->fieldContent() ?>
					<?php $eiHtml->fieldClose(); ?>
				<?php endforeach ?>
				<?php if ($controlsAllowed): ?>
					<?php $view->out('<td class="rocket-table-commands">') ?>
						<?php $eiHtml->entryCommands(true, 6) ?>
					<?php $view->out('</td>') ?>
				<?php endif ?>
			<?php $eiHtml->entryClose() ?>
		<?php endforeach ?>
	<?php $eiHtml->collectionClose() ?>
</table>