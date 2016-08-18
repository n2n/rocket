<?php
	use rocket\script\entity\manage\model\EntryFormViewModel;

	$entryFormViewModel = $view->getParam('entryFormViewModel'); 
	$view->assert($entryFormViewModel instanceof EntryFormViewModel);

	$basePropertyPath = $entryFormViewModel->getBasePropertyPath();
	
// 	$entryForm = $entryFormInfo->getEntryForm();
// 	$propertyPath = $entryFormInfo->getPropertyPath(); 
	
// 	$entryHtml = new ScriptHtmlBuilder($view, $entryForm->getScriptState(), $propertyPath);
	$html->addJs('js/script/edit.js');
?>
       
<?php if (!$entryFormViewModel->isTypeChangable()): ?>
	<?php $view->import($entryFormViewModel->createEditView())?>
<?php else: ?>
	<div class="rocket-type-dependent-entry-form">
		<div class="rocket-script-type-selector">
			<?php $formHtml->label($basePropertyPath->ext('selectedTypeId')) ?>
			<div class="rocket-controls">
				<?php $formHtml->select($basePropertyPath->ext('selectedTypeId'), 
						$entryFormViewModel->getTypeOptions(), array('class' => 'rocket-script-type-selection')) ?>
			</div>
		</div>
	
		<div class="rocket-script-main-type">
			<?php $view->import($entryFormViewModel->createEditView()) ?>
		</div>	
		
		<?php foreach ($entryFormViewModel->getTypeLevelIds() as $id => $levelIds): ?>
			<div class="<?php $html->esc(implode(' ', $levelIds)) ?>">
				<?php $view->import($entryFormViewModel->createTypeLevelEditView($id)) ?>
			</div>
		<?php endforeach ?>
	</div>
<?php endif ?>