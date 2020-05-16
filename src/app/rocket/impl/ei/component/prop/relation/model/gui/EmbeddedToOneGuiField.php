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

use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\util\Eiu;
use rocket\ei\util\frame\EiuFrame;
use rocket\si\content\SiField;
use rocket\si\content\impl\relation\EmbeddedEntryInSiField;
use rocket\si\content\impl\SiFields;
use rocket\si\input\SiEntryInput;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\content\impl\relation\EmbeddedEntryInputHandler;
use rocket\ei\manage\gui\GuiFieldMap;

class EmbeddedToOneGuiField implements GuiField, EmbeddedEntryInputHandler {
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var EiuFrame
	 */
	private $targetEiuFrame;
	/**
	 * @var EmbeddedEntryInSiField
	 */
	private $siField;
	/**
	 * @var EmbeddedGuiCollection
	 */
	private $emebeddedGuiCollection;
	
	function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationModel $relationModel) {
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
		$this->relationModel = $relationModel;
		$this->emebeddedGuiCollection = new EmbeddedGuiCollection(false, $relationModel->isReduced(), 
				$this->relationModel->getMin(), $targetEiuFrame);
		
		$this->siField = SiFields::embeddedEntryIn($this->targetEiuFrame->getSiTypeCategory(),
						$this->targetEiuFrame->getApiUrl($relationModel->getTargetEditEiCommandPath()),
						$this, $this->readValues(), (int) $relationModel->getMin(), $relationModel->getMax())
				->setReduced($this->relationModel->isReduced())
				->setNonNewRemovable($this->relationModel->isRemovable());
	}
	
	/**
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry
	 */
	private function readValues() {
		$this->emebeddedGuiCollection->clear();
		
		if (null !== ($eiuEntry = $this->eiu->field()->getValue())) {
			$this->emebeddedGuiCollection->add($eiuEntry);
		}
		
		$this->emebeddedGuiCollection->fillUp();
		return $this->emebeddedGuiCollection->createSiEmbeddedEntries();
	}
	
	/**
	 * @param SiEntryInput[] $siEntryInputs
	 * @throws CorruptedSiInputDataException
	 */
	function handleInput(array $siEntryInputs): array {
		if (count($siEntryInputs) > 1) {
			throw new CorruptedSiInputDataException('Too many SiEntryInputs for EmbeddedToOneGuiField.');
		}
		
		$this->emebeddedGuiCollection->handleSiEntryInputs($siEntryInputs);
		$this->emebeddedGuiCollection->fillUp();
		return $this->emebeddedGuiCollection->createSiEmbeddedEntries();
	}
	
	function save() {
		$value = $this->emebeddedGuiCollection->save();
		
		$this->eiu->field()->setValue($value);
	}
	
	function getSiField(): SiField {
		return $this->siField;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
}