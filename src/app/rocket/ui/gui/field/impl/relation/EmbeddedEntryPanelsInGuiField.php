<?php

namespace rocket\ui\gui\field\impl\relation;

use rocket\ui\gui\field\impl\InGuiFieldAdapter;
use n2n\core\container\N2nContext;
use n2n\bind\mapper\impl\Mappers;

class EmbeddedEntryPanelsInGuiField extends InGuiFieldAdapter {

	private array $guiPanels;

	protected function createInputMappers(N2nContext $n2nContext): array {
		return [Mappers::valueClosure(function (array $siPanels) {

		})];
	}
}