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
	use rocket\user\view\EiGrantHtmlBuilder;
	use rocket\ei\manage\critmod\filter\impl\controller\FilterAjahHook;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$eiGrantForm = $view->getParam('eiGrantForm'); 
	$view->assert($eiGrantForm instanceof EiGrantForm);
 
	$view->useTemplate('~\core\view\template.html', array('title' => $view->getL10nText('user_grant_title')));
	
	$eiGrantHtml = new EiGrantHtmlBuilder($view);
	
	$filterAjahHook = $view->getParam('filterAjahHook');
	$view->assert($filterAjahHook instanceof FilterAjahHook);
?>

<?php $formHtml->open($eiGrantForm)?>
	<?php $formHtml->messageList() ?>
	
	<div class="rocket-panel">
		<h3><?php $html->l10nText('common_properties_title') ?></h3>
		
		<div class="rocket-properties">
			
			<div class="rocket-control-group">
				<label>Privileges Grants</label>
				
				<div class="rocket-control">
					<div class="rocket-option-array">
						<?php $formHtml->meta()->arrayProps('eiPrivilegeGrantForms', function () 
								use ($view, $html, $formHtml, $eiGrantHtml, $eiGrantForm, $filterAjahHook) { ?>
							<div>
								<div class="rocket-properties">	
									<div class="rocket-editable">
										<div class="rocket-control">
											<?php $formHtml->optionalObjectCheckbox()  ?>
										</div>
									</div>
									
									<div class="rocket-editable">
										<label><?php $html->l10nText('user_group_privileges_label')?></label>
										<ul class="rocket-control">
											<?php $eiGrantHtml->privilegeCheckboxes('eiCommandPathStrs[]', $eiGrantForm->getPrivilegeDefinition()) ?>
										</ul>
									</div>
									
									<?php if ($formHtml->meta()->getMapValue()->getObject()->isEiPropPrivilegeMagFormAvailable()): ?>
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
									<?php endif ?>
									
									<?php if ($eiGrantForm->areRestrictionsAvailable()): ?>
										<div class="rocket-editable">
											<div class="rocket-control">
												<?php $formHtml->optionalObjectCheckbox('restrictionFilterGroupForm', null, 
														$html->getL10nText('user_access_restricted_label')) ?>
											</div>
										</div>
									
										<div>	
											<label><?php $html->l10nText('user_group_access_restrictions_label')?></label>
											<div class="rocket-control">
												<?php $view->import('~\ei\manage\critmod\filter\impl\view\filterForm.html', 
														array('propertyPath' => $formHtml->meta()->createPropertyPath('restrictionFilterGroupForm'),
																'filterAjahHook' => $filterAjahHook)) ?>
											</div>
										</div>
									<?php endif ?>
								</div>
							</div>
						<?php }, count($formHtml->meta()->getMapValue('eiPrivilegeGrantForms')) + 5) ?>
					</div>		
				</div>
			</div>
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
