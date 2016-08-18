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

	use n2n\web\ui\view\impl\html\HtmlView;
	use rocket\spec\ei\manage\critmod\quick\impl\form\QuickSearchForm;
	use n2n\web\ui\Raw;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$quickSearchForm = $view->getParam('quickSearchForm');
	$this->assert($quickSearchForm instanceof QuickSearchForm);
?>

<div class="rocket-quicksearch<?php $view->out($quickSearchForm->isActive() ? ' rocket-active' : '') ?>">
	<?php $formHtml->open($quickSearchForm) ?>
		<?php $formHtml->label('searchStr', $html->getL10nText('common_search_label'), array('class' => 'rocket-quicksearch-label')) ?>
		<?php $formHtml->input('searchStr', array('class' => 'rocket-search-input'), 'search') ?>
		<span class="rocket-quicksearch-command rocket-simple-controls">
			<?php $formHtml->buttonSubmit('search', new Raw('<i class="fa fa-search"></i>'),
					array('class' => 'rocket-control rocket-command-lonely-appended',
							'title' => $view->getL10nText('ei_impl_list_quicksearch_tooltip'))) ?>
			<?php $formHtml->buttonSubmit('clear', new Raw('<i class="fa fa-eraser"></i>'),
					array('class' => 'rocket-control rocket-command-lonely-appended',
							'title' => $view->getL10nText('ei_impl_list_quicksearch_erase_tooltip'))) ?>
		</span>
	<?php $formHtml->close() ?>
</div>
