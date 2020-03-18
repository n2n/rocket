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
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;
use rocket\si\input\SiEntryInput;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\content\impl\relation\EmbeddedEntryInputHandler;
use rocket\si\content\impl\relation\SiEmbeddedEntry;
use rocket\ei\util\gui\EiuEntryGui;
use rocket\ei\util\spec\EiuMask;
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
	 * @var EiuEntryGui
	 */
	private $currentEiuEntryGui = [];
	
	function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationModel $relationModel) {
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
		$this->relationModel = $relationModel;
		
		$this->siField = SiFields::embeddedEntryIn($this->targetEiuFrame->getSiTypeCategory(),
						$this->targetEiuFrame->getApiUrl($relationModel->getTargetEditEiCommandPath()),
						$this, $this->readValues(), (int) $relationModel->getMin(), $relationModel->getMax())
				->setReduced($this->relationModel->isReduced())
				->setNonNewRemovable($this->relationModel->isRemovable())
				->setAllowedTypeQualifiers(array_map(
						function (EiuMask $eiuMask) { return $eiuMask->createSiTypeQualifier(); },
						$targetEiuFrame->engine()->mask()->possibleMasks()));
	}
	
	/**
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry
	 */
	private function readValues() {
		$this->currentEiuEntryGui = null;
		
		if (null !== ($eiuEntry = $this->eiu->field()->getValue())) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$this->currentEiuEntryGui = $eiuEntry->newGui(true, true)->entryGui();
			return [$this->createSiEmbeddeEntry($this->currentEiuEntryGui)];
		}
		
		return [];
	}
	
	/**
	 * @param EiuEntryGui $eiuEntryGui
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry
	 */
	private function createSiEmbeddeEntry($eiuEntryGui) {
		return new SiEmbeddedEntry(
				$eiuEntryGui->createBulkyEntrySiComp(false, false),
				($this->relationModel->isReduced() ?
						$eiuEntryGui->entry()->newGui(false, false)->entryGui()->createCompactEntrySiComp(false, false):
						null));
	}

	/**
	 * @param SiEntryInput[] $siEntryInputs
	 * @throws CorruptedSiInputDataException
	 */
	function handleInput(array $siEntryInputs): array {
		if (empty($siEntryInputs)) {
			return [];
		}
		
		if (count($siEntryInputs) > 1) {
			throw new CorruptedSiInputDataException('Too many SiEntryInputs for EmbeddedToOneGuiField.');
		}
		
		$siEntryInput = current($siEntryInputs);
		CastUtils::assertTrue($siEntryInput instanceof SiEntryInput);
		
		$id = $siEntryInput->getIdentifier()->getId();
			
		if ($this->currentEiuEntryGui !== null && $id !== null && $this->currentEiuEntryGui->entry()->hasId()
				&& $id == $this->currentEiuEntryGui->entry()->getPid()) {
			$this->currentEiuEntryGui->handleSiEntryInput($siEntryInput);
			return $this->createSiEmbeddeEntry($this->currentEiuEntryGui);
		}
			
		$eiuEntryGuiMulti = $this->targetEiuFrame->newEntryGuiMulti(true, false)
				->handleSiEntryInput($siEntryInput);
		$this->currentEiuEntryGui = $eiuEntryGuiMulti->selectedEntryGui();
		return $this->createSiEmbeddeEntry($this->currentEiuEntryGui);
	}
	
	function save() {
		$value = null;
		
		if ($this->currentEiuEntryGui !== null) {
			$this->currentEiuEntryGui->save();
			$value = $this->currentEiuEntryGui->entry();
		}
		
		$this->eiu->field()->setValue($value);
	}
	
	function getSiField(): SiField {
		return $this->siField;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
}