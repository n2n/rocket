<?php
	use rocket\spec\ei\component\field\impl\relation\model\mag\MappingForm;
	use rocket\spec\ei\manage\util\model\EntryFormViewModel;

	$mappingForm = $view->getParam('mappingForm');
	$view->assert($mappingForm instanceof MappingForm);
?>

<div class="rocket-impl-entry"
		data-item-label="<?php $html->out($mappingForm->getEntryLabel()) ?>"
		data-remove-item-label="<?php $html->text('ei_impl_relation_remove_item_label', 
								array('item' => $mappingForm->getEntryLabel())) ?>">
	<?php $formHtml->optionalObjectEnabledHidden() ?>
		
	<?php if (!$mappingForm->isAccessible()): ?>
		<span class="rocket-impl-summary">
			<?php $html->out($mappingForm->getEntryLabel()) ?>
		</span>
	<?php else: ?>
		<div class="rocket-impl-summary">
			<div class="rocket-impl-handle"><i class="fa fa-bars"></i></div>
			<div>
				<i class="<?php $html->out($mappingForm->getIconTyp()) ?>"></i>
				<?php $html->out($mappingForm->getEntryLabel()) ?>
			</div>
			<div>summary</div>
			<div class="rocket-simple-commands"></div>
		</div>
	
		<div class="rocket-impl-body rocket-group">
			<label><?php $html->out($mappingForm->getEntryLabel()) ?></label>
			<div class="rocket-controls">
				<?php $view->import('~\spec\ei\manage\util\view\entryForm.html', array(
						'entryFormViewModel' => new EntryFormViewModel($formHtml->meta()->propPath('entryForm')))) ?>
			</div>
		</div>
	<?php endif ?>
	
	<?php $formHtml->input('orderIndex', array('class' => 'rocket-impl-order-index')) ?>
</div>