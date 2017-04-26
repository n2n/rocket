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

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);

	$entryCommandViewModel = $view->params['entryCommandViewModel']; 
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
	
	$eiObjectUtils = $entryCommandViewModel->getEiuEntry();
	$linkedPreviewType = $currentPreviewType = $view->getParam('currentPreviewType', false);
	if ($linkedPreviewType === null) {
		$linkedPreviewType = $eiObjectUtils->getPreviewType();
	} 
	
	$detailPathParts = null;
	$previewPathParts = null;
	if ($eiObjectUtils->isDraft()) {
		$draftId = $eiObjectUtils->getDraft(true)->getId();
		$detailPathParts = array('draft', $draftId);
		$previewPathParts = array('draftpreview', $draftId, $linkedPreviewType);
	} else {
		$idRep = $eiObjectUtils->getLiveIdRep(true);
		$detailPathParts = array('live', $idRep);
		$previewPathParts = array('livepreview', $idRep, $linkedPreviewType);
	}
 ?>

<ul class="rocket-preview-switch">
	<li>
		<?php $html->linkToController($detailPathParts,
				new n2n\web\ui\Raw('<i class="fa fa-list"></i>' 
						. $html->getL10nText('ei_impl_entry_info_mode_label')), 
				array('class' => 'rocket-control rocket-control-dataview' 
						. ($currentPreviewType === null ? ' rocket-active' : null))) ?>
	</li>
	<li>
		<?php $html->linkToController($previewPathParts, 
				new n2n\web\ui\Raw('<i class="fa fa-eye"></i>' 
						. $html->getL10nText('ei_impl_entry_preview_mode_label')), 
				array('class' => 'rocket-control rocket-control-preview' 
						. ($currentPreviewType !== null ? ' rocket-active' : null))) ?>
	</li>
</ul>
