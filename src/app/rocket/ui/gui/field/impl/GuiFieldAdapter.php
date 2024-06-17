<?php

namespace rocket\ui\gui\field\impl;

use rocket\ui\si\content\SiFieldModel;
use rocket\ui\si\content\BackableSiField;
use rocket\ui\si\content\SiField;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\ui\gui\field\GuiFieldModel;
use rocket\ui\gui\field\BackableGuiField;
use rocket\ui\gui\field\GuiField;

abstract class GuiFieldAdapter implements BackableGuiField, SiFieldModel {

	protected ?GuiFieldModel $model = null;

	protected function __construct(private BackableSiField $siField) {
		$this->siField->setModel($this);
	}


	function getSiField(): SiField {
		return $this->siField;
	}

	function setModel(?GuiFieldModel $model): static {
		$this->model = $model;
		return $this;
	}

	function getModel(): ?GuiFieldModel {
		return $this->model;
	}

	function getMessages(): array {
		return $this->model?->getMessages() ?? [];
	}

	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
}