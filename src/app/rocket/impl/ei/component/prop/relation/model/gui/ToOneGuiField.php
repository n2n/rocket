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

use rocket\ui\gui\field\GuiFieldMap;
use rocket\ui\gui\field\GuiField;
use rocket\ui\si\content\SiField;
use rocket\op\ei\util\Eiu;
use rocket\ui\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use n2n\util\type\CastUtils;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\ui\si\content\impl\relation\QualifierSelectInSiField;
use n2n\util\ex\NotYetImplementedException;
use rocket\ui\gui\field\impl\InGuiFieldAdapter;
use n2n\core\container\N2nContext;
use n2n\util\type\ArgUtils;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\op\ei\manage\entry\UnknownEiObjectException;

class ToOneGuiField extends InGuiFieldAdapter {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var Eiu
	 */
	private $targetEiu;
	/**
	 * @var QualifierSelectInSiField
	 */
	private $siField;
	
	function __construct(Eiu $eiu, RelationModel $relationModel) {
		$this->eiu = $eiu;
		
		$this->targetEiu = $eiu->frame()->forkSelect($eiu->prop()->getPath(), $eiu->entry());
		$this->targetEiu->frame()->exec($relationModel->getTargetReadEiCmdPath());
		
		$values = [];
		if (null !== ($eiuEntry = $eiu->field()->getValue())) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$values[] = $eiuEntry->createSiEntryQualifier();
		}
		
		$this->siField = SiFields::qualifierSelectIn($this->targetEiu->frame()->createSiFrame(),
						$values, ($relationModel->isMandatory() ? 1 : 0), 1, 
						$this->readPickableQualifiers($relationModel->getMaxPicksNum()));


		parent::__construct($this->siField);
	}
	
	private function readPickableQualifiers(int $maxNum) {
		if ($maxNum <= 0) {
			return null;
		}
		
		$num = $this->targetEiu->frame()->count();
		if ($num > $maxNum) {
			return null;
		}
		
		$siEntryQualifiers = [];
		foreach ($this->targetEiu->frame()->lookupObjects() as $eiuObject) {
			$siEntryQualifiers[] = $eiuObject->createSiEntryQualifier();
		}
		return $siEntryQualifiers;
	}

	function getValue(): mixed {
		throw new NotYetImplementedException();
	}
	
	function handleInput(mixed $value, N2nContext $n2nContext): void {
//		$siQualifiers = $this->siField->getValues();
		ArgUtils::valArray($value, SiEntryQualifier::class);
		$siQualifiers = $value;
		
		if (empty($siQualifiers)) {
			$this->eiu->field()->setValue(null);
			return;
		}
		
		$id = $this->targetEiu->frame()->siQualifierToId(current($siQualifiers));
		try {
			$value = $this->targetEiu->frame()->lookupEntry($id);
		} catch (UnknownEiObjectException $e) {
			$this->eiu->field()->setValue($value);
		}
	}

	function getSiField(): SiField {
		return $this->siField;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}

	protected function createInputMappers(N2nContext $n2nContext): array {
		return [];
	}
}
