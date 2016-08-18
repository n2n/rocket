<?php 
	use rocket\script\config\model\EntityScriptForm;
	use rocket\script\entity\command\control\IconType;
	
	$entityScriptForm = $view->getParam('entityScriptForm');
	$view->assert($entityScriptForm instanceof EntityScriptForm);
?>
<section id="rocket-script-config-commands">
	<h2><?php $html->l10nText('script_commands_title') ?></h2>
	<div class="rocket-equal-split-content">
		<div>
			<section class="rocket-panel">
				<?php if (sizeof($classNames = $entityScriptForm->getInheritedCommandClassNames())): ?>
					<h2><?php $html->l10nText('script_inherited_commands_title') ?></h2>
					<ul id="rocket-config-inherited-script-commands" class="rocket-config-list-menu-group">
						<?php foreach ($classNames as $className): ?>
							<li class="rocket-config-list-item"><?php $html->out($className)?></li>
						<?php endforeach ?>
					</ul>
				<?php endif ?>
				<h2><?php $html->l10nText('script_added_commands_title') ?></h2>
				<table class="rocket-list">
					<thead>
						<tr>
							<th><?php $html->text('common_id_label') ?></th>
							<th><?php $html->text('script_command_class_label') ?></th>
							<th><?php $html->text('common_list_tools_label') ?></th>
						</tr>
					</thead>
					<tbody id="rocket-config-assigned-script-commands" class="rocket-config-list-menu-group rocket-draggable"
						data-add-label="<?php $html->text('script_add_unregistered_command_label') ?>">
						<?php foreach ($formHtml->getValue('commandClassNames') as $key => $commandClassName): ?>
							<tr class="rocket-config-table-row">
								<td>
									<?php $formHtml->inputField('commandIds[' . $key . ']', array('class' => 'rocket-config-assigned-item-id')) ?>
								</td>
								<td class="rocket-block">
									<?php $formHtml->inputField('commandClassNames[' . $key . ']', array('class' => 'rocket-config-assigned-item-class-identifier')) ?>
								</td>
								<td></td>
							</tr>
						<?php endforeach ?>
						<tr>
							<td>
								<?php $formHtml->inputField('commandIds[]', array('placeholder' => $html->getText('common_id_label'), 
										'class' => 'rocket-config-assigned-item-id')) ?>
							</td>
							<td class="rocket-block">
								<?php $formHtml->inputField('commandClassNames[]', array('placeholder' => $html->getText('script_command_class_label'), 
										'class' => 'rocket-config-assigned-item-class-identifier')) ?>
							</td>
							<td></td>
						</tr>
					</tbody>
				</table>
			</section>
		</div>
		<div class="rocket-grouped-panels">
			<section id="rocket-config-available-script-command-groups" class="rocket-panel">
				<h2><?php $html->l10nText('script_available_command_groups_title') ?></h2>
				<ul class="rocket-config-list-menu-group">
					<?php foreach ($entityScriptForm->getAvailableCommandGroups() as $scriptCommandGroup): ?>
						<li class="rocket-config-list-item">
							<?php $html->esc($scriptCommandGroup->getName()) ?>
							<ul class="rocket-simple-controls">
								<li><a href="#" class="rocket-config-assign-script rocket-control"><i class="fa fa-plus-circle"></i></a></li>
							</ul>
							<ul class="rocket-config-script-command-groups-commands">
								<?php foreach ($scriptCommandGroup->getCommandClasses() as $scriptCommandClass): ?>
									<li data-script-command-name="<?php $html->esc($scriptCommandClass->getName())?>">
										<?php $html->esc($scriptCommandClass->getName())?>
									</li>
								<?php endforeach ?>
							</ul>
						</li>
					<?php endforeach ?>
				</ul>
			</section>
			<section class="rocket-panel">
				<h2><?php $html->l10nText('script_available_commands_title') ?></h2>
				<ul id="rocket-config-available-script-commands" class="rocket-config-list-menu-group">
					<?php foreach ($entityScriptForm->getAvailableCommandClasses() as $scriptCommandClass): ?>
						<li class="rocket-config-list-item">
							<?php $html->esc($scriptCommandClass->getName()) ?>
							<ul class="rocket-simple-controls">
								<li><a class="rocket-config-assign-script rocket-control" 
										data-script-command-name="<?php $html->esc($scriptCommandClass->getName())?>" href=""><i class="fa fa-plus-circle"></i></a></li>
							</ul>
						</li>
					<?php endforeach ?>
				</ul>
			</section>
		</div>
	</div>
</section> 