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

	use rocket\spec\ei\manage\EntryViewInfo;
	use n2n\impl\web\ui\view\html\HtmlView;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$request = HtmlView::request($this);
	
	$entryCommandViewModel = $view->params['entryViewInfo']; 
	$view->assert($entryCommandViewModel instanceof EntryViewInfo);
?>
<select class="rocket-paging">
	<?php foreach ($entryCommandViewModel->getPreviewTypeNavInfos() as $previewType => $navPoint): ?>
		<option value="<?php $html->out($request->getControllerContextPath($view->getControllerContext(), $navPoint['pathExt'])) ?>"
				<?php $html->out($navPoint['active'] ? ' selected="selected"' : '') ?>>
			<?php $html->out($navPoint['label']) ?>
		</option>
	<?php endforeach ?>
</select>
