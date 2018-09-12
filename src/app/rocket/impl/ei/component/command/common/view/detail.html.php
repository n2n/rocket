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

	use rocket\impl\ei\component\command\common\model\EntryCommandViewModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\ei\util\gui\EiuHtmlBuilder;
	use rocket\ei\util\gui\EiuEntryGui;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
		
	$entryCommandViewModel = $view->getParam('entryCommandViewModel');
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);

	$eiuEntryGui = $view->getParam('eiuEntryGui');
	$view->assert($eiuEntryGui instanceof EiuEntryGui);
 
	$eiuHtml = new EiuHtmlBuilder($view);
	
	$view->useTemplate('~\core\view\template.html', 
			array('title' => $entryCommandViewModel->getTitle()));
?>
 
<?php $eiuHtml->entryOpen('div', $eiuEntryGui)?>
	<?php $view->out($eiuEntryGui->createView($view)) ?>

	<div class="rocket-zone-commands">
		<?php $eiuHtml->entryCommands() ?>
	
		<div class="rocket-aside-commands">
			<?php if ($entryCommandViewModel->isPreviewAvailable()): ?>
				<?php $view->import('inc\previewSwitch.html', $view->getParams()) ?>
			<?php endif ?>
		</div>
	</div>
<?php $eiuHtml->entryClose() ?>
 

<?php if ($entryCommandViewModel->hasDraftHistory()): ?>
	<?php $view->panelStart('additional') ?>
		<?php $view->import('inc\historyNav.html', 
				array('entryCommandViewModel' => $entryCommandViewModel)) ?>
	<?php $view->panelEnd() ?>
<?php endif ?>

