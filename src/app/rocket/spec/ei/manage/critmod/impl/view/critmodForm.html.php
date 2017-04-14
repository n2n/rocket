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
	use n2n\web\dispatch\map\PropertyPath;
	use rocket\spec\ei\manage\critmod\impl\model\CritmodForm;
	use rocket\spec\ei\manage\critmod\filter\impl\controller\FilterAjahHook;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
		
	$critmodForm = $view->getParam('critmodForm'); 
	$view->assert($critmodForm instanceof CritmodForm);
	
	$filterAjahHook = $view->getParam('filterAjahHook');
	$view->assert($filterAjahHook instanceof FilterAjahHook);
?>
<div class="rocket-critmod<?php $view->out($critmodForm->isActive() ? ' rocket-active' : '') ?>">
	<?php $formHtml->open($critmodForm, null, null, array('class' => 'rocket-edit-form rocket-critmod-form'),
			$view->getParam('critmodFormUrl')) ?>
			
		<?php $formHtml->messageList() ?>
			
		<div class="rocket-critmod-save-select">
			<?php $formHtml->label('selectedCritmodSaveId', $view->getL10nText('ei_impl_select_filter_label')) ?>
			<?php $formHtml->select('selectedCritmodSaveId', $critmodForm->getSelectedCritmodSaveIdOptions(), array('class' => 'form-control')) ?>
			<?php $formHtml->inputSubmit('select', 'Select', array('class' => 'btn btn-secondary rocket-critmod-select')) ?>
		</div>
		<div class="rocket-critmod-configuration row">
			<div class="rocket-filter-container col-sm-8">
				<h4><?php $html->l10nText('ei_impl_filter_title') ?></h4>
				<?php $view->import('~\spec\ei\manage\critmod\filter\impl\view\filterForm.html', 
						array('propertyPath' => $formHtml->meta()->createPropertyPath('filterGroupForm'),
								'filterAjahHook' => $filterAjahHook)) ?>
			</div>
			<div class="rocket-sort-container col-sm-4">
				<h4><?php $html->l10nText('ei_impl_sort_title') ?></h4>
				<?php $view->import('~\spec\ei\manage\critmod\sort\impl\view\sortForm.html', 
						array('propertyPath' => $formHtml->meta()->createPropertyPath('sortForm'))) ?>
			</div>
			<div  class="rocket-form-actions col-sm-12">
				<ul class="rocket-critmod-commands nav">
					<li class="nav-item">
						<?php $formHtml->inputSubmit('apply', $view->getL10nText('common_apply_label'),
								array('class' => 'btn btn-secondary rocket-important rocket-critmod-submit-apply')) ?>
					</li>
					<li class="nav-item">
						<?php $formHtml->inputSubmit('clear', $view->getL10nText('common_clear_label'),
								array('class' => 'btn btn-secondary rocket-critmod-submit-clear')) ?>
					</li>
					<li class="nav-item">
						<?php $formHtml->inputSubmit('save', $view->getL10nText('common_save_label'),
								array('class' => 'btn btn-secondary rocket-critmod-submit-save')) ?>
					</li>
					<li class="nav-item rocket-textable-control">
						<?php $formHtml->inputSubmit('saveAs', $view->getL10nText('common_save_as_label'), 
								array('class' => 'btn btn-secondary', 'data-after-label' => $view->getL10nText('common_save_as_label'))) ?>
						<?php $formHtml->input('name', array('maxlength' => '32', 'class' => 'form-control')) ?>
					</li>
					<li class="nav-item">
						<?php $formHtml->inputSubmit('delete', $view->getL10nText('common_delete_label'),
								array('class' => 'btn btn-secondary-danger rocket-critmod-submit-delete')) ?>
					</li>
				</ul>
			</div>
			
		</div>
	<?php $formHtml->close() ?>
</div>
