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

	use rocket\spec\ei\component\command\impl\common\model\OverviewModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\spec\ei\manage\ControlEiHtmlBuilder;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$listModel = $view->getParam('listModel'); 
	$view->assert($listModel instanceof OverviewModel);
	
	$listView = $view->getParam('listView');
	$view->assert($listView instanceof HtmlView);
		
	$view->useTemplate('~\core\view\template.html',
			array('title' => $listModel->getEiuFrame()->getEiFrame()->getContextEiMask()->getLabelLstr()
					->t($view->getN2nLocale())));
	
	$eiMask = $listModel->getEiuFrame()->getEiFrame()->getContextEiMask();
	
	$controlEiHtml = new ControlEiHtmlBuilder($view, $listModel->getEiuFrame());
?>	

<div class="rocket-panel">
	<h3><?php $html->l10nText('ei_impl_list_title') ?></h3>
	
	<?php if ($eiMask->isDraftingEnabled()): ?>
		<div id="rocket-toolbar">
			<ul class="rocket-draft-nav">
				<li><?php $html->linkToController(null, $html->getText('ei_impl_list_title'), array('class' => 'active')) ?></li>
				<li><?php $html->linkToController('drafts', $html->getText('ei_impl_drafts_title')) ?></li>
				<li><?php $html->linkToController('recovery', $html->getText('ei_impl_recovery_title')) ?></li>
			</ul>
		</div>
	<?php endif ?>

	<?php $view->import('inc\overviewTools.html',
			array('critmodForm' => $listModel->getCritmodForm(), 
					'quickSearchForm' => $listModel->getQuickSearchForm(),
					'label' => $eiMask->getLabelLstr()->t($view->getN2nLocale()), 
					'pluralLabel' => $eiMask->getPluralLabelLstr()->t($view->getN2nLocale()))) ?>
	
	<?php $formHtml->open($listModel, null, null, array('class' => 'rocket-overview-main-content',
			'data-num-pages' => $listModel->getNumPages(), 'data-num-entries' => $listModel->getNumEntries(),
			'data-current-page' => $listModel->getCurrentPageNo(),
			'data-overview-path' => $html->meta()->getControllerUrl(null))) ?>
		
		<?php $view->out($listView)?>
		
		<div id="rocket-page-controls">
			<?php /* Bert: do not display UL with no LI contents ?>
			<ul class="rocket-partial-controls">
				<li><?php / * partial control components * / ?></li>
			</ul>
			<?php */ ?>
			
			<?php $controlEiHtml->overallControlList() ?>
		</div>
	<?php $formHtml->close() ?>
</div>
