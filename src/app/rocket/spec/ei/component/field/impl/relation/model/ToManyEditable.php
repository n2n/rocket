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
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\gui\Editable;
use rocket\spec\ei\component\field\impl\relation\model\mag\ToManyMag;
use n2n\web\dispatch\mag\Mag;
use n2n\util\uri\Url;
use rocket\spec\ei\EiPropPath;

class ToManyEditable implements Editable {
	private $label;
	private $min;
	private $max;
	private $compact = true;
	private $sortable = true;
	private $toManyEiField;
	private $targetReadEiFrame;
	private $targetEditEiFrame;
	private $selectOverviewToolsUrl;
	private $newMappingFormUrl;
	private $draftMode = false;
	private $targetOrderEiPropPath;
	
	public function __construct(string $label, ToManyEiField $toManyEiField,
			EiFrame $targetReadEiFrame, EiFrame $targetEditEiFrame, int $min, int $max = null) {
		$this->label = $label;
		$this->min = $min;
		$this->max = $max;
		$this->compact = $compact;
		$this->toManyEiField = $toManyEiField;
		$this->targetReadEiFrame = $targetReadEiFrame;
		$this->targetEditEiFrame = $targetEditEiFrame;
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
	
	public function setCompact(bool $compact) {
		$this->compact = $compact;
	}
	
	public function setSortable(bool $sortable) {
		$this->sortable = $sortable;
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

	public function setTargetOrderEiPropPath(EiPropPath $targetOrderEiPropPath = null) {
		$this->targetOrderEiPropPath = $targetOrderEiPropPath;
	}
	
	private $toManyMag;
	
	public function createMag(): Mag {
		$this->toManyMag = new ToManyMag($this->label, $this->targetReadEiFrame, $this->targetEditEiFrame, 
				$this->min, $this->max);
		$this->toManyMag->setCompact($this->compact);
		$this->toManyMag->setSortable($this->sortable);
		$this->toManyMag->setValue($this->toManyEiField->getValue());
		$this->toManyMag->setSelectOverviewToolsUrl($this->selectOverviewToolsUrl);
		$this->toManyMag->setNewMappingFormUrl($this->newMappingFormUrl);
		$this->toManyMag->setDraftMode($this->draftMode);
		$this->toManyMag->setTargetOrderEiPropPath($this->targetOrderEiPropPath);
		return $this->toManyMag;
	}
	
	public function save() {
		IllegalStateException::assertTrue($this->toManyMag !== null);

		$this->toManyEiField->setValue($this->toManyMag->getValue());
	}
}
