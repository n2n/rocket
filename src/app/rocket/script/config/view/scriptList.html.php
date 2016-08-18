<?php 
	use rocket\script\config\model\ScriptListModel;
	use rocket\script\core\extr\ScriptExtraction;
	use n2n\ui\Raw;
	use rocket\script\core\extr\EntityScriptExtraction;

	$scriptListModel = $view->getParam('scriptListModel');
	$view->assert($scriptListModel instanceof ScriptListModel);

	$view->useTemplate('core\view\template.html', 
			array('title' => $view->getL10nText('script_title')));
?>
<?php foreach ($scriptListModel->getScriptExtractionGroups() as $moduleLabel => $scriptExtractionGroup): ?> 
	<div class="rocket-panel">
		<h3><?php $html->out($moduleLabel) ?></h3>
		<table class="rocket-list">
			<thead>
				<tr>
					<th><?php $html->text('common_id_label') ?></th>
					<th><?php $html->text('common_label_label') ?></th>
					<th><?php $html->text('script_module_label') ?></th>
					<th><?php $html->text('common_list_tools_label') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($scriptExtractionGroup as $scriptId => $scriptExtraction): $view->assert($scriptExtraction instanceof ScriptExtraction) ?>
					<tr>
						<td><?php $html->esc($scriptExtraction->getId()) ?></td>
						<td><?php $html->esc($scriptExtraction->getLabel()) ?></td>
						<td><?php $html->esc($scriptExtraction->getModule()) ?></td>
						<td>	
							<ul class="rocket-simple-controls">
								<li><?php $html->linkToController(array('edit', $scriptExtraction->getId()), 
										new Raw('<i class="fa fa-pencil"></i><span>' . $html->getL10nText('script_edit_label') . '</span>'), 
										array('class' => 'rocket-control-warning')) ?></li>
								<?php if ($scriptExtraction instanceof EntityScriptExtraction): ?>
									<li><?php $html->linkToController(array('entityscript', $scriptExtraction->getId()), 
											new Raw('<i class="fa fa-edit"></i><span>' . $html->getL10nText('script_config_label') . '</span>'), 
											array('class' => 'rocket-control-warning')) ?></li>
								<?php endif ?>
								
								<?php if ($scriptListModel->isSealed($scriptExtraction)): ?>
									<li><?php $html->linkToController(array('unseal', $scriptExtraction->getId()), 
												new Raw('<i class="fa fa-lock"></i><span>' . $html->getL10nText('script_sealed_label') . '</span>'), 
												array('class' => 'rocket-control')) ?></li>
								<?php else: ?>
									<li><?php $html->linkToController(array('seal', $scriptExtraction->getId()), 
												new Raw('<i class="fa fa-unlock"></i><span>' . $html->getL10nText('script_unsealed_label') . '</span>'), 
												array('class' => 'rocket-control')) ?></li>
									<li><?php $html->linkToController(array('delete', $scriptExtraction->getId()), 
												new Raw('<i class="fa fa-times"></i><span>' . $html->getL10nText('script_delete_label') . '</span>'), 
												array('class' => 'rocket-control-danger')) ?></li>
								<?php endif ?>
							</ul>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
<?php endforeach ?>

<div id="rocket-page-controls">
	<ul>
		<li>
			<?php $html->linkToController('cleanup', 
					new Raw('<i class="fa fa-database"></i><span>' . $html->getL10nText('script_generate_db_meta_label') . '</span>'),
					array('class' => 'rocket-control-warning', 'title' => $view->getL10nText('script_generate_db_meta_tooltip'))) ?>
		</li>
		<li>
			<?php $html->linkToController('add', 
					new Raw('<i class="fa fa-plus-circle"></i><span>' . $html->getL10nText('script_add_label') . '</span>'),
					array('class' => 'rocket-control-success rocket-important')) ?>
		</li>
	</ul>
</div>