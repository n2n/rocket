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

	use rocket\user\model\RocketUserForm;
	use n2n\web\ui\Raw;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\web\ui\view\View;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$userGroupForm = $view->getParam('userForm'); 
	$view->assert($userGroupForm instanceof RocketUserForm);

	$rocketUser = $userGroupForm->getRocketUser();
	
	$view->useTemplate('~\core\view\template.html', array('title' => ($userGroupForm->isNew() 
			? $view->getL10nText('user_add_title') : $view->getL10nText('user_edit_title', 
					array('user' => (string) $rocketUser)))));
?>

<?php $formHtml->open($userGroupForm, null, 'post', array('class' => 'rocket-edit-form'))?>
	<div class="rocket-panel">
		<h3><?php $html->l10nText('common_properties_title') ?></h3>
	
		<?php $formHtml->messageList() ?>
			
		<div class="rocket-properties">
			<div>
				<?php $formHtml->label('rocketUser.nick') ?>
				<div class="rocket-controls">
					<?php $formHtml->input('rocketUser.nick', array('maxlength' => 128)) ?>
				</div>
			</div>
			<div>
				<?php $formHtml->label('rawPassword') ?>
				<div class="rocket-controls">
					<?php $formHtml->input('rawPassword', null, 'password', true) ?>
				</div>
			</div>
			<div>
				<?php $formHtml->label('rawPassword2') ?>
				<div class="rocket-controls">
					<?php $formHtml->input('rawPassword2', null, 'password', true) ?>
				</div>
			</div>
			<div>
				<?php $formHtml->label('rocketUser.firstname') ?>
				<div class="rocket-controls">
					<?php $formHtml->input('rocketUser.firstname', array('maxlength' => 32)) ?>
				</div>
			</div>
			<div>
				<?php $formHtml->label('rocketUser.lastname') ?>
				<div class="rocket-controls">
					<?php $formHtml->input('rocketUser.lastname', array('maxlength' => 32)) ?>
				</div>
			</div>
			<div>
				<?php $formHtml->label('rocketUser.email') ?>
				<div class="rocket-controls">
					<?php $formHtml->input('rocketUser.email', array('maxlength' => 128), 'email') ?>
				</div>
			</div>
			
			<?php if (null !== ($powerOptions = $userGroupForm->getPowerOptions())): ?>
				<div>
					<?php $formHtml->label('power') ?>
					<div class="rocket-controls">
						<?php $formHtml->select('power', $powerOptions) ?>
					</div>
				</div>
			<?php endif ?>
			
			<div>
				<?php $formHtml->label('rocketUserGroupIds') ?>
				<div class="rocket-controls">
					<?php $availableRocketUserGroups = $userGroupForm->getAvailableRocketUserGroups() ?>
					<ul>
						<?php if ($availableRocketUserGroups === null): ?>
							<?php foreach ($rocketUser->getRocketUserGroups() as $userGroup): ?>
								<li><?php $html->out($userGroup->getName()) ?></li>
							<?php endforeach ?>
						<?php else: ?>
							<?php foreach ($availableRocketUserGroups as $id => $userGroup): ?>
								<li><?php $formHtml->inputCheckbox($formHtml->meta()->propPath('rocketUserGroupIds')
										->fieldExt($id), $id, null, $userGroup->getName())?></li>
							<?php endforeach ?>
						<?php endif ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="rocket-context-controls">	
		<ul>
			<li>
				<?php $formHtml->buttonSubmit('save', 
						new Raw('<i class="fa fa-save"></i>' . $html->getL10nText('common_save_label')), 
						array('class' => 'rocket-control-warning rocket-important')) ?>
			</li>
		</ul>
	</div>
<?php $formHtml->close() ?>
