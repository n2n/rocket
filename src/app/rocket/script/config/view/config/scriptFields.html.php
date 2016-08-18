<?php 
	
?>
<ul class="rocket-config-list-menu-group rocket-nested-list">
	<?php $formHtml->arrayProps('scriptFieldConfigModels', function() use ($view, $html, $formHtml) { ?>
		<?php $scriptFieldConfigModel = $formHtml->getValue()->getObject() ?>
		<li class="rocket-config-list-item">
			<div class="rocket-config-list-item-heading">
				<label for=""><?php $html->esc($scriptFieldConfigModel->getName()) ?></label>
				<div class="rocket-controls rocket-controls-inline">
					<?php $html->esc($scriptFieldConfigModel->getTypeName()) ?>
				</div>
			</div>
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