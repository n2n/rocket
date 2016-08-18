<?php
	use rocket\user\bo\UserGroup;
	use rocket\user\bo\User;
	use rocket\user\model\UserGroupOverviewModel;
	use n2n\ui\Raw;

	$view->useTemplate('core\view\template.html', array('title' => $view->getL10nText('user_groups_title')));
	
	$userGroupOverviewModel = $view->getParam('userGroupOverviewModel');
	$view->assert($userGroupOverviewModel instanceof UserGroupOverviewModel);
?>
<div class="rocket-panel">
	<h3><?php $html->l10nText('user_groups_title') ?></h3>
	<table class="rocket-list">
		<thead>
			<tr>
				<th><?php $html->l10nText('common_id_label') ?></th>
				<th><?php $html->l10nText('common_name_label') ?></th>
				<th><?php $html->l10nText('user_group_members_label') ?></th>
				<th><?php $html->l10nText('user_accessable_menu_items_label') ?></th>
				<th><?php $html->l10nText('user_access_grants_label') ?></th>
				<th><?php $html->l10nText('common_list_tools_label') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($userGroupOverviewModel->getUserGroups() as $userGroup): $view->assert($userGroup instanceof UserGroup) ?>
				<tr>
					<td><?php $html->esc($userGroup->getId()) ?></td>
					<td><?php $html->esc($userGroup->getName()) ?></td>
					<td>
						<ul>
							<?php foreach ($userGroup->getUsers() as $user): $view->assert($user instanceof User) ?>
								<li><?php $html->esc($user->getNick())?></li>
							<?php endforeach ?>
						</ul>
					</td>
					<td>
						<?php if (!$userGroup->isMenuItemAccessRestricted()): ?>
							<?php $html->text('user_no_restrictions') ?>
						<?php else: ?>
							<?php $html->out(implode(', ', $userGroup->getAccessableMenuItemIds())) ?>
						<?php endif ?>
					</td>
					<td>
						<ul>
							<?php foreach ($userGroup->getUserScriptGrants() as $accessGrant): ?>
								<li<?php $view->out($accessGrant->isFull() ? '' : ' class="rocket-user-access-restricted"') ?>>
									<?php $html->esc($accessGrant->getScriptId())?>
								</li>
							<?php endforeach ?>
						</ul>
					</td>
					<td>
						<ul class="rocket-simple-controls">
							<li>
								<?php $html->linkToController(array('edit', $userGroup->getId()), 
										new n2n\ui\Raw('<i class="fa fa-pencil"></i><span>' . $view->getL10nText('user_edit_group_label') . '</span>'),
										array('title' => $view->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-warning')) ?>
							</li>
							<li>
								<?php $html->linkToController(array('grants', $userGroup->getId()), 
										new n2n\ui\Raw('<i class="fa fa-key"></i><span>' . $view->getL10nText('user_modify_grants_label') . '</span>'),
										array('title' => $view->getL10nText('user_modify_grants_tooltip'),
												'class' => 'rocket-control')) ?>
							</li>
							<li>
								<?php $html->linkToController(array('delete', $userGroup->getId()), 
										new n2n\ui\Raw('<i class="fa fa-times"></i><span>' . $view->getL10nText('user_delete_group_label') . '</span>'),
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