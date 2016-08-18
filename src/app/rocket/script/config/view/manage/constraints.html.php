<?php 
	use rocket\script\config\model\EntityScriptForm;
	use rocket\script\entity\command\control\IconType;
	
	$entityScriptForm = $view->getParam('entityScriptForm');
	$view->assert($entityScriptForm instanceof EntityScriptForm);
?>
		
<section id="rocket-script-config-constraints" class="rocket-equal-split-content">
	<h2><?php $html->l10nText('script_constraints_title') ?></h2>
	<div>
		<section class="rocket-panel">
			<?php if (sizeof($classNames = $entityScriptForm->getInheritedConstraintClassNames())): ?>
				<h2><?php $html->l10nText('script_inherited_constraints_title') ?></h2>
				<table class="rocket-list">
					<thead>
						<tr>
							<th><?php $html->text('script_constraint_class_label') ?></th>
						</tr>
					</thead>
					<tbody id="rocket-config-inherited-constraints" class="rocket-config-list-menu-group rocket-draggable" >
						<?php foreach ($classNames as $className): ?>
							<tr class="rocket-config-table-row">
								<td><?php $html->out($className)?></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			<?php endif ?>
			<h2><?php $html->l10nText('script_added_constraints_title') ?></h2>
			<table class="rocket-list">
				<thead>
					<tr>
						<th><?php $html->text('common_id_label') ?></th>
						<th><?php $html->text('script_constraint_class_label') ?></th>
						<th><?php $html->text('common_list_tools_label') ?></th>
					</tr>
				</thead>
				<tbody id="rocket-config-assigned-edit-constraints" class="rocket-config-list-menu-group rocket-draggable" 
						data-add-label="<?php $html->text('script_add_unregistered_constraint_label') ?>">
					<?php foreach ($formHtml->getValue('constraintClassNames') as $key => $constraintClassName): ?>
						<tr class="rocket-config-table-row">
							<td>
								<?php $formHtml->inputField('constraintIds[' . $key . ']', array('class' => 'rocket-config-assigned-item-id')) ?>
							</td>
							<td>
								<?php $formHtml->inputField('constraintClassNames[' . $key . ']',  array('class' => 'rocket-config-assigned-item-class-identifier')) ?>
							</td>
							<td></td>
						</tr>
					<?php endforeach ?>
					<tr>
						<td>
							<?php $formHtml->inputField('constraintIds[]', array('placeholder' => $html->getText('common_id_label'), 
									'class' => 'rocket-config-assigned-item-id')) ?>
						</td>
						<td>
							<?php $formHtml->inputField('constraintClassNames[]', array('placeholder' => $html->getText('script_constraint_class_label'), 
										'class' => 'rocket-config-assigned-item-class-identifier')) ?>
						</td>
						<td></td>
					</tr>
				</tbody>
			</table>
		</section>
	</div>
	<div>
		<section class="rocket-panel">	
			<h2><?php $html->l10nText('script_available_constraints_title') ?></h2>
			<ul id="rocket-config-available-script-constraints" class="rocket-config-list-menu-group">
				<?php foreach ($entityScriptForm->getAvailableConstraintClasses() as $scriptModificatorClass): ?>
					<li class="rocket-config-list-item">
						<?php $html->esc($scriptModificatorClass->getName())?>
						<ul class="rocket-simple-controls">
							<li><a class="rocket-config-assign-script rocket-control" 
									data-script-constraint-name="<?php $html->esc($scriptModificatorClass->getName())?>" href=""><i class="fa fa-plus-circle"></i></a></li>
						</ul>
					</li>
				<?php endforeach ?>
			</ul>
		</section>
	</div>
</section>