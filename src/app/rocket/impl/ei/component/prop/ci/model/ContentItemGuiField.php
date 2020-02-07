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

use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\util\Eiu;
use rocket\ei\util\frame\EiuFrame;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;
use rocket\si\content\impl\relation\SiEmbeddedEntry;
use rocket\ei\util\gui\EiuEntryGui;
use rocket\si\content\SiField;
use rocket\si\content\impl\relation\SiPanelInput;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\content\impl\relation\SiPanel;
use rocket\impl\ei\component\prop\ci\ContentItemsEiProp;
use rocket\si\content\impl\relation\EmbeddedEntryPanelInputHandler;
use rocket\si\content\impl\relation\EmbeddedEntryPanelsInSiField;
use rocket\ei\manage\gui\GuiFieldMap;

class ContentItemGuiField implements GuiField, EmbeddedEntryPanelInputHandler {
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
	 * @var EiuEntryGuiPool
	 */
	private $currentPool;
	/**
	 * @var PanelLayout
	 */
	private $panelLayout;
	/**
	 * @var EmbeddedEntryPanelsInSiField
	 */
	private $siField;
	
	/**
	 * @param Eiu $eiu
	 * @param EiuFrame $targetEiuFrame
	 * @param RelationModel $relationModel
	 * @param PanelDeclaration[] $panelDeclarations
	 */
	public function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationModel $relationModel,
			array $panelDeclarations) {
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
		$this->relationModel = $relationModel;
		$this->panelLayout = new PanelLayout($panelDeclarations);
		$this->panelLayout->assignConfigs($panelDeclarations, $targetEiuFrame, $relationModel);
		
		$this->siField = SiFields::embeddedEntryPanelsIn(
				$this->targetEiuFrame->getApiUrl($relationModel->getTargetEditEiCommandPath()),
				$this, $this->readValues());
	}
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry[]
	 */
	private function readValues() {
		$this->panelLayout->clearSiEmbeddedEntries();
		$this->currentPool = new EiuEntryGuiPool();
				
		$currentEiuEntryGuis = [];
		foreach ($this->eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$currentEiuEntryGuis[] = $eiuEntry->newEntryGui(true, true);
		}
		
		$orderPropPath = ContentItemsEiProp::getOrderIndexEiPropPath();
		uasort($currentEiuEntryGuis, function($a, $b) use ($orderPropPath) {
			$aValue = $a->entry()->getValue($orderPropPath);
			$bValue = $b->entry()->getValue($orderPropPath);
			
			if ($aValue == $bValue) {
				return 0;
			}
			
			return ($aValue < $bValue) ? -1 : 1;
		});
		
		foreach ($currentEiuEntryGuis as $eiuEntryGui) {
			$panelName = $eiuEntryGui->entry()->getValue('panel');
			if ($this->panelLayout->addSiEmbeddedEntry($panelName, $this->createSiEmbeddeEntry($eiuEntryGui))) {
				$this->currentPool->add($panelName, $eiuEntryGui);
			}
		}
		
		return $this->panelLayout->toSiPanels();
	}

	/**
	 * @param EiuEntryGui $eiuEntryGui
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry
	 */
	private function createSiEmbeddeEntry($eiuEntryGui) {
		return new SiEmbeddedEntry(
				$eiuEntryGui->createBulkyEntrySiComp(false, false),
				($this->relationModel->isReduced() ?
						$eiuEntryGui->entry()->newEntryGui(false, false)->createCompactEntrySiComp(false, false):
						null));
	}
	
	
	
	/**
	 * @param SiPanelInput[] $siPanelInputs
	 * @return SiPanel[]
	 * @throws CorruptedSiInputDataException
	 */
	function handleInput(array $siPanelInputs): array {
		$this->panelLayout->clearSiEmbeddedEntries();
		$this->currentPool = new EiuEntryGuiPool();
		
		foreach ($siPanelInputs as $siPanelInput) {
			CastUtils::assertTrue($siPanelInput instanceof SiPanelInput);
			
			foreach ($siPanelInput->getEntryInputs() as $siEntryInput) {
				$eiuEntryGui = null;
				$id = $siEntryInput->getIdentifier()->getId();
				
				if ($id !== null && null !== ($eiuEntryGui = $this->currentPool->findById($id))) {
					$eiuEntryGui->handleSiEntryInput($siEntryInput);
				} else {
					$eiuEntryGui = $this->targetEiuFrame->newEntryGuiMulti(true, false)
							->handleSiEntryInput($siEntryInput)->selectedEntryGui();;
				}
				
				$panelName = $siPanelInput->getName();
				if ($panelLayout->addSiEmbeddedEntry($panelName, $this->createSiEmbeddeEntry($eiuEntryGui))) {
					$this->currentPool->add($panelName, $eiuEntryGui);
				}
			}
		}
		
		return $panelLayout->toSiPanels();
	}
	
	function save() {
		$i = 0;
		$targetOrderEiPropPath = $this->relationModel->getTargetOrderEiPropPath();
		
		$values = [];
		foreach ($this->panelLayout->getPanelNames() as $panelName) {
			foreach ($this->panelPool->getByPanelName($panelName) as $eiuEntryGui) {
				$eiuEntryGui->save();
				$values[] = $eiuEntry = $eiuEntryGui->entry();
				
				$eiuEntry->setValue(ContentItemsEiProp::getOrderIndexEiPropPath(), $i);
				$eiuEntry->setValue(ContentItemsEiProp::getPanelEiPropPath(), $panelName);
				
				$i += 10;
				$eiuEntry->setScalarValue($targetOrderEiPropPath, $i);
			}
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


class EiuEntryGuiPool {
	
	private $eiuEntryGuis = [];
	
	private function clearCurrentEiuEntryGuis() {
		$this->currentEiuEntryGuis = [];
	}
	
	/**
	 * @param string $panelName
	 * @param EiuEntryGui $eiuEntryGui
	 */
	function add(string $panelName, EiuEntryGui $eiuEntryGui) {
		if (!isset($this->eiuEntryGuis[$panelName])) {
			$this->eiuEntryGuis[$panelName] = [];
		}
		
		$this->eiuEntryGuis[$panelName][] = $eiuEntryGui;
	}
	
	function sort() {
		foreach (array_keys($this->eiuEntryGuis) as $panelName) {
			uasort($this->currentEiuEntryGuis[$panelName], function($a, $b) {
				$aValue = $a->entry()->getValue('orderIndex');
				$bValue = $b->entry()->getValue('orderIndex');
				
				if ($aValue == $bValue) {
					return 0;
				}
				
				return ($aValue < $bValue) ? -1 : 1;
			});
		}
		
	}
	
	/**
	 * @param string $id
	 * @return \rocket\ei\util\gui\EiuEntryGui|NULL
	 */
	private function findById(string $panelName, string $id) {
		foreach ($this->currentEiuEntryGuis as $eiuEntryGui) {
			if ($eiuEntryGui->entry()->hasId() && $id == $eiuEntryGui->entry()->getPid()) {
				return $eiuEntryGui;
			}
		}
		
		return null;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
}