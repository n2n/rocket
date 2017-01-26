<?php

namespace rocket\spec\ei\component\field\impl\enum;

use rocket\spec\ei\component\field\impl\adapter\DisplayableEiFieldAdapter;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\gui\FieldSourceInfo;

class EnumGuiGroupEiField extends DisplayableEiFieldAdapter {
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\impl\adapter\StatelessDisplayable::createOutputUiComponent()
	 */
	public function createOutputUiComponent(HtmlView $view, FieldSourceInfo $entrySourceInfo) {
		
	}


}

