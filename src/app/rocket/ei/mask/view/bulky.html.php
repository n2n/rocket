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
	use rocket\ei\util\Eiu;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$displayStructure = $view->getParam('displayStructure');
	$view->assert($displayStructure instanceof DisplayStructure);
	
	$eiu = $view->getParam('eiu');
	$view->assert($eiu instanceof Eiu);

	$eiuGui = $eiu->gui();
	
	$eiuHtml = new EiuHtmlBuilder($view);
	if (!$eiuHtml->meta()->isEntryOpen()) {
		$displayStructure = $displayStructure->groupedItems();
	}
?>

<?php if ($eiuGui->isSingle()): ?>
	<?php $view->import('bulkyEntry.html', [ 'eiuEntryGui' => $eiuGui->entryGui(), 'displayStructure' => $displayStructure ]) ?>
<?php else: ?>
	<?php $eiuHtml->collectionOpen('div', $eiuGui) ?>
		<?php foreach ($eiuGui->entryGuis() as $eiuEntryGui): ?>
			<?php $view->import('bulkyEntry.html', 
					[ 'eiuEntryGui' => $eiuEntryGui, 'displayStructure' => $displayStructure ]) ?>
		<?php endforeach ?>
	<?php $eiuHtml->collectionClose() ?>
<?php endif ?>