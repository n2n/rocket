<?php 
	use rocket\script\entity\manage\ScriptHtmlBuilder;
	use rocket\script\entity\command\impl\common\model\ListModel;
	use rocket\script\entity\command\impl\common\model\ListFilterForm;
	use rocket\script\entity\command\impl\common\model\ListQuickSearchModel;
	use n2n\ui\html\HtmlView;
	
	$treeListModel = $view->getParam('listModel'); 
	$view->assert($treeListModel instanceof ListModel);
	
	$filterModel = $view->getParam('listFilterForm'); 
	$view->assert($filterModel instanceof ListFilterForm);
	$listQuickSearchModel = $view->getParam('listQuickSearchModel'); 
	$view->assert($listQuickSearchModel instanceof ListQuickSearchModel);
	$navPoints = $view->getParam('navPoints'); 
	$view->assert(is_array($navPoints));
	
	$listView = $view->getParam('listView');
	$view->assert($listView instanceof HtmlView);
	
	$view->useTemplate('core\view\template.html',
			array('title' => $treeListModel->getScriptState()->getScriptMask()->getLabel()));
	
	$html->addJs('js/script/display.js');
	
	$currentPageNo = $treeListModel->getCurrentPageNo();
	
	$scriptHtml = new ScriptHtmlBuilder($view, $treeListModel);
?>	
<div class="rocket-panel">
	<h3><?php $html->l10nText('script_impl_list_title') ?></h3>
	<!-- Paging -->
	<?php if (sizeof($navPoints)): ?>
		<div class="rocket-quick-access-panel rocket-paging">
			<label>Seite
				<select class="rocket-paging">
					<?php foreach ($navPoints as $path => $pageNo): ?>
						<?php if ($pageNo == $currentPageNo): ?>
							<option selected="selected" value="<?php $html->esc($path) ?>">
								<?php $html->esc($pageNo) ?>
							</option>
						<?php else: ?>
							<option value="<?php $html->esc($path) ?>"><?php $html->esc($pageNo) ?></option>
						<?php endif ?>
					<?php endforeach ?>
				</select>
			</label>
		</div>
	<?php endif ?>
	<!-- End Paging -->
	<div class="rocket-tool-panel">
		<?php $view->import('script\entity\command\impl\common\view\inc\listFilter.html', 
				array('listFilterForm' => $filterModel, 'listQuickSearchModel' => $listQuickSearchModel)) ?>
	</div>
	<?php $formHtml->open($treeListModel) ?>
		<?php $view->out($listView)?>
		
		<div id="rocket-page-controls">
			<ul class="rocket-partial-controls">
				<li><?php /* partial control components */ ?></li>
			</ul>
			
			<?php $scriptHtml->overallControlList() ?>
		</div>
		<?php $formHtml->hiddenCommand('executePartialCommand') ?>
	<?php $formHtml->close() ?>
</div>