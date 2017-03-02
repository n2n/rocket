<?php
	/*
	 * Copyright (c) 2012-2016, Hofmänner New Media.
	 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
	 *
	 * This file is part of the n2n module ROCKET.
	 *
	 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
	 * GNU Lesser General Public License as published by the Free Software Foundation, either
	 * version 2.1 of the License, or (at your option) any later version.
	 *
	 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
	 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
	 *
	 * The following people participated in this project:
	 *
	 * Andreas von Burg...........:	Architect, Lead Developer, Concept
	 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
	 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
	 */

	use rocket\spec\ei\component\command\impl\tree\model\TreeMoveModel;
	use n2n\web\ui\Raw;
	
	$treeMoveModel = $view->params['treeMoveModel']; 
	$view->assert($treeMoveModel instanceof TreeMoveModel);
	
	$view->useTemplate('~\core\view\template.html',
			array('title' => $treeMoveModel->getTitle()));
	
	$eiFrame = $treeMoveModel->getEiFrame(); 
?>

<?php $formHtml->open($treeMoveModel, null, null, array('class' => 'rocket-edit-form')) ?>
	<div class="rocket-panel">
		<h3><?php $html->l10nText('ei_impl_tree_move_title') ?></h3>
		<div class="rocket-edit-content">
			<ul class="rocket-edit-content-entries">
				<li>
					<?php $formHtml->label('parentId', $html->getText('ei_impl_tree_move_parent_select_label')) ?>
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
							. $html->getL10nText('ei_impl_tree_move_label') . '</span>')) ?>
			</li>
		</ul>
	</div>
<?php $formHtml->close()?>
