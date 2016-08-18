<?php
	use rocket\user\model\GroupGrantsViewModel;
	
	$view->useTemplate('core\view\template.html', array('title' => $view->getL10nText('user_group_grants_title')));
	
	$groupGrantsViewModel = $view->getParam('groupGrantsViewModel');
	$view->assert($groupGrantsViewModel instanceof GroupGrantsViewModel);
?>

<div class="rocket-panel">
	<h3><?php $html->l10nText('user_group_grants_title') ?></h3>
	<table class="rocket-list">
		<thead>
			<tr>
				<th><?php $html->l10nText('common_name_label') ?></th>
				<th><?php $html->l10nText('user_access_type_label') ?></th>
				<th><?php $html->l10nText('common_list_tools_label') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($groupGrantsViewModel->getItems() as $item): ?>
				<tr>
					<td><?php $html->esc($item->getLabel()) ?></td>
					<td class="rocket-access-type-<? $html->out($item->getAccessType()) ?>">
						<?php $html->esc($item->getAccessType()) ?>
					</td>
					<td>
						<ul class="rocket-simple-controls">
							<li>
								<?php $html->linkToController(array('fullygrant', $groupGrantsViewModel->getGroupId(), $item->getId()), 
										new n2n\ui\Raw('<i class="fa fa-thumbs-up"></i><span>' . $view->getL10nText('user_edit_group_label') . '</span>'),
										array('title' => $view->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-success')) ?>
							</li>
							<li>
								<?php $html->linkToController(array('restrictgrant', $groupGrantsViewModel->getGroupId(), $item->getId()), 
										new n2n\ui\Raw('<i class="fa fa-wrench"></i><span>' . $view->getL10nText('user_edit_group_label') . '</span>'),
										array('title' => $view->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-warning')) ?>
							</li>
							<li>
								<?php $html->linkToController(array('removegrant', $groupGrantsViewModel->getGroupId(), $item->getId()),
										new n2n\ui\Raw('<i class="fa fa-thumbs-down"></i><span>' . $view->getL10nText('user_edit_group_label') . '</span>'),
										array('title' => $view->getL10nText('user_edit_tooltip'),
												'class' => 'rocket-control-danger')) ?>
							</li>
						</ul>
					</td>
				</tr>
			<?php endforeach ?>	
		</tbody>
	</table>
</div>