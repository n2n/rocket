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

	use rocket\spec\ei\component\command\impl\common\model\EditModel;
	use rocket\spec\ei\manage\EiHtmlBuilder;
	use rocket\spec\ei\manage\EntryViewInfo;
	use n2n\impl\web\ui\view\html\HtmlView;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	
	$editModel = $view->params['commandEditEntryModel'];
	$view->assert($editModel instanceof EditModel);
	
	$entryCommandViewModel = $view->params['entryViewInfo'];
	$view->assert($entryCommandViewModel instanceof EntryViewInfo);
 
	$entryHtml = new EiHtmlBuilder($view, $entryCommandViewModel->getEiFrame());
	
	$view->useTemplate('~\core\view\template.html',
			array('title' => $entryCommandViewModel->getTitle(), 'tmplMode' => 'rocket-preview'));
	$html->meta()->addJs('js/preview.js');
	
	$currentHistoryDraft = $editModel->getEiEntry()->getDraft();
?>
<!-- 
<div class="rocket-select-view-toolbar">
	<input type="text" id="rocket-preview-draft-name-input"/>

	<?php if ($editModel->isTranslatable()): ?>
		<?php $view->import('spec\ei\component\command\impl\common\view\inc\langNav.html', array('entryViewInfo' => $entryCommandViewModel)) ?>
	<?php endif ?>
	
	<?php if ($entryCommandViewModel->hasPreviewTypeNav()): ?>
		<?php $view->import('spec\ei\component\command\impl\common\view\inc\previewTypeNav.html', array('entryViewInfo' => $entryCommandViewModel)) ?>
	<?php endif ?>
</div>
 -->
 
<div class="rocket-panel">
	<h3>Detail</h3>
	<div class="rocket-edit-content rocket-preview-wrapper">
		<div id="rocket-preview-messages"></div>
		<iframe src="<?php $html->esc($view->params['iframeSrc']) ?>" id="rocket-preview-content"></iframe>
	</div>
	
	<div class="rocket-context-commands">
		<button type="button" id="rocket-preview-save-command"
				data-rocket-confirm-msg="<?php $html->l10nText('ei_impl_edit_publish_draft_confirm_message') ?>"
				data-rocket-confirm-ok-label="<?php $html->l10nText('common_yes_label') ?>"
				data-rocket-confirm-cancel-label="<?php $html->l10nText('common_no_label') ?>">
			<?php $html->l10nText('common_save_label') ?>
		</button>
		<?php $html->link($entryCommandViewModel->getCancelPath($request), $view->getL10nText('common_cancel_label')) ?>
	
		<?php if ($entryCommandViewModel->hasPreviewSwitch()): ?>
			<?php $view->import('spec\ei\component\command\impl\common\view\inc\previewSwitch.html', array('entryViewInfo' => $entryCommandViewModel)) ?>
		<?php endif ?>
	</div>
</div>

<?php if ($editModel->isDraftable()): ?>
	<?php $view->panelStart('additional') ?>
		<?php $view->import('spec\ei\component\command\impl\common\view\inc\historyNav.html', array('entryViewInfo' => $entryCommandViewModel)) ?>
	<?php $view->panelEnd() ?>
<?php endif ?>
