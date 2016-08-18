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

	use rocket\user\bo\RocketUserGroup;
	use rocket\user\model\RocketUserGroupListModel;
	use n2n\web\ui\Raw;
	use rocket\user\bo\RocketUser;
	use n2n\web\ui\view\impl\html\HtmlView;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$view->useTemplate('~\core\view\template.html', array('title' => $view->getL10nText('user_groups_title')));
	
	$userGroupOverviewModel = $view->getParam('userGroupOverviewModel');
	$view->assert($userGroupOverviewModel instanceof RocketUserGroupListModel);
?>
<div class="rocket-panel">
	<h3><?php $html->l10nText('user_groups_title') ?></h3>
	<table class="rocket-list">
		<thead>
			<tr>
				<th><?php $html->l10nText('common_id_label') ?></th>
				<th><?php $html->l10nText('common_name_label') ?></th>
				<th><?php $html->l10nText('user_group_members_label') ?></th>
				<th><?php $html->l10nText('user_accessible_menu_items_label') ?></th>
				<th><?php $html->l10nText('user_access_grants_label') ?></th>
				<th><?php $html->l10nText('common_list_tools_label') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($userGroupOverviewModel->getRocketUserGroups() as $userGroup): $view->assert($userGroup instanceof RocketUserGroup) ?>
				<tr>
					<td><?php $html->esc($userGroup->getId()) ?></td>
					<td><?php $html->esc($userGroup->getName()) ?></td>
					<td>
						<ul>
							<?php foreach ($userGroup->getRocketUsers() as $user): $view->assert($user instanceof RocketUser) ?>
								<li><?php $html->esc($user->getNick())?></li>
							<?php endforeach ?>
						</ul>
					</td>
					<td>
						<?php if (!$userGroup->isMenuItemAccessRestricted()): ?>
							<?php $html->text('user_no_restrictions') ?>
						<?php else: ?>
							<?php $html->out(implode(', ', $userGroup->getAccessibleMenuItemIds())) ?>
						<?php endif ?>
					</td>
					<td>
						<ul>
							<?php foreach ($userGroup->getEiGrants() as $eiGrant): ?>
								<li<?php $view->out($eiGrant->isFull() ? '' : ' class="rocket-user-access-restricted"') ?>>
									<?php $html->esc($userGroupOverviewModel->prettyEiGrantName($eiGrant) )?>
								</li>
							<?php endforeach ?>
							<?php foreach ($userGroup->getCustomGrants() as $customGrant): ?>
								<li<?php $view->out($customGrant->isFull() ? '' : ' class="rocket-user-access-restricted"') ?>>
									<?php $html->esc($userGroupOverviewModel->prettyCustomGrantName($customGrant)) ?>
								</li>
							<?php endforeach ?>
						</ul>
					</td>
					<td>
						<ul class="rocket-simple-controls">
							<li>
								<?php $html->linkToController(array('edit', $userGroup->getId()), 
										new n2n\web\ui\Raw('<i class="fa fa-pencil"></i><span>' . $view->getL10nText('user_edit_group_label') . '</span>'),
										array('title' => $view->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-warning')) ?>
							</li>
							<li>
								<?php $html->linkToController(array('grants', $userGroup->getId()), 
										new n2n\web\ui\Raw('<i class="fa fa-key"></i><span>' . $view->getL10nText('user_modify_grants_label') . '</span>'),
										array('title' => $view->getL10nText('user_modify_grants_tooltip'),
												'class' => 'rocket-control')) ?>
							</li>
							<li>
								<?php $html->linkToController(array('delete', $userGroup->getId()), 
										new n2n\web\ui\Raw('<i class="fa fa-times"></i><span>' . $view->getL10nText('user_delete_group_label') . '</span>'),
										array('title' => $view->getL10nText('user_delete_group_tooltip'), 
												'data-rocket-confirm-msg' => $view->getL10nText('user_group_delete_confirm', array('group' => $userGroup->getName())),
												'data-rocket-confirm-ok-label' => $view->getL10nText('common_yes_label'),
												'data-rocket-confirm-cancel-label' => $view->getL10nText('common_no_label'),
												'class' => 'rocket-control-danger')) ?>
							</li>
						</ul>
					</td>
				</tr>
			<?php endforeach ?>	
		</tbody>
	</table>
</div>
<div id="rocket-page-controls">
	<ul>
		<li>
			<?php $html->linkToController('add', new Raw('<i class="fa fa-plus-circle"></i><span>' 
							. $view->getL10nText('user_add_group_label') . '</span>'), 
					array('class' => 'rocket-control-success rocket-important')) ?>
		</li>
	</ul>
</div>
