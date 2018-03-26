<?php 
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\ei\util\model\EiuEntryGui;
	use n2n\web\dispatch\map\PropertyPath;
	use rocket\ei\manage\EiHtmlBuilder;
	use n2n\l10n\N2nLocale;

	$view = HtmlView::view($this);
	$html = HtmlView::html($view);
	$formHtml = HtmlView::formHtml($view);
	$request = HtmlView::request($view);
	
	$eiuEntryGui = $view->getParam('eiuEntryGui');
	$view->assert($eiuEntryGui instanceof EiuEntryGui);
	
	$dispatchable = $eiuEntryGui->getEiEntryGui()->getDispatchable();
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$n2nLocale = $view->getParam('n2nLocale');
	$view->assert($n2nLocale instanceof N2nLocale);
	
	$guiIdPaths = $view->getParam('guiIdPaths');
	
	$eiHtml = new EiHtmlBuilder($view);
?>
<?php if ($dispatchable !== null): ?>
	<?php $formHtml->openPseudo($dispatchable, $propertyPath) ?>
<?php else: ?>
	<div>
<?php endif ?>

<?php $eiHtml->entryOpen('div', $eiuEntryGui, array('class' => 'rocket-impl-translation-src')) ?>
	<?php foreach ($guiIdPaths as $guiIdPath): ?>
		<div data-rocket-impl-gui-id-path="<?php $html->out($guiIdPath) ?>">
			<?php $eiHtml->fieldOpen('div', $guiIdPath) ?>
				<?php $eiHtml->fieldLabel(array('title' => $n2nLocale->getName($request->getN2nLocale()), 
							'class' => 'rocket-impl-locale-label'), $n2nLocale->toPrettyId()) ?>
				<div class="rocket-control">
					<?php $eiHtml->fieldContent() ?>
					<?php $eiHtml->fieldMessage() ?>
				</div>
			<?php $eiHtml->fieldClose() ?>
		</div>
	<?php endforeach ?>
<?php $eiHtml->entryClose() ?>

<?php if ($dispatchable !== null): ?>
	<?php $formHtml->closePseudo() ?>
<?php else:  ?>
	</div>
<?php endif ?>