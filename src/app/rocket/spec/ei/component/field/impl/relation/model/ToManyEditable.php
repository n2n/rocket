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

use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\gui\Editable;
use rocket\spec\ei\component\field\impl\relation\model\mag\ToManyMag;
use n2n\web\dispatch\mag\Mag;
use n2n\util\uri\Url;
use rocket\spec\ei\EiFieldPath;

class ToManyEditable implements Editable {
	private $label;
	private $min;
	private $max;
	private $toManyMappable;
	private $targetReadEiState;
	private $targetEditEiState;
	private $selectOverviewToolsUrl;
	private $newMappingFormUrl;
	private $draftMode = false;
	private $targetOrderEiFieldPath;
	
	public function __construct(string $label, ToManyMappable $toManyMappable,
			EiState $targetReadEiState, EiState $targetEditEiState, int $min, int $max = null) {
		$this->label = $label;
		$this->min = $min;
		$this->max = $max;
		$this->toManyMappable = $toManyMappable;
		$this->targetReadEiState = $targetReadEiState;
		$this->targetEditEiState = $targetEditEiState;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Editable::isMandatory()
	 */
	public function isMandatory(): bool {
		return $this->min > 0;
	}
	
	public function getMin(): int {
		return $this->min;
	}
	
	public function getMax() {
		return null;
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

	public function setTargetOrderEiFieldPath(EiFieldPath $targetOrderEiFieldPath = null) {
		$this->targetOrderEiFieldPath = $targetOrderEiFieldPath;
	}
	
	private $toManyMag;
	
	public function createMag(string $propertyName): Mag {
		$this->toManyMag = new ToManyMag($propertyName, $this->label, $this->targetReadEiState, $this->targetEditEiState, 
				$this->min, $this->max);
		$this->toManyMag->setValue($this->toManyMappable->getValue());
		$this->toManyMag->setSelectOverviewToolsUrl($this->selectOverviewToolsUrl);
		$this->toManyMag->setNewMappingFormUrl($this->newMappingFormUrl);
		$this->toManyMag->setDraftMode($this->draftMode);
		$this->toManyMag->setTargetOrderEiFieldPath($this->targetOrderEiFieldPath);
		return $this->toManyMag;
	}
	
	public function save() {
		IllegalStateException::assertTrue($this->toManyMag !== null);

		$this->toManyMappable->setValue($this->toManyMag->getValue());
	}
}
