<?php 
	$propertyName = $view->getParam('propertyName');
?>
<div class="rocket-quick-access-panel">
	<?php foreach (array_keys($formHtml->getValue($propertyName)) as $key): ?>
		<?php $html->link('#rocket-field-' . $key, $key, array('class' => 'rocket-control')) ?> 
	<?php endforeach ?>
</div>

<ul class="rocket-config-list-menu-group">
	<?php $formHtml->arrayProps($propertyName, function($key) use ($view, $html, $formHtml) { ?>
		<?php $configurableModel = $formHtml->getValue()->getObject() ?>
		<li class="rocket-panel" id="rocket-field-<?php $html->out($key) ?>"> 
			<h3><?php $html->out($key) ?> (<?php $html->out($configurableModel->getTypeName()) ?>)</h3>
				
			<ul class="rocket-properties">
				<?php $formHtml->objectProps('optionForm', function() use ($formHtml) { ?>
					<?php $formHtml->openOption('li') ?>
						<?php $formHtml->optionLabel() ?>
						<div class="rocket-controls">
							<?php $formHtml->optionField() ?>
						</div>
					<?php $formHtml->closeOption() ?>
				<?php }) ?>
			</ul>
		</li>
	<?php }) ?>
</ul>