<?php 
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\ei\util\model\EiuEntryGui;
	use n2n\web\dispatch\map\PropertyPath;
	use rocket\ei\manage\EiHtmlBuilder;
use n2n\l10n\N2nLocale;
use rocket\ei\manage\gui\GuiIdPath;

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
	
	$guiIdPath = $view->getParam('guiIdPath');
	$view->assert($guiIdPath instanceof GuiIdPath);
	
	$eiHtml = new EiHtmlBuilder($view);
?>

<?php $formHtml->openPseudo($dispatchable, $propertyPath) ?>
	<?php $eiHtml->entryOpen('div', $eiuEntryGui, array('class' => 'rocket-impl-translation-copy')) ?>
		<?php $eiHtml->fieldOpen('div', $guiIdPath) ?>
			<?php $eiHtml->fieldLabel(array('title' => $n2nLocale->getName($request->getN2nLocale()), 
						'class' => 'rocket-impl-locale-label'), $n2nLocale->toPrettyId()) ?>
			<div class="rocket-control">
				<?php $eiHtml->fieldContent() ?>
				<?php $eiHtml->fieldMessage() ?>
			</div>
		<?php $eiHtml->fieldClose() ?>
	<?php $eiHtml->entryClose() ?>
<?php $formHtml->closePseudo() ?>