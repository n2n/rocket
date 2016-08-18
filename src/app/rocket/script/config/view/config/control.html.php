<?php 
	use rocket\script\config\model\ScriptMaskForm;
	
	$configForm = $view->getParam('maskForm'); 
	$view->assert($configForm instanceof ScriptMaskForm);
?>
<div class="rocket-grouped-panels">
	<div class="rocket-panel">
		<h3><?php $html->l10nText('script_control_config_partial_title') ?></h3>
		<ul class="rocket-config-list-menu-group">
			<?php foreach ($configForm->getPartialControlOptions($request->getLocale()) as $key => $label): ?>
				<li class="rocket-config-list-item">
					<span class="rocket-config-list-drag">
						<i class="fa fa-th"></i>
					</span>
					<?php $formHtml->inputField('partialControlOrder[]', null, 'text', false, $key) ?>
					<?php $html->out($label) ?>
				</li>
			<?php endforeach ?>
		</ul>
	</div>
	<div class="rocket-panel">
		<h3><?php $html->l10nText('script_control_config_overall_title') ?></h3>
		<ul class="rocket-config-list-menu-group">
			<?php foreach ($configForm->getOverallControlOptions($request->getLocale()) as $key => $label): ?>
				<li class="rocket-config-list-item">
					<span class="rocket-config-list-drag">
						<i class="fa fa-th"></i>
					</span>
					<?php $formHtml->inputField('overallControlOrder[]', null, 'text', false, $key) ?>
					<?php $html->out($label) ?>
				</li>
			<?php endforeach ?>
		</ul>
	</div>
	<div class="rocket-panel">
		<h3><?php $html->l10nText('script_control_config_entry_title') ?></h3>
		<ul class="rocket-config-list-menu-group">
			<?php foreach ($configForm->getEntryControlOptions($request->getLocale()) as $key => $label): ?>
				<li class="rocket-config-list-item ">
					<span class="rocket-config-list-drag">
						<i class="fa fa-th"></i>
					</span>
					<?php $formHtml->inputField('entryControlOrder[]', null, 'text', false, $key) ?>
					<?php $html->out($label) ?>
				</li>
			<?php endforeach ?>
		</ul>
	</div>
</div>