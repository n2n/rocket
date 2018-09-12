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

	use rocket\impl\ei\component\command\common\model\OverviewModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\ei\util\gui\EiuHtmlBuilder;
		
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$overviewModel = $view->getParam('listModel'); 
	$view->assert($overviewModel instanceof OverviewModel);
	
	$view->useTemplate('~\core\view\template.html',
			array('title' => $overviewModel->getEiuFrame()->getGenericLabel()));
	
	$eiMask = $overviewModel->getEiuFrame()->getContextEiMask();
	
	$eiuHtml = new EiuHtmlBuilder($view);
?>	


<div class="rocket-impl-overview" 
		data-num-pages="<?php $html->out($overviewModel->getNumPages()) ?>"
		data-num-entries="<?php $html->out($overviewModel->getNumEntries()) ?>"
		data-page-size="<?php $html->out($overviewModel->getPageSize()) ?>"
		data-current-page="<?php $html->out($overviewModel->getCurrentPageNo()) ?>"
		data-overview-path="<?php $html->out($html->meta()->getControllerUrl(null)) ?>">
		
	<?php if ($eiMask->isDraftingEnabled()): ?>
		<div class="rocket-zone-toolbar">
			<ul class="rocket-draft-nav">
				<li><?php $html->linkToController(null, $html->getText('ei_impl_list_title'), array('class' => 'active')) ?></li>
				<li><?php $html->linkToController('drafts', $html->getText('ei_impl_drafts_title')) ?></li>
				<li><?php $html->linkToController('recovery', $html->getText('ei_impl_recovery_title')) ?></li>
			</ul>
		</div>
	<?php endif ?>
	
	<?php $view->import('inc\overviewTools.html',
			$view->mergeParams(array('critmodForm' => $overviewModel->getCritmodForm(), 
					'quickSearchForm' => $overviewModel->getQuickSearchForm(),
					'label' => $eiMask->getLabelLstr()->t($view->getN2nLocale()), 
					'pluralLabel' => $eiMask->getPluralLabelLstr()->t($view->getN2nLocale())))) ?>
	
	<?php $formHtml->open($overviewModel) ?>
		
		<?php $view->out($overviewModel->getEiuGui()->createView($view)) ?>
		
		<div class="rocket-zone-commands">
			<?php /* Bert: do not display UL with no LI contents ?>
			<ul class="rocket-partial-controls">
				<li><?php / * partial control components * / ?></li>
			</ul>
			<?php */ ?>
			<?php $eiuHtml->frameCommands($overviewModel->getEiuGui()) ?>
			
		</div>
	<?php $formHtml->close() ?>
</div>