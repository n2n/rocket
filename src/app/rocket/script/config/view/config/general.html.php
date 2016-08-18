<?php 
	use rocket\script\config\model\EntityScriptConfigForm;
	
	$configForm = $view->getParam('configForm');
	$view->assert($configForm instanceof EntityScriptConfigForm);
?>

<ul class="rocket-properties">
	<li>
		<?php $formHtml->label('knownStringPattern') ?>
		<div class="rocket-controls">
			<?php $formHtml->inputField('knownStringPattern') ?>
			<dl id="rocket-edit-entity-script-available-placeholders">
				<?php foreach ($configForm->getHighlightableScriptFieldFieldNames() as $placeHolder => $name): ?>
					<dt><?php $html->esc($placeHolder) ?></dt>
					<dd><?php $html->esc($name) ?></dd>
				<?php endforeach ?>
			</dl>
		</div>
	</li>
	<li>
		<label>Default Sort</label>
		<div class="rocket-controls">
			<?php $view->import('script\entity\filter\view\sortForm.html', 
					array('propertyPath' => $formHtml->createPropertyPath('sortForm'))) ?>
		</div>
	</li>
	<li>
		<?php $formHtml->label('defaultMaskId') ?>
		<div class="rocket-controls">
			<?php $formHtml->select('defaultMaskId', $configForm->getDefaultMaskIdOptions())?>
		</div>
	</li>
	<li>
		<label>Masks</label>
		<div class="rocket-controls">
			<table>
				<tr>
					<th>Id</th>
					<th>Label</th>
					<th>Plural Label</th>
					<th>Filtered</th>
				</tr>
				<?php foreach ($configForm->getEntityScript()->getMaskSet() as $mask): ?>
					<tr>
						<td><?php $html->out($mask->getId())?></td>
						<td><?php $html->out($mask->getLabel())?></td>
						<td><?php $html->out($mask->getPluralLabel())?></td>
						<td><?php $html->out(null !== $mask->isFiltered() ? $html->text('common_yes') 
								: $html->text('common_no'))?></td>
						<td><?php $html->linkToController(array('mask', $mask->getEntityScript()->getId(), $mask->getId()), 'edit')?></td>
					</tr>
				<?php endforeach ?>
			</table>
		</div>
	</li>
</ul>