<?php	
	use rocket\script\entity\field\impl\string\command\UrlModel;
	
	$view->useTemplate('core\view\template.html',
			array('title' => $view->getL10nText('script_cmd_url_edit_title')));
	$urlModel = $view->getParam('urlModel');
	$view->assert($urlModel instanceof UrlModel);
?>


<?php $formHtml->open($urlModel) ?>
	<?php $formHtml->inputField('url') ?>
<?php $formHtml->close() ?>