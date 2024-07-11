<?php

namespace rocket\ui\gui\field\impl;

use rocket\ui\si\content\SiFieldModel;
use rocket\ui\si\content\BackableSiField;
use rocket\ui\si\content\SiField;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\ui\gui\field\GuiFieldModel;
use rocket\ui\gui\field\BackableGuiField;
use n2n\l10n\Message;
use n2n\l10n\N2nLocale;
use n2n\util\type\ArgUtils;

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

	function getMessageStrs(): array {
		if ($this->model === null) {
			return [];
		}

		$messages = $this->model->getMessages();
		ArgUtils::valArrayReturn($messages, $this->model, 'getMessages', Message::class);
		return array_map(fn (Message $m) => $m->t(N2nLocale::getAdmin()), $messages);
	}

	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
}