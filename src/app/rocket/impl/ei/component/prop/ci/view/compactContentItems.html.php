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
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);

	$panelConfigs = $view->getParam('panelConfigs');
	$view->assert(is_array($panelConfigs));

	$groupedUiComponents = $view->getParam('groupedUiComponents');
	$view->assert(is_array($groupedUiComponents));
	
	$i = 0;
?>
<div class="rocket-content-nested rocket-content-item-composer">
	<?php foreach ($panelConfigs as $panelConfig): ?>
		<div>
			<?php if (!isset($groupedUiComponents[$panelConfig->getName()])): ?>
				<div class="rocket-impl-content-type rocket-impl-content-type-empty">
					<span class="fa fa-rocket-empty" title="<?php $html->text('common_empty_label') ?>"></span>
				</div>
			<?php else: ?>
				<ul class="list-inline rocket-content-item">
					<?php foreach ($groupedUiComponents[$panelConfig->getName()] as $uiComponent): ?>
						<?php $view->out($uiComponent) ?>
					<?php endforeach ?>
				</ul>
			<?php endif ?>
		</div>
	<?php endforeach ?>
</div>
