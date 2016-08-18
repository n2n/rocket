<?php
	use n2n\ui\Raw;
	use rocket\tool\controller\ToolController;
	use rocket\script\entity\command\control\IconType;
	
	$view->useTemplate('core\view\template.html', array('title' => $view->getL10nText('tool_title')));
?>
<div class="rocket-panel">
	<table class="rocket-list">
		<thead>
			<tr>
				<th><?php $html->l10nText('tool_title') ?></th>
				<th><?php $html->l10nText('common_list_tools_label') ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<?php $html->l10nText('tool_backup_title') ?>
				</td>
				<td>
					<ul class="rocket-simple-controls">
						<li>
							<?php $html->linkToController(ToolController::ACTION_BACKUP_OVERVIEW, 
									new Raw('<i class="fa fa-hdd-o"></i><span>' 
											. $html->getL10nText('tool_backup_title') . '</span>'), 
									array('class' => 'rocket-control')) ?>
						</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td>
					<?php $html->l10nText('tool_mail_center_title') ?>
				</td>
				<td>
					<ul class="rocket-simple-controls">
						<li>
							<?php $html->linkToController(ToolController::ACTION_MAIL_CENTER, 
									new Raw('<i class="' . IconType::ICON_ENVELOPE . '"></i><span>' . $html->getL10nText('tool_mail_center_tooltip') . '</span>'), 
									array('class' => 'rocket-control')) ?>
						</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td>
					<?php $html->l10nText('tool_clear_cache_title') ?>
				</td>
				<td>
					<ul class="rocket-simple-controls">
						<li>
							<?php $html->linkToController(ToolController::ACTION_CLEAR_CACHE, 
									new Raw('<i class="' . IconType::ICON_ERASER . '"></i><span>' 
													. $html->getL10nText('tool_clear_cache_title') . '</span>'), 
											array('class' => 'rocket-control', 
													'title' => $html->getL10nText('tool_clear_cache_title'))) ?>
						</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
</div>