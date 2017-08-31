<?php
	use rocket\spec\ei\component\field\impl\relation\model\mag\MappingForm;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\web\ui\view\View;
	use rocket\spec\ei\manage\EiHtmlBuilder;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	$eiHtml = new EiHtmlBuilder($view);
	
	$mappingForm = $view->getParam('mappingForm');
	$view->assert($mappingForm instanceof MappingForm);
	
	$eiuEntry = $mappingForm->getEntryForm()->getChosenEntryTypeForm()->getEiuEntryGui()->getEiuEntry();
?>

<div class="rocket-impl-entry"
		data-item-label="<?php $html->out($mappingForm->getEntryLabel()) ?>"
		data-remove-item-label="<?php $html->text('ei_impl_relation_remove_item_label', 
								array('item' => $mappingForm->getEntryLabel())) ?>">
	<?php $formHtml->optionalObjectEnabledHidden() ?>
	<?php if (!$mappingForm->isAccessible()): ?>
		<div class="rocket-impl-summary">
			<div class="rocket-impl-handle"><i class="fa fa-bars"></i></div>
			<div class="rocket-impl-content">
				<div class="rocket-impl-content-type">
					<?php $html->out($mappingForm->getEntryLabel()) ?>
				</div>
			</div>
		</div>
		
		<div class="rocket-impl-body rocket-group">
			<label><?php $html->out($mappingForm->getEntryLabel()) ?></label>
			<div class="rocket-controls">
				<?php $html->text('ei_impl_not_accessible') ?>
			</div>
		</div>
	<?php else: ?>
		<?php if (!$eiuEntry->isNew()): ?>
			<?php $eiuEntryGui = $eiuEntry->newEntryGui(false) ?>
			<?php $eiHtml->entryOpen('div', $eiuEntryGui, array('class' => 'rocket-impl-summary')) ?>
				<div class="rocket-impl-handle"><i class="fa fa-bars"></i></div>
				<div class="rocket-impl-content">
					<div class="rocket-impl-content-type">
						<i class="<?php $html->out($eiuEntry->getGenericIconType()) ?>"></i>
						<?php $html->out($eiuEntry->getGenericLabel()) ?>
					</div>
					<div>
						<?php foreach ($eiuEntryGui->getGuiIdPaths() as $guiIdPath): ?>
							<?php $eiHtml->fieldOpen('div', $guiIdPath) ?>
								<?php $eiHtml->fieldContent() ?>
							<?php $eiHtml->fieldClose() ?>
						<?php endforeach ?>
					</div>
				</div>
				<div class="rocket-simple-commands"></div>
			<?php $eiHtml->entryClose() ?>
		<?php else: ?>
			<div class="rocket-impl-summary">
				<div class="rocket-impl-handle"><i class="fa fa-bars"></i></div>
				<div class="rocket-impl-content">
					<div class="rocket-impl-content-type">
						<i class="<?php $html->out($eiuEntry->getGenericIconType()) ?>"></i>
						<?php $html->out($eiuEntry->getGenericLabel()) ?>
					</div>
					<div><?php $html->text('ei_impl_new_entry_txt') ?></div>
				</div>
				<div class="rocket-simple-commands"></div>
			</div>
		<?php endif ?>
	
		<div class="rocket-impl-body rocket-group">
			<label><?php $html->out($mappingForm->getEntryLabel()) ?></label>
			<div class="rocket-controls">
				<?php $view->out($mappingForm->getEntryForm()
						->setContextPropertyPath($formHtml->meta()->propPath('entryForm'))->createView()) ?>
			</div>
		</div>
	<?php endif ?>
	
	<?php $formHtml->input('orderIndex', array('class' => 'rocket-impl-order-index')) ?>
</div>