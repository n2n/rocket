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
	use rocket\spec\ei\component\field\impl\ci\model\PanelConfig;
	use n2n\util\uri\Url;

	$view = HtmlView::view($view);
	$html = HtmlView::html($view);
	$formHtml = HtmlView::formHtml($view);
	
	$panelConfigs = $view->getParam('panelConfigs');
	$view->assert(is_array($panelConfigs));

	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$ciEiSpecLabels = $view->getParam('ciEiSpecLabels');
	$view->assert(is_array($ciEiSpecLabels));
?>

<div class="rocket-content-items" data-ci-ei-spec-labels="<?php $html->out(json_encode($ciEiSpecLabels)) ?>">
	<?php foreach ($panelConfigs as $panelConfig): $view->assert($panelConfig instanceof PanelConfig) ?>
		<?php $formHtml->magOpen('div', $propertyPath->ext($panelConfig->getName()),
				array('class' => 'rocket-content-item-panel', 'data-name' => $panelConfig->getName(),
						'data-allowed-ci-spec-ids' => $panelConfig->isRestricted() 
								? json_encode(array_values($panelConfig->getAllowedContentItemIds())) : null)) ?>
			<h4><?php $html->out($panelConfig->getLabel()) ?></h4>
			
			<?php $formHtml->magField() ?>
		<?php $formHtml->magClose() ?>
	<?php endforeach ?>	
</div>
