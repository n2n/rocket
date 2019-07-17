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
use rocket\si\content\impl\EmbeddedEntryInSiField;
use rocket\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;
use rocket\si\input\SiEntryInput;
use rocket\si\input\CorruptedSiInputDataException;

class EmbeddedToManyGuiField implements GuiField {
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
	
	private $currentEiuEntryGuis = [];
	
	function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationModel $relationModel) {
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
		
		$values = [];
		$summarySiEntries =  [];
		foreach ($eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$this->currentEiuEntryGuis[] = $eiuEntryGui = $eiuEntry->newEntryGui(true, true);
			$values[] = $eiuEntryGui->createSiEntry();
			$summarySiEntries[] = $eiuEntry->newEntryGui(false, false)->createSiEntry();
		}
		
		$this->siField = SiFields::embeddedEntryIn(
				$this->targetEiuFrame->getApiUrl($relationModel->getTargetEditEiCommandPath()),
				$this, $values, $summarySiEntries, (int) $relationModel->getMin(), $relationModel->getMax());
	}
	
	/**
	 * @param array $siEntryInputs
	 * @throws CorruptedSiInputDataException
	 */
	function handleInput(array $siEntryInputs) {
		$newEiuEntryGuis = []; 
		$siEntries = [];
		
		foreach ($siEntryInputs as $siEntryInput) {
			CastUtils::assertTrue($siEntryInput instanceof SiEntryInput);
			
			$eiuEntryGui = null;
			$id = $siEntryInput->getSiIdentifier()->getId();
			if ($id !== null && isset($this->eiuEntryGuis[$id])) {
				$newEiuEntryGuis[] = $eiuEntryGui = $this->eiuEntryGuis[$id]->handleSiEntryInput($siEntryInput);
				$siEntries[] = $eiuEntryGui->createSiEntry();
				continue;
			}
			
			$eiuEntryGuiMulti = $this->targetEiuFrame->newEntryGuiMulti(true, false)
					->handleSiEntryInput($siEntryInput);
			$siEntries[] = $eiuEntryGuiMulti->createSiEntry();
			$newEiuEntryGuis[] = $eiuEntryGuiMulti->selectedEntryGui();
		}
		
		$this->eiuEntryGuis = $newEiuEntryGuis;
		$this->siField->setValues($siEntries);
	}
	
	function save() {
		$values = [];
		foreach ($this->siField->getValues() as $siQualifier) {
			$id = $this->targetEiuFrame->siQualifierToId($siQualifier);
			$values[] = $this->targetEiuFrame->lookupEntry($id);
		}
		
		$this->eiu->field()->setValue($values);
	}
	
	function getSiField(): SiField {
		return $this->siField;
	}	
}