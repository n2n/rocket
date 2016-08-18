<?php
	use n2n\ui\Raw;
	use n2n\io\fs\AbstractPath;

	$files = $view->getParam('files', false, array()); $view->assert(is_array($files));
	$view->useTemplate('core\view\template.html', array('title' => $view->getL10nText('tool_backup_title')));
?>

<div class="rocket-panel">
	<table class="rocket-list">
		<thead>
			<tr>
				<th><?php $html->l10nText('tool_backup_file_name_label') ?></th>
				<th class="rocket-common-actions-label"><?php $html->l10nText('common_list_tools_label') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($files as $file) : $view->assert($file instanceof AbstractPath) ?>
				<tr> 
					<td>
						<?php $html->out($file->getName()) ?>
					</td>
					<td>
						<ul class="rocket-simple-controls">
							<li>
								<?php $html->linkToController(array('download', $file->getName()), 
											new Raw('<i class="fa fa-save"></i><span>' . $html->getL10nText('common_save_as_label') . '</span>'), 
											array('class' => 'rocket-control', 'title' => $html->getL10nText('common_save_as_label'))) ?>
							</li>
							<li>
								<?php $html->linkToController(array('delete', $file->getName()), 
										new Raw('<i class="fa fa-times-circle"></i><span>' . $html->getL10nText('common_delete_label') . '</span>'), 
										array('class' => 'rocket-control', 'title' => $html->getL10nText('common_delete_label'),
												'data-rocket-confirm-cancel-label' => $view->getL10nText('common_no_label'),
												'data-rocket-confirm-ok-label' => $view->getL10nText('common_yes_label'),
												'data-rocket-confirm-msg' => $view->getL10nText('tool_backup_delete_confirm_msg', 
														array('file_name' => $file->getName())))) ?>
							</li>
						</ul>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<div id="rocket-page-controls">
		<ul>
			<li>
				<?php $html->linkToController(array('create'), 
						new Raw('<i class="fa fa-plus-circle"></i><span>' . $html->getL10nText('tool_backup_create_label') . '</span>'), 
						array('class' => 'rocket-control', 
								'title' => $view->getL10nText('tool_backup_create_tooltip'))) ?>
			</li>
			<li>
				<?php $html->linkToController(array('delete', '*'), 
						new Raw('<i class="fa fa-times-circle"></i><span>' . $html->getL10nText('tool_backup_delete_all_label') . '</span>'), 
						array('class' => 'rocket-control', 'title' => $view->getL10nText('tool_backup_delete_all_tooltip'),
								'data-rocket-confirm-cancel-label' => $view->getL10nText('common_no_label'),
								'data-rocket-confirm-ok-label' => $view->getL10nText('common_yes_label'),
								'data-rocket-confirm-msg' => $view->getL10nText('tool_backup_delete_all_confirm_msg'))) ?>
			</li>
		</ul>
	</div>
</div>
