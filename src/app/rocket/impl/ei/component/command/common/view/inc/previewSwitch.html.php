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

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);

	$entryCommandViewModel = $view->getParam('entryCommandViewModel'); 
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
	
	$eiuEntry = $entryCommandViewModel->getEiuEntry();
	$linkedPreviewType = $currentPreviewType = $view->getParam('currentPreviewType', false);
	if ($linkedPreviewType === null) {
		$linkedPreviewType = $eiuEntry->getDefaultPreviewType();
	} 
	
	$detailPathParts = null;
	$previewPathParts = null;
	if ($eiuEntry->isDraft()) {
		$draftId = $eiuEntry->getDraft(true)->getId();
		$detailPathParts = array('draft', $draftId);
		$previewPathParts = array('draftpreview', $draftId, $linkedPreviewType);
	} else {
		$pid = $eiuEntry->getPid(true);
		$detailPathParts = array('live', $pid);
		$previewPathParts = array('livepreview', $pid, $linkedPreviewType);
	}
 ?>

<div class="rocket-impl-preview-switch">
	<?php $html->linkToController($detailPathParts,
			new n2n\web\ui\Raw('<i class="fa fa-list"></i><span>' 
					. $html->getL10nText('ei_impl_entry_info_mode_label') . ' </span>'), 
			array('class' => 'btn btn-secondary rocket-icon-impoortant rocket-jhtml' 
					. ($currentPreviewType === null ? ' rocket-active' : null))) ?>

	<?php $html->linkToController($previewPathParts, 
			new n2n\web\ui\Raw('<i class="fa fa-eye"></i><span>' 
					. $html->getL10nText('ei_impl_entry_preview_mode_label') . '</span>'), 
			array('class' => 'btn btn-secondary rocket-icon-impoortant rocket-jhtml'
					. ($currentPreviewType !== null ? ' rocket-active' : null))) ?>
</div>
