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
namespace rocket\impl\ei\component\prop\relation\model;

use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\relation\model\mag\ToOneMag;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\gui\GuiFieldEditable;
use n2n\web\dispatch\mag\Mag;
use n2n\util\uri\Url;

class ToOneEditable implements GuiFieldEditable {
	private $label;
	private $mandatory;
	private $relationEiField;
	private $targetReadEiFrame;
	private $targetEditEiFrame;
	private $selectOverviewToolsUrl;
	private $newMappingFormUrl;
	private $draftMode = false;
	private $reduced = true;
	
	public function __construct(string $label, bool $mandatory, ToOneEiField $relationEiField,
			EiFrame $targetReadEiFrame, EiFrame $targetEditEiFrame) {
		$this->label = $label;
		$this->mandatory = $mandatory;
		$this->relationEiField = $relationEiField;
		$this->targetReadEiFrame = $targetReadEiFrame;
		$this->targetEditEiFrame = $targetEditEiFrame;
	}
	
	public function isMandatory(): bool {
		return $this->mandatory;
	}
	
	public function setSelectOverviewToolsUrl(Url $selectOverviewToolsUrl = null) {
		$this->selectOverviewToolsUrl = $selectOverviewToolsUrl;
	}
		
	public function setNewMappingFormUrl(Url $newMappingFormUrl = null) {
		$this->newMappingFormUrl = $newMappingFormUrl;
	}
	
	public function setDraftMode(bool $draftMode) {
		$this->draftMode = $draftMode;
	}
	
	public function setReduced(bool $reduced) {
		$this->reduced = $reduced;
	}
	
	public function getMag(): Mag {
		$this->toOneMag = new ToOneMag($this->label, $this->mandatory, $this->targetReadEiFrame,
				$this->targetEditEiFrame);
	
		$this->toOneMag->setValue($this->relationEiField->getValue());
		$this->toOneMag->setSelectOverviewToolsUrl($this->selectOverviewToolsUrl);
		$this->toOneMag->setNewMappingFormUrl($this->newMappingFormUrl);
		$this->toOneMag->setDraftMode($this->draftMode);
		$this->toOneMag->setReduced($this->reduced);
		return $this->toOneMag;
	}
	
	public function save() {
		IllegalStateException::assertTrue($this->toOneMag !== null);
	
		$this->relationEiField->setValue($this->toOneMag->getValue());
	}
}
