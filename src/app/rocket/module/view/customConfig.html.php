<?php
	use n2n\ui\html\Form;
	use n2n\dispatch\option\OptionCollectionDispatchable;

	$optionForm = $view->getParam('optionForm');
	$view->assert($optionForm instanceof OptionCollectionDispatchable);
	
	$view->useTemplate('core\view\template.html', 
			array('title' => $view->getL10nText('module_custom_config_title')));
?>
<?php $formHtml->open($optionForm, Form::ENCTYPE_MULTIPART) ?>
	<div class="rocket-option-form">	
		<?php foreach ($optionForm->getPropertyNames() as $propertyName): ?>
			<?php $formHtml->openOption('div', $propertyName, array('class' => 'rocket-panel')) ?>
				<h3><?php $formHtml->optionLabel() ?></h3>
				<div class="rocket-edit-content">				
					<?php $formHtml->optionField($propertyName) ?>
				</div>
			<?php $formHtml->closeOption() ?>
		<?php endforeach ?>
		<div id="rocket-page-controls">
			<ul>
				<li class="rocket-control-warning">
					<i class="fa fa-save"></i>	
					<input type="submit" value="Speichern" />
				</li>
			</ul>
		</div>
	</div>
<?php $formHtml->close() ?>

