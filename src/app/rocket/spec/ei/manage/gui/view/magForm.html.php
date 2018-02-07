<?php
	use n2n\web\dispatch\mag\UiOutfitter;
	use n2n\impl\web\ui\view\html\HtmlView;

	$view = HtmlView::view($view);
	$formHtml = HtmlView::formHtml($view);
	
	$uo = $view->getParam('uo');
	$view->assert($uo instanceof UiOutfitter);
?>

<?php $formHtml->meta()->objectProps(null, function () use ($formHtml, $uo) { ?>
	<?php $formHtml->magOpen('div', null, array('class' => 'rocket-item'), $uo) ?>
		<?php $formHtml->magLabel() ?>
		
		<div class="rocket-control">
			<?php $formHtml->magField() ?>
			<?php $formHtml->message() ?>
		</div>
	<?php $formHtml->magClose() ?>
<?php }) ?>