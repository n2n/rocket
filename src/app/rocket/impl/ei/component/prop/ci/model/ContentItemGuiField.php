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
use rocket\ei\util\spec\EiuMask;
use rocket\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;
use rocket\si\content\SiEmbeddedEntry;
use rocket\ei\util\gui\EiuEntryGui;
use rocket\si\content\SiField;

class ContentItemGuiField implements GuiField, EmbeddedEntryPanelInputHandle {
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
	 * @var PanelConfig[]
	 */
	private $panelConfigs;
	
	/**
	 * @param Eiu $eiu
	 * @param EiuFrame $targetEiuFrame
	 * @param RelationModel $relationModel
	 * @param PanelConfig[] $panelConfigs
	 */
	public function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationModel $relationModel,
			array $panelConfigs) {
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
		$this->relationModel = $relationModel;
		$this->panelConfigs = $panelConfigs;
		
		$this->siField = SiFields::embeddedEntryPanelsIn(
						$this->targetEiuFrame->getApiUrl($relationModel->getTargetEditEiCommandPath()),
						$this, $this->readValues())
				->setSortable(true)
				->setReduced($relationModel->isReduced())
				->setPasteCategory($targetEiuFrame->engine()->type()->supremeType()->getId())
				->setAllowedTypes(array_map(
						function (EiuMask $eiuMask) { return $eiuMask->createSiType(); },
						$targetEiuFrame->engine()->mask()->possibleMasks()));
	}
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\si\content\SiEmbeddedEntry[]
	 */
	private function readValues() {
		$panelLayout = new PanelLayout($this->panelConfigs);
		$this->currentPool = new EiuEntryGuiPool();
				
		$currentEiuEntryGuis = [];
		foreach ($this->eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$currentEiuEntryGuis[] = $eiuEntry->newEntryGui(true, true);
		}
		
		uasort($currentEiuEntryGuis, function($a, $b) {
			$aValue = $a->entry()->getValue('orderIndex');
			$bValue = $b->entry()->getValue('orderIndex');
			
			if ($aValue == $bValue) {
				return 0;
			}
			
			return ($aValue < $bValue) ? -1 : 1;
		});
		
		foreach ($currentEiuEntryGuis as $eiuEntryGui) {
			$panelName = $eiuEntryGui->entry()->getValue('panel');
			if ($panelLayout->addSiEmbeddedEntry($panelName, $this->createSiEmbeddeEntry($eiuEntryGui))) {
				$this->addCurrentEiuEntryGui($panelName, $eiuEntryGui);
			}
		}
		
		return $panelLayout->toSiPanels();
	}

	/**
	 * @param EiuEntryGui $eiuEntryGui
	 * @return \rocket\si\content\SiEmbeddedEntry
	 */
	private function createSiEmbeddeEntry($eiuEntryGui) {
		return new SiEmbeddedEntry(
				$eiuEntryGui->createBulkyEntrySiContent(false, false),
				($this->relationModel->isReduced() ?
						$eiuEntryGui->entry()->newEntryGui(false, false)->createCompactEntrySiContent(false, false):
						null));
	}
	
	
	
	/**
	 * @param SiPanelInput[] $siPanelInputs
	 * @return SiPanel[]
	 * @throws CorruptedSiInputDataException
	 */
	function handleInput(array $siPanelInputs): array {
		$panelLayout = new PanelLayout($this->panelConfigs);
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
		
		return $this->currentPool->toSiPanels();
	}
	
	function save() {
		$i = 0;
		$targetOrderEiPropPath = $this->relationModel->getTargetOrderEiPropPath();
		
		$values = [];
		foreach ($this->panelLayout->getPanelNames() as $panelName) {
			foreach ($this->panelPool->getByPanelName($panelName) as $eiuEntryGui) {
				$eiuEntryGui->save();
				$values[] = $eiuEntry = $eiuEntryGui->entry();
				
				$eiuEntry->setValue('orderIndex', $i);
				$eiuEntry->setValue('panel', $panelName);
				
				$i += 10;
				$eiuEntry->setScalarValue($targetOrderEiPropPath, $i);
			}
		}
		
		$this->eiu->field()->setValue($values);
	}
	
	function getSiField(): SiField {
		return $this->siField;
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
}