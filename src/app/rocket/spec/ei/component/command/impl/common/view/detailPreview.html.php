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
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\spec\ei\manage\ControlEiHtmlBuilder;
use n2n\util\uri\Path;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$entryCommandViewModel = $view->getParam('entryCommandViewModel');
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
	
	$controlEiHtml = new ControlEiHtmlBuilder($view, $entryCommandViewModel->getEiFrame());
	
	$view->useTemplate('~\core\view\template.html',
			array('title' => $entryCommandViewModel->getTitle(), 'tmplMode' => 'rocket-preview'));
	
	$eiObjectUtils = $entryCommandViewModel->getEiuEntry();
	$currentPreviewType = $view->getParam('currentPreviewType');
	
	$previewPath = null;
	if ($eiObjectUtils->isDraft()) {
		$previewPath = new Path(array('draftpreview', $eiObjectUtils->getDraft()->getId()));
	} else {
		$previewPath = new Path(array('livepreview', $eiObjectUtils->getLiveIdRep()));
	}
?>

	
<div class="rocket-panel">
	<h3 class="rocket-preview-iframe-title">Detail</h3>
	
	<div class="rocket-context-toolbar">
		<select onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
			<?php foreach ($eiObjectUtils->getPreviewTypeOptions() as $previewType => $label): ?>
				<option value="<?php $html->out($html->meta()->getControllerUrl($previewPath->ext($previewType))) ?>"
						<?php $view->out($currentPreviewType == $previewType ? ' selected="selected"' : '') ?>>
					<?php $html->out($label) ?>
				</option>	
			<?php endforeach ?>
		</select>
	</div>

	<div class="rocket-detail-content rocket-preview-wrapper">
		<iframe src="<?php $html->esc($view->getParam('iframeSrc')) ?>" id="rocket-preview-content"></iframe>
	</div>
</div>

<div class="rocket-context-commands">
	<?php //$controlEiHtml->entryGuiControlList($entryCommandViewModel->getEntryGuiModel()) ?>
	
	<?php if ($entryCommandViewModel->isPreviewAvailable()): ?>
		<?php $view->import('inc\previewSwitch.html', array('entryCommandViewModel' => $entryCommandViewModel)) ?>
	<?php endif ?>
</div>
