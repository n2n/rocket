<?php

namespace rocket\ui\gui\field\impl\iframe;

use rocket\ui\gui\field\impl\InGuiFieldAdapter;
use n2n\core\container\N2nContext;
use rocket\ui\si\content\impl\iframe\IframeInSiField;

class IframeInGuiField extends InGuiFieldAdapter {

	function __construct(private IframeInSiField $siField) {
		parent::__construct($siField);
	}

	function setParams(array $params): static {
		$this->siField->setParams($params);
		return $this;
	}

	protected function createInputMappers(N2nContext $n2nContext): array {
		return [];
	}
}