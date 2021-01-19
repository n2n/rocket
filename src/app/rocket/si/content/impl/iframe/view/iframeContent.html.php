<?php
$useTemplate = $view->getParam('useTemplate');
$view->assert($useTemplate instanceof bool);

if ($useTemplate) {
	$view->useTemplate('rocket\si\content\impl\iframeTemplate.html');
}
?>

iframecontent
