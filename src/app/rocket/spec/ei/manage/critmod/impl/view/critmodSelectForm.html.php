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
<?php $formHtml->open($critmodForm, null, null, array('class' => 'rocket-impl-critmod-select'),
		$view->getParam('critmodFormUrl')) ?>
			
	<?php $formHtml->label('selectedCritmodSaveId', $view->getL10nText('ei_impl_select_filter_label')) ?>
	<?php $formHtml->select('selectedCritmodSaveId', $critmodForm->getSelectedCritmodSaveIdOptions(), 
			array('class' => 'form-control')) ?>
	<?php $formHtml->buttonSubmit('select', 'Select', array('class' => 'btn btn-secondary rocket-critmod-select')) ?>
	<?php $formHtml->message() ?>
<?php $formHtml->close() ?>
