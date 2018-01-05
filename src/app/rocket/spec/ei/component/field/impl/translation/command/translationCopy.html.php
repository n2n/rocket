<?php 
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\spec\ei\manage\util\model\EiuEntryGui;
	use n2n\web\dispatch\map\PropertyPath;
	use rocket\spec\ei\manage\EiHtmlBuilder;

	$view = HtmlView::view($this);
	$html = HtmlView::html($view);
	$formHtml = HtmlView::formHtml($view);
	
	$eiuEntryGui = $view->getParam('eiuEntryGui');
	$view->assert($eiuEntryGui instanceof EiuEntryGui);
	
	$dispatchable = $eiuEntryGui->getEiEntryGui()->getDispatchable();
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$eiHtml = new EiHtmlBuilder($view);
?>

<?php $formHtml->openPseudo($dispatchable, $propertyPath) ?>
	<?php $eiHtml->entryOpen('div', $eiuEntryGui) ?>
		<?php $eiHtml->fieldOpen('div', $guiIdPath, array('class' => 'rocket-impl-translation-copy')) ?>
			<?php $eiHtml->fieldContent() ?>
		<?php $eiHtml->fieldClose()?>
	<?php $eiHtml->entryClose() ?>
<?php $formHtml->closePseudo() ?>