<?php 
	use rocket\script\entity\command\impl\tree\model\TreeMoveModel;
use n2n\ui\Raw;
	
	$treeMoveModel = $view->params['treeMoveModel']; 
	$view->assert($treeMoveModel instanceof TreeMoveModel);
	
	$view->useTemplate('core\view\template.html',
			array('title' => $treeMoveModel->getTitle()));
	
	$scriptState = $treeMoveModel->getScriptState(); 
?>

<?php $formHtml->open($treeMoveModel, null, null, array('class' => 'rocket-edit-form')) ?>
	<div class="rocket-panel">
		<h3><?php $html->l10nText('script_cmd_tree_move_title') ?></h3>
		<div class="rocket-edit-content">
			<ul class="rocket-edit-content-entries">
				<li>
					<?php $formHtml->label('parentId', $html->getText('script_cmd_tree_move_parent_select_label')) ?>
					<div class="rocket-controls">
						<?php $formHtml->select('parentId', $treeMoveModel->getParentIdOptions()) ?>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<div id="rocket-page-controls">
		<ul>
			<li class="rocket-control-warning">
				<?php $formHtml->buttonSubmit('move', new Raw('<i class="fa fa-save"></i><span>' 
							. $html->getL10nText('script_cmd_tree_move_label') . '</span>')) ?>
			</li>
		</ul>
	</div>
<?php $formHtml->close()?>


