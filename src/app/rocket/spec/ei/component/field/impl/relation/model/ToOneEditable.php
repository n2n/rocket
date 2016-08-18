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
namespace rocket\spec\ei\component\field\impl\relation\model;

use rocket\spec\ei\component\field\impl\relation\model\RelationMappable;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\field\impl\relation\model\mag\ToOneMag;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\gui\Editable;
use n2n\web\dispatch\mag\Mag;
use n2n\util\uri\Url;

class ToOneEditable implements Editable {
	private $label;
	private $mandatory;
	private $relationMappable;
	private $targetReadEiState;
	private $targetEditEiState;
	private $selectOverviewToolsUrl;
	private $newMappingFormUrl;
	private $draftMode = false;
	
	public function __construct(string $label, bool $mandatory, ToOneMappable $relationMappable,
			EiState $targetReadEiState, EiState $targetEditEiState) {
		$this->label = $label;
		$this->mandatory = $mandatory;
		$this->relationMappable = $relationMappable;
		$this->targetReadEiState = $targetReadEiState;
		$this->targetEditEiState = $targetEditEiState;
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
	
	public function createMag(string $propertyName): Mag {
		$this->toOneMag = new ToOneMag($propertyName, $this->label, $this->mandatory, $this->targetReadEiState,
				$this->targetEditEiState);
	
		$this->toOneMag->setValue($this->relationMappable->getValue());
		$this->toOneMag->setSelectOverviewToolsUrl($this->selectOverviewToolsUrl);
		$this->toOneMag->setNewMappingFormUrl($this->newMappingFormUrl);
		$this->toOneMag->setDraftMode($this->draftMode);
		return $this->toOneMag;
	}
	
	public function save() {
		IllegalStateException::assertTrue($this->toOneMag !== null);
	
		$this->relationMappable->setValue($this->toOneMag->getValue());
	}
}
