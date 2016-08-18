<?php
	use rocket\script\config\model\FieldOrderForm;
	use n2n\dispatch\PropertyPath;
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$fieldOrderViewModel = $formHtml->getValue($propertyPath)->getObject();
	$view->assert($fieldOrderViewModel instanceof FieldOrderForm);
	
	$numFields = $view->getParam('numFields');
?>
	
<ul class="rocket-properties">
	<li>
		<?php $formHtml->label($propertyPath->createExtendedPath(array('enabled'))) ?>
		<div class="rocket-controls">
			<?php $formHtml->inputCheckbox($propertyPath->createExtendedPath(array('enabled'))) ?>
		</div>
	</li>
	<li>
		<label>Order</label>
		<div class="rocket-controls">
			<ul>
				<?php for ($key = 0; $key < $numFields; $key++): ?>
					<li>
						<?php $formHtml->inputField($propertyPath->createExtendedPath(array('fieldIds[' . $key . ']')), 
								array('class' => 'rocket-mask-field-id')) ?>
						<?php $formHtml->inputField($propertyPath->createExtendedPath(array('fieldGroupKeys[' . $key . ']')), 
								array('class' => 'rocket-mask-group-key')) ?>
					</li>
				<?php endfor ?>
			</ul>
			
			<ul>
				<?php for ($key = 0; $key < 10; $key++): ?>
					<li data-key="<?php $html->out($key) ?>">
						<?php $formHtml->inputField($propertyPath->createExtendedPath(array('groupTitles[' . $key . ']')), 
								array('class' => 'rocket-mask-group-title')) ?>
						<?php $formHtml->select($propertyPath->createExtendedPath(array('groupTypes[' . $key . ']')),
								$fieldOrderViewModel->getGroupTypeOptions(), array('class' => 'rocket-mask-group-type')) ?>
						<?php $formHtml->inputField($propertyPath->createExtendedPath(array('groupParentKeys[' . $key . ']')), 
								array('class' => 'rocket-mask-group-parent-key')) ?>
					</li>
				<?php endfor ?>
			</ul>
		</div>
	</li>
</ul>