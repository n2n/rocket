<?php
	use n2n\dispatch\PropertyPath;
	use rocket\script\entity\command\control\IconType;
use n2n\reflection\ReflectionUtils;
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	$html->addJs('js\filters.js');
?>
<ul class="rocket-filter-items" 
		data-add-icon-class-name="<?php $html->out(IconType::ICON_PLUS_CIRCLE) ?>"
		data-remove-icon-class-name="<?php $html->out(IconType::ICON_TIMES)?>" 
		data-text-add-group="<?php $html->text('script_filter_add_group_label') ?>" 
		data-text-or="<?php $html->text('common_or_label') ?>"
		data-text-and="<?php $html->text('common_and_label') ?>"
		data-text-activate-group="<?php $html->text('script_filter_activate_group_label')?>">
	<?php $formHtml->arrayProps($propertyPath->ext('filterItemForms'), 
			function ($value) use ($view, $html, $formHtml) { ?>
		
		<li class="rocket-filterable-property">
			<label><?php $html->out($value->getObject()->getLabel()) ?></label>
			<ul class="rocket-filter-property-usages">
				<?php $formHtml->arrayProps('usages', function ($value) use ($view, $formHtml) { ?>
					<li>
						<?php $formHtml->objectOptionalCheckbox($formHtml->createPropertyPath(), array('class' => 'rocket-filter-item-used')) ?>
						<?php $formHtml->inputField('groupKey', array('class' => 'rocket-filter-item-group-key')) ?>
						<ul class="rocket-properties">
							<?php $formHtml->objectProps($formHtml->createPropertyPath('optionForm'), function () use ($formHtml) { ?>
								<?php $formHtml->openOption('li') ?>
									<?php $formHtml->optionLabel() ?>
									<div>
										<?php $formHtml->optionField() ?>
									</div>
								<?php $formHtml->closeOption() ?>
							<?php }) ?>
						</ul>
					</li>
				<?php }, sizeof($formHtml->getValue('usages')) + 4) ?>
			</ul>
		</li>
	<?php }) ?>
	<li class="rocket-filter-group-definition">
		<ul>
			<?php foreach ($propertyPath->createExtendedPath(array('groupParentKeys')) as $key => $groupParentKey): ?>
				<li class="rocket-filter-group-definition-item" data-key="<?php $html->out($key) ?>">
					<?php $formHtml->inputCheckbox($propertyPath->createExtendedPath(array('groupAndUsed[' . $key . ']')), 
							true, array('class' => 'rocket-filter-group-used')) ?>
					<?php $formHtml->inputField($propertyPath->createExtendedPath(array('groupParentKeys[' . $key . ']')),
							array('class' => 'rocket-filter-group-parent-keys')) ?>
				</li>
			<?php endforeach ?>

		</ul>
	</li>
</ul>