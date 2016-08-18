<?php 
	use rocket\script\config\model\ScriptExtractionListModel;
	use n2n\ui\Raw;
	use rocket\script\entity\EntityScript;
	use rocket\script\config\model\BrokenScript;
	use rocket\script\core\Script;

	$scriptListForm = $view->params['scriptListForm'];
	$view->assert($scriptListForm instanceof ScriptExtractionListModel);

	$view->useTemplate('core\view\template.html', 
			array('title' => $view->getL10nText('script_list_title')));
	$menuGroupLabels = $scriptListForm->getMenuGroupLabels();
	$lastMenuGroupId = 0;
?>
<?php $formHtml->open($scriptListForm, null, null, array('id' => 'rocket-config-list-form')) ?>
	<div id="rocket-script-list" class="rocket-panel"
			data-text-add-menu-group-label="<?php $html->l10nText('script_list_add_menu_group_title') ?>">
		<?php $formHtml->arrayProps('menuGroupLabels', function($key) use ($formHtml, $html) { ?>
			<section class="rocket-config-list-section" data-menu-group-id="<?php $html->esc($key) ?>">
				<h3><?php $formHtml->inputField(null, array('id' => 'rocket-config-list-menu-label-' . $key, 'class' => 'rocket-config-list-menu-label')) ?></h3>
				<ul class= "rocket-config-list-menu-group rocket-draggable"></ul>
			</section>
		<?php }) ?>
		<?php $newMenuGroupId = sizeof($formHtml->getValue('menuGroupLabels')) + 1 ?>
		<section class="rocket-config-list-section rocket-config-list-section-new" data-menu-group-id="<?php $html->esc($newMenuGroupId) ?>">
			<h3><?php $formHtml->inputField('menuGroupLabels[' . $newMenuGroupId . ']', array('id' => 'rocket-config-list-menu-label-' . $newMenuGroupId, 'class' => 'rocket-config-list-menu-label', 'placeholder' => $view->getL10nText('script_new_section'))) ?></h3>
			<ul class="rocket-config-list-menu-group rocket-draggable"></ul>
		</section>
		<section class="rocket-config-list-section rocket-config-list-scripts-without-section" data-menu-group-id="">	
			<h3><?php $html->l10nText('script_non_menu_group_title')?></h3>
			<ul>
				<?php foreach ($scriptListForm->getScripts() as $script): $view->assert($script instanceof Script) ?>
					<li class="rocket-config-list-item">
						<span class="rocket-config-list-drag"><i class="fa fa-th"></i></span>
						<?php $html->esc($script->getId()) ?>
						<?php $html->esc($script->getLabel()) ?>
						<?php if (null !== ($module = $script->getModule())): ?>
							<?php $html->esc($module->getNamespace()) ?>
						<?php endif ?>
						<?php $formHtml->inputField('scriptMenuGroupIds[' . $script->getId() . ']', array('class' => 'rocket-config-menu-group-id')) ?>
						
						<?php if ($script instanceof BrokenScript): ?>
							<div class="rocket-broken-info">
								<?php $html->escBr($script->getReason()->getMessage())?>
							</div>
						<?php endif ?>
						
						<ul class="rocket-simple-controls">
							<li><?php $html->linkToController(array('edit', $script->getId()), 
									new Raw('<i class="fa fa-pencil"></i><span>' . $html->getL10nText('script_edit_label') . '</span>'), 
									array('class' => 'rocket-control')) ?></li>
							<?php if ($script instanceof EntityScript): ?>
								<li><?php $html->linkToController(array('config', $script->getId()), 
										new Raw('<i class="fa fa-edit"></i><span>' . $html->getL10nText('script_fields_edit_label') . '</span>'), 
										array('class' => 'rocket-control')) ?></li>
							<?php endif ?>
							
							<?php if ($scriptListForm->isScriptSealed($script)): ?>
								<li><?php $html->linkToController(array('unseal', $script->getId()), 
											new Raw('<i class="fa fa-lock"></i><span>' . $html->getL10nText('script_sealed_label') . '</span>'), 
											array('class' => 'rocket-control')) ?></li>
							<?php else: ?>
								<li><?php $html->linkToController(array('seal', $script->getId()), 
											new Raw('<i class="fa fa-unlock"></i><span>' . $html->getL10nText('script_unsealed_label') . '</span>'), 
											array('class' => 'rocket-control')) ?></li>
								<li><?php $html->linkToController(array('delete', $script->getId()), 
											new Raw('<i class="fa fa-times"></i><span>' . $html->getL10nText('script_delete_label') . '</span>'), 
											array('class' => 'rocket-control')) ?></li>
							<?php endif ?>
						</ul>
						
					</li>
				<?php endforeach ?>
			</ul>
		</section>
	</div>
	<div id="rocket-page-controls">
		<ul>
			<li class="rocket-control">
				<i class="fa fa-save"></i>				
				<?php $formHtml->inputSubmit('save', $view->getL10nText('common_save_label'), array('id' => 'rocket-config-list-form-save')) ?>
			</li>
			<li class="rocket-control">
				<?php $html->linkToController('cleanup', 
						new Raw('<i class="fa fa-stethoscope"></i><span>' . $html->getL10nText('script_clean_up_label') . '</span>'))?>
			</li>
			<li class="rocket-control">
				<?php $html->linkToController('add', 
						new Raw('<i class="fa fa-plus-circle"></i><span>' . $html->getL10nText('script_add_label') . '</span>'))?>
			</li>
		</ul>
	</div>
<?php $formHtml->close() ?>
