<?php
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\ei\util\model\EiuEntry;
	use rocket\ei\manage\EiHtmlBuilder;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	
	$eiuEntry = $view->getParam('eiuEntry');
	$view->assert($eiuEntry instanceof EiuEntry);
	
	$eiHtml = new EiHtmlBuilder($view);
	
	$summaryRequired = $view->getParam('summaryRequired');
?>

<div class="rocket-impl-entry">
	<?php if (!$eiuEntry->isAccessible()): ?>
		<?php if ($summaryRequired): ?>
			<div class="rocket-impl-summary">
				<div class="rocket-impl-handle"></div>
				<div class="rocket-impl-content">
					<div class="rocket-impl-content-type">
						<i class="<?php $html->out($eiuEntry->getIconTyp()) ?>"></i>
						<?php $html->out($eiuEntry->getGenericLabel()) ?>
					</div>
					<div>
						<?php $html->text('ei_impl_not_accessible', array('entry' => $eiuEntry->createIdentityString())) ?>
					</div>
				</div>
			</div>
		<?php endif ?>
		
		<div class="rocket-impl-body rocket-group">
			<label><?php $html->out($eiuEntry->createIdentityString()) ?></label>
			<div class="rocket-control">
				<?php $html->text('ei_impl_not_accessible', array('entry' => $eiuEntry->createIdentityString())) ?>
			</div>
		</div>
	<?php else: ?>
		<?php if ($summaryRequired): ?>
			<?php $eiuEntryGui = $eiuEntry->newEntryGui(false) ?>
			<?php $eiHtml->entryOpen('div', $eiuEntryGui, array('class' => 'rocket-impl-summary')) ?>
				<div class="rocket-impl-handle"></div>
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
		<?php endif ?>
	
		<?php $eiuEntryGui = $eiuEntry->newEntryGui(true)->allowControls() ?>
		<?php $eiHtml->entryOpen('div', $eiuEntryGui, array('class' => 'rocket-impl-body rocket-group rocket-light-group')) ?>
			<label><?php $html->out($eiuEntry->createIdentityString()) ?></label>
			<div class="rocket-control">
				<?php $view->import($eiuEntryGui->createView($view)) ?>
			</div>
			
			<div class="rocket-zone-commands">
				<?php $eiHtml->entryCommands(false) ?>
			</div>
		<?php $eiHtml->entryClose() ?>
	<?php endif ?>
</div>