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

	use n2n\web\ui\Raw;
	use rocket\user\model\EiGrantForm;
	use n2n\impl\web\ui\view\html\HtmlView;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$eiGrantForm = $view->getParam('eiGrantForm'); 
	$view->assert($eiGrantForm instanceof EiGrantForm);
 
	$view->useTemplate('~\core\view\template.html', array('title' => $view->getL10nText('user_group_privileges_for_mask_title')));
?>

<?php $formHtml->open($eiGrantForm)?>
	<?php $formHtml->messageList() ?>
	
	
	<div class="rocket-group rocket-simple-group">
		<label>Inherited Privileges</label>
		<div class="rocket-control">
		</div>
	</div>
	
	<div class="rocket-group rocket-simple-group">
		<label>Privileges</label>
		
		<div class="rocket-control">
			
			<?php $formHtml->meta()->arrayProps('eiGrantPrivilegeForms', function () 
					use ($view, $html, $formHtml, $eiGrantForm) { ?>
				<div class="rocket-group rocket-simple-group">
					<div class="rocket-control">	
						<div class="rocket-editable">
							<div class="rocket-control">
								<?php $formHtml->optionalObjectCheckbox()  ?>
							</div>
						</div>
						
						<?php if (null !== ($mappingResult = $formHtml->meta()->getMapValue('eiuPrivilegeForm'))): ?>
							<?php $html->out($mappingResult->getObject()
									->setContextPropertyPath($formHtml->meta()->propPath('eiuPrivilegeForm'))) ?>
						<?php endif ?>
						
						<?php if ($eiGrantForm->areRestrictionsAvailable()): ?>
							<div class="rocket-editable">
								<div class="rocket-control">
									<?php $formHtml->optionalObjectCheckbox('restrictionEiuFilterForm', null, 
											$html->getL10nText('user_access_restricted_label')) ?>
								</div>
							</div>
						
							<div>	
								<label><?php $html->l10nText('user_group_access_restrictions_label')?></label>
								<div class="rocket-control">
									<?php $html->out($formHtml->meta()->getMapValue('restrictionEiuFilterForm')->getObject()
											->setContextPropertyPath($formHtml->meta()->propPath('restrictionEiuFilterForm')))?>
								</div>
							</div>
						<?php endif ?>
					</div>
				</div>
			<?php }, count($formHtml->meta()->getMapValue('eiGrantPrivilegeForms')) + 5) ?>
		</div>
	</div>
	<div class="rocket-zone-commands">	
		<div>
			<?php $formHtml->buttonSubmit('save', new Raw('<i class="fa fa-save"></i><span>' 
							. $html->getL10nText('common_save_label') . '</span>'),
					array('class' => 'btn btn-primary')) ?>
		</div>
	</div>
<?php $formHtml->close() ?>
