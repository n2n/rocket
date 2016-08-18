<?php 
	use rocket\script\entity\manage\ScriptHtmlBuilder;
	use n2n\ui\html\HtmlView;
	use rocket\script\entity\command\impl\tree\model\TreeListModel;
	
	$treeListModel = $view->getParam('treeListModel'); 
	$view->assert($treeListModel instanceof TreeListModel);
	
	$treeListView = $view->getParam('treeListView');
	$view->assert($treeListView instanceof HtmlView);
	
	$view->useTemplate('core\view\template.html',
			array('title' => $treeListModel->getScriptState()->getScriptMask()->getLabel()));
	
	$scriptHtml = new ScriptHtmlBuilder($view, $treeListModel);
	
?>	
<div class="rocket-panel">
	<h3><?php $html->l10nText('script_impl_list_title') ?></h3>
	
	<?php $view->out($treeListView)?>
	
	<div id="rocket-page-controls">
		<?php $scriptHtml->overallControlList() ?>
	</div>
</div>