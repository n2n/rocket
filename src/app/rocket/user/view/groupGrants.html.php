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

	use rocket\user\model\GroupGrantsViewModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$view->useTemplate('~\core\view\template.html', array('title' => $view->getL10nText('user_group_grants_title')));
	
	$groupGrantsViewModel = $view->getParam('groupGrantsViewModel');
	$view->assert($groupGrantsViewModel instanceof GroupGrantsViewModel);
	
	$groupId = $groupGrantsViewModel->getRocketUserGroup()->getId()
?>

<div class="rocket-panel">
	<h3><?php $html->l10nText('user_group_grants_title') ?></h3>
	<table class="rocket-list">
		<thead>
			<tr>
				<th><?php $html->l10nText('common_name_label') ?></th>
				<th><?php $html->l10nText('user_power_label') ?></th>
				<th><?php $html->l10nText('common_list_tools_label') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($groupGrantsViewModel->getEiSpecItems() as $eiSpecId => $eiSpecItem): ?>
				<tr class="rocket-tree-level-<?php $html->out($eiSpecItem->getLevel()) ?>">
					<td><?php $html->esc($eiSpecItem->getLabel()) ?></td>
					<?php if ($eiSpecItem->isFullyAccessible()): ?>
						<td class="rocket-access-type-full"><?php $html->text('user_full_access_label') ?></td>
					<?php elseif ($eiSpecItem->isAccessible()): ?>
						<td class="rocket-access-type-restricted"><?php $html->text('user_restricted_access_label') ?></td>
					<?php else: ?>
						<td class="rocket-access-type-denied"><?php $html->text('user_denied_access_label') ?></td>
					<?php endif ?>
					<td>
						<ul class="rocket-simple-controls">
							<li>
								<?php $html->linkToController(array('fullyeigrant', $groupId, $eiSpecId), 
										new n2n\web\ui\Raw('<i class="fa fa-thumbs-up"></i><span>' . $html->getL10nText('user_grant_full_access_label') . '</span>'),
										array('title' => $view->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-success')) ?>
							</li>
							<li>
								<?php $html->linkToController(array('restricteigrant', $groupId, $eiSpecId), 
										new n2n\web\ui\Raw('<i class="fa fa-wrench"></i><span>' . $html->getL10nText('user_grant_restricted_access_label') . '</span>'),
										array('title' => $view->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-warning')) ?>
							</li>
							<li>
								<?php $html->linkToController(array('removeeigrant', $groupId, $eiSpecId),
										new n2n\web\ui\Raw('<i class="fa fa-thumbs-down"></i><span>' . $view->getL10nText('user_deny_access_label') . '</span>'),
										array('title' => $view->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-danger')) ?>
							</li>
						</ul>
					</td>
				</tr>
				
				<?php foreach ($eiSpecItem->getEiMaskItems() as $eiMaskItem): ?>
					<tr class="rocket-ei-mask">
						<td><?php $html->esc('> ' . $eiMaskItem->getLabel()) ?></td>
						<?php if ($eiSpecItem->isFullyAccessible()): ?>
							<td class="rocket-access-type-full"><?php $html->text('user_full_access_label') ?></td>
						<?php elseif ($eiSpecItem->isAccessible()): ?>
							<td class="rocket-access-type-restricted"><?php $html->text('user_restricted_access_label') ?></td>
						<?php else: ?>
							<td class="rocket-access-type-denied"><?php $html->text('user_denied_access_label') ?></td>
						<?php endif ?>
						<td>
							<ul class="rocket-simple-controls">
								<li>
									<?php $html->linkToController(array('fullyeigrant', $groupId, $eiSpecId, $eiMaskItem->getEiMaskId()), 
											new n2n\web\ui\Raw('<i class="fa fa-thumbs-up"></i><span>' 
													. $html->getL10nText('user_grant_full_access_label') . '</span>'),
											array('title' => $view->getL10nText('user_edit_tooltip'),
													'class' => 'rocket-control-success')) ?>
								</li>
								<li>
									<?php $html->linkToController(array('restricteigrant', $groupId, $eiSpecId, $eiMaskItem->getEiMaskId()), 
											new n2n\web\ui\Raw('<i class="fa fa-wrench"></i><span>' 
													. $html->getL10nText('user_grant_restricted_access_label') . '</span>'),
											array('title' => $view->getL10nText('user_edit_tooltip'),
													'class' => 'rocket-control-warning')) ?>
								</li>
								<li>
									<?php $html->linkToController(array('removeeigrant', $groupId, $eiSpecId, $eiMaskItem->getEiMaskId()),
											new n2n\web\ui\Raw('<i class="fa fa-thumbs-down"></i><span>' 
													. $view->getL10nText('user_deny_access_label') . '</span>'),
											array('title' => $view->getL10nText('user_edit_tooltip'),
													'class' => 'rocket-control-danger')) ?>
								</li>
							</ul>
						</td>
					</tr>
				<?php endforeach ?>
			<?php endforeach ?>	
			
			<?php foreach ($groupGrantsViewModel->getCustomItems() as $customSpecId => $customItem): ?>
				<tr>
					<td><?php $html->esc($customItem->getLabel()) ?></td>
					<?php if ($customItem->isFullyAccessible()): ?>
						<td class="rocket-access-type-full"><?php $html->text('user_full_access_label') ?></td>
					<?php elseif ($customItem->isAccessible()): ?>
						<td class="rocket-access-type-restricted"><?php $html->text('user_restricted_access_label') ?></td>
					<?php else: ?>
						<td class="rocket-access-type-denied"><?php $html->text('user_denied_access_label') ?></td>
					<?php endif ?>
					<td>
						<ul class="rocket-simple-controls">
							<li>
								<?php $html->linkToController(array('fullycustomgrant', $groupId, $customSpecId), 
										new n2n\web\ui\Raw('<i class="fa fa-thumbs-up"></i><span>' 
												. $html->getL10nText('user_edit_group_label') . '</span>'),
										array('title' => $html->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-success')) ?>
							</li>
							<li>
								<?php $html->linkToController(array('restrictcustomgrant', $groupId, $customSpecId), 
										new n2n\web\ui\Raw('<i class="fa fa-wrench"></i><span>' 
												. $html->getL10nText('user_edit_group_label') . '</span>'),
										array('title' => $html->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-warning')) ?>
							</li>
							<li>
								<?php $html->linkToController(array('removecustomgrant', $groupId, $customSpecId),
										new n2n\web\ui\Raw('<i class="fa fa-thumbs-down"></i><span>' 
												. $html->getL10nText('user_edit_group_label') . '</span>'),
										array('title' => $html->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-danger')) ?>
							</li>
						</ul>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</div>
