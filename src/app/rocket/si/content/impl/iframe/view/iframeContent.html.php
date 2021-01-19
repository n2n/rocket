<?php
$useTemplate = $view->getParam('useTemplate');
$view->assert(is_bool($useTemplate));

if ($useTemplate) {
	$view->useTemplate('iframeTemplate.html');
}
?>

iframecontent
