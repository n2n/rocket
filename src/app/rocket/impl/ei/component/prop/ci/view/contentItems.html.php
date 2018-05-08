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

	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\impl\ei\component\prop\ci\model\PanelLayout;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$panelLayout = $view->getParam('panelLayout');
	$view->assert($panelLayout instanceof PanelLayout);

	$groupedEiuEntries = $view->getParam('groupedEiuEntries');
	$view->assert(is_array($groupedEiuEntries));
?>
<div<?php $view->out($panelLayout->hasGrid() ? ' style="display: grid; grid-template-columns: repeat(' . ($panelLayout->getNumGridCols() - 1). ', 1fr)" class="rocket-impl-grid"' : null) ?>>
	<?php foreach ($panelLayout->getPanelConfigs() as $panelConfig): ?>
		<?php $gridPos = $panelConfig->getGridPos() ?>
		
		<div class="rocket-impl-content-items rocket-group rocket-simple-group" <?php $view->out($gridPos === null ? null : ' style="grid-column-start: ' . $gridPos->getColStart() 
				. '; grid-column-end: ' . $gridPos->getColEnd() . '; grid-row-start: ' . $gridPos->getRowStart() 
				. '; grid-row-end: ' . $gridPos->getRowEnd() . '"') ?>>
				
			<label><?php $html->out($panelConfig->getLabel()) ?></label>
			<div class="rocket-control">
				<?php if (!isset($groupedEiuEntries[$panelConfig->getName()])): ?>
					<?php $html->text('common_empty_label') ?>
				<?php else: ?>
					<?php $view->import('..\..\relation\view\embeddedOneToMany.html', 
							array('eiuEntries' => $groupedEiuEntries[$panelConfig->getName()], 'reduced' => true)) ?>
				<?php endif ?>
			</div>
		</div>
	<?php endforeach ?>
</div>
