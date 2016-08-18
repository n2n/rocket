<?php
	namespace rocket\script\config\view; 

	use rocket\script\config\model\ConfigTemplateModel;
	use rocket\script\config\model\ConfigNavItem;
		
	$configTemplateModel = $view->getParam('configTemplateModel');
	$view->assert($configTemplateModel instanceof ConfigTemplateModel);

	$view->useTemplate('core\view\template.html', array('title' => $view->getParam('title')));
?>
<?php $view->importContentView() ?>

<?php $view->panelStart('additional', true) ?>
	<aside>
		<h2><?php $html->text('script_title') ?></h2>
		<ul>
			<?php foreach ($configTemplateModel->getNavItems() as $navItem): ?>
				<li><?php printNavItem($navItem, $html) ?></li>
			<?php endforeach ?>
		</ul>
	</aside>
<?php $view->panelEnd() ?>

<?php function printNavItem(ConfigNavItem $navItem, $html) { ?>
	<?php $html->linkToController($navItem->getPathExt(), $navItem->getLabel(),
			array('class' => ($navItem->isActive() ? 'rocket-active' : null))) ?>
	<span><?php $html->out($navItem->getModuleNamespace()) ?></span>
	
	<?php if ($navItem->hasChildren()):  ?>
		<ul>
			<?php foreach ($navItem->getChildren() as $childNavItem): ?>
				<li><?php printNavItem($childNavItem, $html) ?></li>
			<?php endforeach ?>
		</ul>
	<?php endif ?>	
<?php } ?>