<?php

namespace rocket\ui\gui\field\impl;

use rocket\ui\si\content\BackableSiField;
use n2n\util\type\ArgUtils;
use n2n\core\container\N2nContext;
use n2n\util\ex\UnsupportedOperationException;

abstract class OutGuiFieldAdapter extends GuiFieldAdapter {

	protected function __construct(private BackableSiField $siField) {
		ArgUtils::assertTrue($this->siField->isReadOnly(), 'SiField must not readOnly.');
		parent::__construct($siField);
	}

	function prepareForSave(N2nContext $n2nContext): bool {
		throw new UnsupportedOperationException('GuiField is read only.');
	}

	function save(N2nContext $n2nContext): void {
		throw new UnsupportedOperationException('GuiField is read only.');
	}

	function handleInput(mixed $value, N2nContext $n2nContext): bool {
		throw new UnsupportedOperationException('GuiField is read only.');
	}

	function flush(N2nContext $n2nContext): void {
		throw new UnsupportedOperationException('GuiField is read only.');
	}
}