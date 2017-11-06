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
namespace rocket\spec\ei\component\field\impl\ci\model;

use rocket\spec\ei\manage\gui\Editable;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\component\field\impl\relation\model\ToManyEiField;
use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\mag\Mag;
use n2n\util\uri\Url;

class ContentItemEditable implements Editable {
	private $label;
	private $toManyEiField;
	private $targetReadEiFrame;
	private $targetEditEiFrame;
	private $panelConfigs;
	private $newMappingFormUrl;
	private $draftMode = false;

	public function __construct(string $label, ToManyEiField $toManyEiField,
			EiFrame $targetReadEiFrame, EiFrame $targetEditEiFrame, array $panelConfigs) {
		$this->label = $label;
		$this->toManyEiField = $toManyEiField;
		$this->targetReadEiFrame = $targetReadEiFrame;
		$this->targetEditEiFrame = $targetEditEiFrame;
		$this->panelConfigs = $panelConfigs;
	}

	public function setNewMappingFormUrl(Url $newMappingFormUrl = null) {
		$this->newMappingFormUrl = $newMappingFormUrl;
	}
	
	public function setDraftMode(bool $draftMode) {
		$this->draftMode = $draftMode;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Editable::isMandatory()
	 */
	public function isMandatory(): bool {
		foreach ($this->panelConfigs as $panelConfig) {
			if ($panelConfig->getMin() > 0) return true;
		}

		return false;
	}

	private $contentItemMag;
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Editable::createMag($propertyName)
	 */
	public function createMag(string $propertyName): Mag {
		$this->contentItemMag = new ContentItemMag($propertyName, $this->label, $this->panelConfigs,
				$this->targetReadEiFrame, $this->targetEditEiFrame);
		$this->contentItemMag->setNewMappingFormUrl($this->newMappingFormUrl);
		$this->contentItemMag->setValue($this->toManyEiField->getValue());
		return $this->contentItemMag;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Savable::save()
	 */
	public function save() {
		IllegalStateException::assertTrue($this->contentItemMag !== null);

		$this->toManyEiField->setValue($this->contentItemMag->getValue());
	}
}
