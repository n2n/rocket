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

	use rocket\spec\ei\manage\EiHtmlBuilder;
	use n2n\ui\view\impl\html\HtmlView;
	use rocket\spec\ei\component\command\impl\tree\model\TreeListModel;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	
	$treeListModel = $view->getParam('treeListModel'); 
	$view->assert($treeListModel instanceof TreeListModel);
	
	$treeListView = $view->getParam('treeListView');
	$view->assert($treeListView instanceof HtmlView);
	
	$view->useTemplate('~\core\view\template.html',
			array('title' => $treeListModel->getEiState()->getContextEiMask()->getLabel()));
	
	$eiHtml = new EiHtmlBuilder($view, $treeListModel->getEiState(), $treeListModel);
	
?>	
<div class="rocket-panel">
	<h3><?php $html->l10nText('ei_impl_list_title') ?></h3>
	
	<?php $view->out($treeListView)?>
	
	<div id="rocket-page-controls">
		<?php $eiHtml->overallControlList() ?>
	</div>
</div>
