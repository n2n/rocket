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

	use rocket\spec\ei\component\command\impl\common\model\EntryCommandViewModel;
	use n2n\web\ui\view\impl\html\HtmlView;
	use rocket\spec\ei\manage\ControlEiHtmlBuilder;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
		
	$entryCommandViewModel = $view->getParam('entryCommandViewModel');
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
 
	
	$controlEiHtml = new ControlEiHtmlBuilder($view, $entryCommandViewModel->getEiState());
	
	$view->useTemplate('~\core\view\template.html', array('title' => $entryCommandViewModel->getTitle()));
?>

<div class="rocket-select-view-toolbar">

</div>

 
<div class="rocket-panel">
	<h3><?php $html->l10nText('common_properties_title') ?></h3>
	
	<?php $view->import($entryCommandViewModel->createDetailView()) ?>
</div> 

<?php if ($entryCommandViewModel->hasDraftHistory()): ?>
	<?php $view->panelStart('additional') ?>
		<?php $view->import('spec\ei\component\command\impl\common\view\inc\historyNav.html', 
				array('entryCommandViewModel' => $entryCommandViewModel)) ?>
	<?php $view->panelEnd() ?>
<?php endif ?>

<div id="rocket-page-controls">
	<?php $controlEiHtml->entryControlList($entryCommandViewModel->getEntryGuiModel()) ?>
	
	<?php if ($entryCommandViewModel->isPreviewAvailable()): ?>
		<?php $view->import('inc\previewSwitch.html') ?>
	<?php endif ?>
</div>
