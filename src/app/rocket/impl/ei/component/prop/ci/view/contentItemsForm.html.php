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

	use n2n\web\dispatch\map\PropertyPath;
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\impl\ei\component\prop\ci\model\PanelConfig;
	use rocket\impl\ei\component\prop\ci\model\PanelLayout;

	$view = HtmlView::view($view);
	$html = HtmlView::html($view);
	$formHtml = HtmlView::formHtml($view);
	
	$panelLayout = $view->getParam('panelLayout');
	$view->assert($panelLayout instanceof PanelLayout);

	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$ciEiTypeLabels = $view->getParam('ciEiTypeLabels');
	$view->assert(is_array($ciEiTypeLabels));
	
	$divClass = 'rocket-impl-content-items' . ($panelLayout->hasGrid() ? ' rocket-impl-grid' : '');
?>

<div class="<?php $view->out($divClass) ?>"<?php $view->out($panelLayout->hasGrid() ? ' style="grid-template-columns: repeat(' . ($panelLayout->getNumGridCols() - 1). ', 1fr)"' : null) ?>>
	<?php foreach ($panelLayout->getPanelConfigs() as $panelConfig): $view->assert($panelConfig instanceof PanelConfig) ?>
		<?php $gridPos = $panelConfig->getGridPos() ?>
	
		<?php $formHtml->magOpen('div', $propertyPath->ext($panelConfig->getName()),
				array('class' => 'rocket-impl-content-item-panel rocket-group rocket-simple-group',
						'data-name' => $panelConfig->getName(),
						'style' => ($gridPos === null ? null : 'grid-column-start: ' . $gridPos->getColStart() . '; grid-column-end: ' . $gridPos->getColEnd() 
								. '; grid-row-start: ' . $gridPos->getRowStart() . '; grid-row-end: ' . $gridPos->getRowEnd()))) ?>
			<?php $formHtml->magLabel() ?>
			<div class="rocket-control">
				<?php $formHtml->magField() ?>
			</div>
		<?php $formHtml->magClose() ?>
	<?php endforeach ?>	
</div>
