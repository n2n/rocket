<?php 
	use rocket\user\model\LoginContext;

	$loginContext = $view->lookup('rocket\user\model\LoginContext'); 
	$view->assert($loginContext instanceof LoginContext);
?>
<?php
	$view->useTemplate('core\view\template.html', array('title' => 'Page not found'));
?>

<p>
	<?php $html->linkToController(null, 'Go to startpage', null, null, null, 'rocket\core\controller\RocketController') ?>
</p>