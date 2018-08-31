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
	use rocket\impl\ei\component\command\common\model\critmod\CritmodForm;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
		
	$critmodForm = $view->getParam('critmodForm'); 
	$view->assert($critmodForm instanceof CritmodForm);
?>

<?php $formHtml->open($critmodForm, null, null, array(
		'class' => 'rocket-impl-critmod' . ($critmodForm->isActive() ? ' rocket-active' : ''),
		'data-rocket-impl-critmod-save-id' => $critmodForm->getSelectedCritmodSaveId(),
		'data-rocket-impl-post-url' => $view->getParam('critmodFormUrl'))) ?>
		
	<?php $formHtml->messageList() ?>

	<div class="row">
		<div class="col-sm-8">
			<h3><?php $html->l10nText('ei_impl_filter_title') ?></h3>
			<?php $html->out($critmodForm->getEiuFilterForm()
					->setContextPropertyPath($formHtml->meta()->propPath('eiuFilterForm'))) ?>
		</div>
		<div class="col-sm-4">
			<h3><?php $html->l10nText('ei_impl_sort_title') ?></h3>
			<?php $html->out($critmodForm->getEiuSortForm()
					->setContextPropertyPath($formHtml->meta()->propPath('eiuSortForm'))) ?>
		</div>
		<div class="col-sm-12 rocket-critmod-command-container">
			<div class="rocket-impl-critmod-commands">
				<?php $formHtml->buttonSubmit('apply', $view->getL10nText('common_apply_label'),
						array('class' => 'btn btn-primary rocket-impl-critmod-apply')) ?>
				
				<?php $formHtml->buttonSubmit('clear', $view->getL10nText('common_clear_label'),
						array('class' => 'btn btn-secondary rocket-impl-critmod-clear')) ?>
			</div>
			<div class="rocket-impl-critmod-manage">
				<label><?php $html->text('ei_impl_filter_save_label') ?>
					<?php $formHtml->input('name', array('maxlength' => '32', 'class' => 'form-control rocket-impl-critmod-name')) ?>
				</label>
				
				<?php $formHtml->buttonSubmit('save', $view->getL10nText('common_save_label'),
						array('class' => 'btn btn-secondary rocket-impl-critmod-save')) ?>

				<?php $formHtml->buttonSubmit('saveAs', $view->getL10nText('common_save_as_copy_label'), 
						array('class' => 'btn btn-secondary rocket-impl-critmod-save-as')) ?>
				
				<?php $formHtml->buttonSubmit('delete', $view->getL10nText('common_delete_label'),
						array('class' => 'btn btn-secondary rocket-impl-critmod-delete')) ?>
			</div>
		</div>
	</div>
<?php $formHtml->close() ?>