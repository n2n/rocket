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
namespace rocket\impl\ei\component\prop\relation\model\gui;

use rocket\op\ei\util\Eiu;
use rocket\ui\si\content\SiField;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\ui\gui\field\GuiField;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ui\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\ui\si\content\SiFieldModel;
use n2n\core\container\N2nContext;

class RelationLinkGuiField implements GuiField, SiFieldModel {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	/**
	 * @var SiField
	 */
	private $siField;
	
	function __construct(Eiu $eiu, RelationModel $relationModel) {
		$this->eiu = $eiu;
		$this->relationModel = $relationModel;
		
		if ($relationModel->isTargetMany()) {
			$this->siField = $this->createToManySiField();
		} else {
			$this->siField = $this->createToOneSiField();
		}
	}
	

	

	
	function getSiField(): SiField {
		return $this->siField;
	}

	function getValue(): mixed {
		return $this->siField->getValue();
	}

	function getContextSiFields(): array {
		return [];
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
	
	function save(N2nContext $n2nContext): void {
//		throw new UnsupportedOperationException();
	}

	function handleInput(mixed $value, N2nContext $n2nContext): bool {
		throw new UnsupportedOperationException();
	}

	function getMessageStrs(): array {
		return $this->eiu->field()->getMessagesAsStrs();
	}
}