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
namespace rocket\impl\ei\component\prop\ci\model;

use rocket\op\ei\manage\gui\field\GuiField;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\util\gui\EiuGuiEntry;
use rocket\si\content\SiField;
use rocket\si\content\impl\relation\SiPanelInput;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\content\impl\relation\SiPanel;
use rocket\si\content\impl\relation\EmbeddedEntryPanelInputHandler;
use rocket\si\content\impl\relation\EmbeddedEntryPanelsInSiField;
use rocket\op\ei\manage\gui\GuiFieldMap;
use rocket\impl\ei\component\prop\relation\model\gui\EmbeddedGuiCollection;
use rocket\op\ei\EiPropPath;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\ci\ContentItemsEiPropNature;
use rocket\si\content\SiFieldModel;

class ContentItemGuiField implements GuiField, EmbeddedEntryPanelInputHandler, SiFieldModel {
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
	 * @var EiuGuiEntryPool
	 */
	private $currentPool;
	/**
	 * @var EmbeddedEntryPanelsInSiField
	 */
	private $siField;
	/**
	 * @var bool
	 */
	private $readOnly;
	
	/**
	 * @param Eiu $eiu
	 * @param EiuFrame $targetEiuFrame
	 * @param RelationModel $relationModel
	 * @param PanelDeclaration[] $panelDeclarations
	 */
	public function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationModel $relationModel,
			array $panelDeclarations, bool $readOnly) {
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
//		$this->relationModel = $relationModel;
		
		$this->currentPool = new EiuGuiEntryPool($panelDeclarations, $readOnly, $relationModel->isReduced(), $targetEiuFrame);
		
		$this->readOnly = $readOnly;
		
		if ($readOnly) {
			$this->siField = SiFields::embeddedEntryPanelsOut($this->targetEiuFrame->createSiFrame(), $this->readValues())
					->setModel($this);
			return;
		}
		
		$this->siField = SiFields::embeddedEntryPanelsIn($this->targetEiuFrame->createSiFrame(),
						$this, $this->readValues())
				->setModel($this);
	}
	
	/**
	 * @return SiPanel[]
	 */
	private function readValues() {
		$this->currentPool->clear();
				
		foreach ($this->eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$this->currentPool->add($eiuEntry);
		}
		
		$this->currentPool->sort();
		
		if (!$this->readOnly) {
			$this->currentPool->fillUp();
		}
		
		return $this->currentPool->createSiPanels();
	}

	/**
	 * @param SiPanelInput[] $siPanelInputs
	 * @return SiPanel[]
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiPanelInputs(array $siPanelInputs): array {
		IllegalStateException::assertTrue(!$this->readOnly);
		
		$this->currentPool->handleInput($siPanelInputs);
		$this->currentPool->fillUp();
		$this->eiu->field()->setValue($this->currentPool->save());
		return $this->currentPool->createSiPanels();
	}
	
	function save(): void {
// 		IllegalStateException::assertTrue(!$this->readOnly);
		
// 		$values = $this->currentPool->save();
		
// 		$this->eiu->field()->setValue($values);
	}
	
	function getSiField(): SiField {
		return $this->siField;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}

	function handleInput(): bool {
		return true;
	}

	function getMessageStrs(): array {
		return $this->eiu->field()->getMessagesAsStrs();
	}
}


class EiuGuiEntryPool {
	/**
	 * @var PanelDeclaration[]
	 */
	private $panelDeclarations;
	private $reduced;
	/**
	 * @var EmbeddedGuiCollection[]
	 */
	private $embeddedGuiCollections = [];
	private $orderEiPropPath;
	private $panelEiPropPath;
	private $panelLayout;
	/**
	 * @param PanelDeclaration[] $panelDeclarations
	 */
	function __construct(array $panelDeclarations, bool $readOnly, bool $reduced, EiuFrame $eiuFrame) {
		$this->orderEiPropPath = new EiPropPath(['orderIndex']);
		$this->panelEiPropPath = new EiPropPath(['panel']);
		
		$this->reduced = $reduced;
		
		$this->panelLayout = new PanelLayout();
		$this->panelLayout->assignConfigs($panelDeclarations);
		
		foreach ($panelDeclarations as $panelDeclaration) {
			$panelName = $panelDeclaration->getName();
			$this->panelDeclarations[$panelName] = $panelDeclaration;
			
			$allowedEiuTypes = null;
			if ($panelDeclaration->isRestricted()) {
				$allowedEiuTypes = $eiuFrame->contextEngine()->mask()->type()
						->possibleTypes($panelDeclaration->getAllowedContentItemIds());
			}
			$this->embeddedGuiCollections[$panelName] = new EmbeddedGuiCollection($readOnly, $reduced, 
					$panelDeclaration->getMin(), $eiuFrame, $allowedEiuTypes);
		}
	}
	
	/**
	 * 
	 */
	function clear() {
		foreach ($this->embeddedGuiCollections as $collection) {
			$collection->clear();
		}
	}
	
	/**
	 * @param string $panelName
	 * @param EiuGuiEntry $eiuGuiEntry
	 */
	function add(EiuEntry $eiuEntry) {
		$panelName = $eiuEntry->getScalarValue('panel');
		
		if (isset($this->embeddedGuiCollections[$panelName])) {
			$this->embeddedGuiCollections[$panelName]->add($eiuEntry);
		}
		
	}
	
	/**
	 * 
	 */
	function sort() {
		foreach ($this->embeddedGuiCollections as $collection) {
			$collection->sort($this->orderEiPropPath);
		}	
	}
	
	/**
	 * 
	 */
	function fillUp() {
		foreach ($this->embeddedGuiCollections as $collection) {
			$collection->fillUp();
		}
	}
	
	/**
	 * @return SiPanel[] 
	 */
	function createSiPanels() {
		$siPanels = [];
		
		foreach ($this->embeddedGuiCollections as $panelName => $collection) {
			$panelDeclaration = $this->panelDeclarations[$panelName];
			$allowedSiTypeIds = $panelDeclaration->isRestricted() ? $panelDeclaration->getAllowedContentItemIds() : null;
			
			$siPanels[] = $siPanel = new SiPanel($panelName, $panelDeclaration->getLabel());
			$siPanel->setEmbeddedEntries($collection->createSiEmbeddedEntries())
					->setAllowedTypeIds($allowedSiTypeIds)
					->setGridPos($this->panelLayout->getSiGridPos($panelName))
					->setMin($panelDeclaration->getMin())
					->setMax($panelDeclaration->getMax())
					->setReduced($this->reduced)
					->setSortable(true);
		}
		
		return $siPanels;
	}

	/**
	 * @param SiPanelInput[] $siPanelInputs
	 * @throws CorruptedSiInputDataException
	 */
	function handleInput(array $siPanelInputs): void {
		foreach ($siPanelInputs as $siPanelInput) {
			ArgUtils::assertTrue($siPanelInput instanceof SiPanelInput);
			
			$panelName = $siPanelInput->getName();
			if (isset($this->embeddedGuiCollections[$panelName])) {
				$this->embeddedGuiCollections[$panelName]->handleSiEntryInputs($siPanelInput->getEntryInputs());
			}
		}
	}
	
	function save() {
		$eiuEntries = [];
		
		foreach ($this->embeddedGuiCollections as $panelName => $collection) {
			$panelEiuEntries = $collection->save($this->orderEiPropPath);
			
			foreach ($panelEiuEntries as $panelEiuEntry) {
				$panelEiuEntry->setValue($this->panelEiPropPath, $panelName);
			}
			
			array_push($eiuEntries, ...$panelEiuEntries);
		}
		
		return $eiuEntries;
	}
}