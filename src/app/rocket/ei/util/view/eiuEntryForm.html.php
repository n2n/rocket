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

	use rocket\ei\util\model\EiuEntryFormViewModel;
	use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\map\PropertyPath;
use rocket\ei\manage\EiHtmlBuilder;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$eiuEntryFormViewModel = $view->getParam('eiuEntryFormViewModel');
	$view->assert($eiuEntryFormViewModel instanceof EiuEntryFormViewModel);
	
	$eiHtml = new EiHtmlBuilder($view);
	
// 	$eiuEntryFormViewModel->initFromView($view);
	
	$efPropertyPath = $eiuEntryFormViewModel->getEiuEntryForm()->getContextPropertyPath();
	if ($efPropertyPath === null) {
		$efPropertyPath = new PropertyPath(array());
	}
	$selectedTypeIdPropertyPath = $efPropertyPath->ext('chosenId');
	
	$typeChoicesMap = $eiuEntryFormViewModel->getTypeChoicesMap();
	$iconTypesMap = $eiuEntryFormViewModel->getIconTypeMap();
?>
<div class="rocket-editable">
	<label><?php $html->l10nText('user_group_privileges_label')?></label>
	
	<ul class="rocket-control">
		<?php $eiGrantHtml->privilegeCheckboxes('eiCommandPathStrs[]', $eiGrantForm->getPrivilegeDefinition()) ?>
	</ul>
</div>

<div>
	<label><?php $html->l10nText('user_group_access_config_label')?></label>
	<?php $view->out('<ul class="rocket-control">') ?>
		<?php $formHtml->meta()->objectProps('eiPropPrivilegeMagForm', function() use ($formHtml) { ?>
			<?php $formHtml->magOpen('li', null, array('class' => 'rocket-editable')) ?>
				<?php $formHtml->magLabel() ?>
				<div class="rocket-control">
					<?php $formHtml->magField() ?>
				</div>
			<?php $formHtml->magClose() ?>
		<?php }) ?>
	<?php $view->out('</ul>') ?>
</div>