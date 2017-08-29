<?php
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\web\ui\view\View;
	use rocket\spec\ei\manage\util\model\EiuEntry;
	use rocket\spec\ei\manage\EiHtmlBuilder;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	
	$eiuEntry = $view->getParam('eiuEntry');
	$view->assert($eiuEntry instanceof EiuEntry);
	
	$eiHtml = new EiHtmlBuilder($view);
?>

<div class="rocket-impl-entry">
	<?php if (!$eiuEntry->isAccessible()): ?>
		<div class="rocket-impl-summary">
			<div class="rocket-impl-handle"></div>
			<div class="rocket-impl-content">
				<div>
					<i class="<?php $html->out($eiuEntry->getIconTyp()) ?>"></i>
					<?php $html->out($eiuEntry->getGenericLabel()) ?>
				</div>
				<div>
					<?php $html->text('ei_impl_not_accessible', array('entry' => $eiuEntry->createIdentityString())) ?>
				</div>
			</div>
		</div>
		
		<div class="rocket-impl-body rocket-group">
			<label><?php $html->out($eiuEntry->createIdentityString()) ?></label>
			<div class="rocket-controls">
				<?php $html->text('ei_impl_not_accessible', array('entry' => $eiuEntry->createIdentityString())) ?>
			</div>
		</div>
	<?php else: ?>
		<?php $eiuEntryGui = $eiuEntry->newEntryGui(false) ?>
		<?php $eiHtml->entryOpen('div', $eiuEntryGui, array('class' => 'rocket-impl-summary')) ?>
			<div class="rocket-impl-handle"></div>
			<div class="rocket-impl-content">
				<div>
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
	
		<?php $eiuEntryGui = $eiuEntry->newEntryGui(true) ?>
		<?php $eiHtml->entryOpen('div', $eiuEntryGui, array('class' => 'rocket-impl-body rocket-group')) ?>
			<label><?php $html->out($eiuEntry->createIdentityString()) ?></label>
			<div class="rocket-controls">
				<?php $view->import($eiuEntryGui->createView()) ?>
			</div>
			
			<div class="rocket-context-commands">
				<?php $eiHtml->entryCommands(false) ?>
			</div>
		<?php $eiHtml->entryClose() ?>
	<?php endif ?>
</div>