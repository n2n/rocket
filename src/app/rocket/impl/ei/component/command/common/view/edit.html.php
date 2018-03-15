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

	use rocket\impl\ei\component\command\common\model\EditModel;
	use rocket\impl\ei\component\command\common\model\EntryCommandViewModel;
	use n2n\web\ui\Raw;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\impl\web\dispatch\ui\Form;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	$request = HtmlView::request($this);
	
	$editModel = $view->getParam('editModel');
	$view->assert($editModel instanceof EditModel);
	
	$entryCommandViewModel = $view->getParam('entryCommandViewModel');
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
	
	$view->useTemplate('~\core\view\template.html', array('title' => $entryCommandViewModel->getTitle()));
?>
<?php $formHtml->open($editModel, Form::ENCTYPE_MULTIPART, null, array('class' => 'rocket-form')) ?>
	<?php $view->out($editModel->getEiuEntryForm()->createView($view, true)) ?>
					
	<?php $formHtml->messageList() ?>
					
	<div class="rocket-zone-commands">
		<div class="rocket-main-commands">
			<?php $formHtml->buttonSubmit('save', new Raw('<i class="fa fa-save"></i><span>' 
							. $html->getL10nText('common_save_and_back_label') . '</span>'), 
					array('class' => 'btn btn-primary rocket-important')) ?>
					
			<?php $formHtml->buttonSubmit('quicksave', new Raw('<i class="fa fa-save"></i><span>' 
							. $html->getL10nText('common_quicksave_label') . '</span>'), 
					array('class' => 'btn btn-primary')) ?>
			
			<?php if ($editModel->isDraftable()): ?>
				<?php $formHtml->buttonSubmit('saveAsNewDraft', new Raw('<i class="fa fa-save"></i><span>' 
								. $html->getL10nText('common_save_as_new_draft_label') . '</span>'), 
						array('class' => 'btn btn-secondary')) ?>
			<?php endif ?>
			
			<?php if ($editModel->isPublishable()): ?>
				<?php $formHtml->buttonSubmit('saveAndPublish', new Raw('<i class="fa fa-save"></i><span>' 
								. $html->getL10nText('common_save_and_publish_label') . '</span>'), 
						array('class' => 'btn btn-secondary')) ?>
			<?php endif ?>
			
			<?php $html->link($entryCommandViewModel->determineCancelUrl($view->getHttpContext()), 
					new Raw('<i class="fa fa-times-circle"></i><span>' 
							. $html->getL10nText('common_cancel_label') . '</span>'),
					array('class' => 'btn btn-secondary rocket-jhtml', 'data-jhtml-use-page-scroll-pos' => 'true')) ?>
		</div>
		
		<?php if ($entryCommandViewModel->isPreviewAvailable()): ?>
			<div class="rocket-aside-commands">
				<?php $formHtml->buttonSubmit('saveAndPreview', new Raw('<i class="fa fa-eye"></i><span>' 
								. $html->getL10nText('common_save_and_preview_label') . '</span>'), 
						array('class' => 'btn btn-secondary')) ?>
			</div>
		<?php endif ?>
	</div>
<?php $formHtml->close() ?>

<?php if ($entryCommandViewModel->hasDraftHistory()): ?>
	<?php $view->panelStart('additional') ?>
		<?php $view->import('inc\historyNav.html', array('entryCommandViewModel' => $entryCommandViewModel)) ?>
	<?php $view->panelEnd() ?>
<?php endif ?>
