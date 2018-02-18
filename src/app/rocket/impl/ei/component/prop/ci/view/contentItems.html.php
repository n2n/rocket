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

	$groupedUiComponents = $view->getParam('groupedUiComponents');
	$view->assert(is_array($groupedUiComponents));
?>
<div<?php $view->out($panelLayout->hasGrid() ? ' style="display:grid"' : null) ?>>
	<?php foreach ($panelLayout->getPanelConfigs() as $panelConfig): ?>
		<?php $gridPos = $panelConfig->getGridPos() ?>
		
		<h4><?php $html->out($panelConfig->getLabel()) ?></h4>
		<div<?php $view->out($gridPos === null ? null : ' style="grid-column-start: ' . $gridPos->getColStart() . '; grid-column-end: ' . $gridPos->getColEnd() 
								. '; grid-row-start: ' . $gridPos->getRowStart() . '; grid-row-end: ' . $gridPos->getRowEnd()) . '"' ?>>
			<?php if (!isset($groupedUiComponents[$panelConfig->getName()])): ?>
				<?php $html->text('common_empty_label') ?>
			<?php else: ?>
				<?php foreach ($groupedUiComponents[$panelConfig->getName()] as $uiComponent): ?>
					<?php $view->out($uiComponent) ?>
				<?php endforeach ?>
			<?php endif ?>
		</div>
	<?php endforeach ?>
</div>
