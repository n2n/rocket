<?php 
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\ei\util\gui\EiuEntryGui;
	use n2n\web\dispatch\map\PropertyPath;
	use rocket\ei\util\gui\EiuHtmlBuilder;
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
	
	$eiuHtml = new EiuHtmlBuilder($view);
?>

<?php $formHtml->openPseudo($dispatchable, $propertyPath) ?>
	
	<?php $eiuHtml->entryOpen('div', $eiuEntryGui, array('class' => 'rocket-impl-translation-src')) ?>
		<?php foreach ($guiIdPaths as $guiIdPath): ?>
			<div data-rocket-impl-gui-id-path="<?php $html->out($guiIdPath) ?>">
				<?php $eiuHtml->fieldOpen('div', $guiIdPath, null, false, false) ?>
					<?php $eiuHtml->fieldLabel(array('title' => $n2nLocale->getName($request->getN2nLocale()), 
								'class' => 'rocket-impl-locale-label'), $n2nLocale->toPrettyId()) ?>
					<div class="rocket-control">
						<?php $eiuHtml->fieldContent() ?>
						<?php $eiuHtml->fieldMessage() ?>
					</div>
				<?php $eiuHtml->fieldClose() ?>
			</div>
		<?php endforeach ?>
	<?php $eiuHtml->entryClose() ?>

<?php $formHtml->closePseudo() ?>