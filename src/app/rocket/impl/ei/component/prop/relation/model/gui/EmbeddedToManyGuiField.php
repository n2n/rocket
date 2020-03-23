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
use rocket\ei\util\gui\EiuEntryGuiTypeDef;

class EmbeddedToManyGuiField implements GuiField, EmbeddedEntryInputHandler {
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
	private $currentEiuEntryGuis = [];
	
	function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationModel $relationModel) {
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
		$this->relationModel = $relationModel;
		
		$this->siField = SiFields::embeddedEntryIn($this->targetEiuFrame->getSiTypeCategory(),
						$this->targetEiuFrame->getApiUrl($relationModel->getTargetEditEiCommandPath()),
						$this, $this->readValues(), (int) $relationModel->getMin(), $relationModel->getMax())
				->setReduced($this->relationModel->isReduced())
				->setNonNewRemovable($this->relationModel->isRemovable())
				->setSortable($relationModel->getMax() > 1 && $relationModel->getTargetOrderEiPropPath() !== null)
				->setAllowedTypeQualifiers(array_map(
						function (EiuMask $eiuMask) { return $eiuMask->createSiTypeQualifier(); }, 
						$targetEiuFrame->engine()->mask()->possibleMasks()));
	}
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry[]
	 */
	private function readValues() {
		$this->currentEiuEntryGuis = [];
		
		foreach ($this->eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$this->currentEiuEntryGuis[] = $eiuEntry->newGui(true, true)->entryGui();
			break;
		}
	
		if (null !== ($targetOrderEiPropPath = $this->relationModel->getTargetOrderEiPropPath())) {
			uasort($this->currentEiuEntryGuis, function($a, $b) use ($targetOrderEiPropPath) {
				$aValue = $a->entry()->getScalarValue($targetOrderEiPropPath);
				$bValue = $b->entry()->getScalarValue($targetOrderEiPropPath);
				
				if ($aValue == $bValue) {
					return 0;
				}
				
				return ($aValue < $bValue) ? -1 : 1;
			});
		}
		
// 		$max = $this->relationModel->getMax();
// 		while ($max !== null && $max > count($this->currentEiuEntryGuis)) {
// 			$this->currentEiuEntryGuis[] = $this->targetEiuFrame->newForgeMultiEntryGui(true, false);
// 		}
		
		return array_values(array_map(
				function ($eiuEntryGui) { return $this->createSiEmbeddeEntry($eiuEntryGui); }, 
				$this->currentEiuEntryGuis));
	}
	
	/**
	 * @param EiuEntryGui $eiuEntryGui
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry
	 */
	private function createSiEmbeddeEntry($eiuEntryGui) {
		return new SiEmbeddedEntry(
				$eiuEntryGui->gui()->createBulkyEntrySiComp(false, false),
				($this->relationModel->isReduced() ? 
						$eiuEntryGui->gui()->copy(false, true)->createCompactEntrySiComp(false, false):
						null));
	}
	
	/**
	 * @param EiuEntryGuiTypeDef $eiuEntryGuiMulti
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry
	 */
	private function createSiEmbeddeEntryFromMulti($eiuEntryGuiMulti) {
		return new SiEmbeddedEntry(
				$eiuEntryGuiMulti->createBulkyEntrySiComp(false, false),
				($this->relationModel->isReduced() ?
						$eiuEntryGuiMulti->entry()->newGui(false, false)->createCompactEntrySiComp(false, false):
						null));
	}
	
	/**
	 * @param string $id
	 * @return \rocket\ei\util\gui\EiuEntryGui|NULL
	 */
	function findCurrentEiuEntryGuiById(string $id) {
		foreach ($this->currentEiuEntryGuis as $eiuEntryGui) {
			if ($eiuEntryGui->entry()->hasId() && $id == $eiuEntryGui->entry()->getPid()) {
				return $eiuEntryGui;
			}
		}

		return null;
	}
	
	/**
	 * @param SiEntryInput $siEntryInputs
	 * @throws CorruptedSiInputDataException
	 */
	function handleInput(array $siEntryInputs): array {
		$newEiuEntryGuis = []; 
		$siEmbededEntries = [];
		
		foreach ($siEntryInputs as $siEntryInput) {
			CastUtils::assertTrue($siEntryInput instanceof SiEntryInput);
			
			$eiuEntryGui = null;
			$id = $siEntryInput->getIdentifier()->getId();
			
			if ($id !== null && null !== ($eiuEntryGui = $this->findCurrentEiuEntryGuiById($id))) {
				$eiuEntryGui->handleSiEntryInput($siEntryInput);
				$newEiuEntryGuis[] = $eiuEntryGui;
				$siEmbededEntries[] = $this->createSiEmbeddeEntry($eiuEntryGui);
				continue;
			}
			
			$eiuEntryGuiMulti = $this->targetEiuFrame->newEntryGuiMulti(true, false)
					->handleSiEntryInput($siEntryInput);
			$siEmbededEntries[] = $this->createSiEmbeddeEntry($eiuEntryGuiMulti->selectedEntryGui());
			$newEiuEntryGuis[] = $eiuEntryGuiMulti->selectedEntryGui();
		}
		
		$this->currentEiuEntryGuis = $newEiuEntryGuis;
		return $siEmbededEntries;
	}
	
	function save() {
		$i = 0;
		$targetOrderEiPropPath = $this->relationModel->getTargetOrderEiPropPath();
		
		$values = [];
		foreach ($this->currentEiuEntryGuis as $eiuEntryGui) {
			$eiuEntryGui->save();
			$values[] = $eiuEntry = $eiuEntryGui->entry();
			
			if (null === $targetOrderEiPropPath) {
				continue;
			}
			
			$i += 10;
			$eiuEntry->setScalarValue($targetOrderEiPropPath, $i);
		}
		
		$this->eiu->field()->setValue($values);
	}
	
	function getSiField(): SiField {
		return $this->siField;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
}