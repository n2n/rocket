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