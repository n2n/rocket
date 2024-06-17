<?php

namespace rocket\ui\gui\field\impl\general;

use rocket\ui\gui\field\impl\OutGuiFieldAdapter;
use rocket\ui\si\content\BackableSiField;

class OutGuiField extends OutGuiFieldAdapter {

	function __construct(BackableSiField $siField) {
		parent::__construct($siField);
	}
}