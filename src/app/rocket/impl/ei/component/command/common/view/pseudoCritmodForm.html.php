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
	use n2n\web\dispatch\map\PropertyPath;
	use rocket\ei\util\filter\form\FilterGroupForm;
	use rocket\ei\util\filter\controller\FilterJhtmlHook;
	use rocket\ei\util\sort\form\SortForm;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$filterGroupForm = $view->getParam('filterGroupForm');
	$view->assert($filterGroupForm instanceof FilterGroupForm);
	
	$filterJhtmlHook = $view->getParam('filterJhtmlHook');
	$view->assert($filterJhtmlHook instanceof FilterJhtmlHook);

	$sortForm = $view->getParam('sortForm');
	$view->assert($sortForm instanceof SortForm);
?>

<fieldset>
	<h4><?php $html->l10nText('ei_impl_filter_title') ?></h4>
	<?php $formHtml->openPseudo($filterGroupForm, new PropertyPath(array('filterForm'))) ?>
		<?php $view->import('ei\manage\critmod\filter\impl\view\filterForm.html', 
				array('propertyPath' => $formHtml->meta()->createPropertyPath(),
						'filterJhtmlHook' => $filterJhtmlHook)) ?>
	<?php $formHtml->closePseudo() ?>
</fieldset>
<fieldset>
	<h4><?php $html->l10nText('ei_impl_sort_title') ?></h4>
	<?php $formHtml->openPseudo($sortForm, new PropertyPath(array('sortForm'))) ?>
		<?php $view->import('ei\manage\critmod\sort\impl\view\sortForm.html', 
				array('propertyPath' => $formHtml->meta()->createPropertyPath())) ?>
	<?php $formHtml->closePseudo() ?>
</fieldset>
