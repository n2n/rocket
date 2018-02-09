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
namespace rocket\impl\ei\component\prop\relation\model\mag;

use rocket\spec\ei\manage\util\model\EiUtils;

class EntryLabeler {
	private $eiUtils;
	private $genericLabel;
	private $selectedIdentityStrings = array();
	
	public function __construct(EiUtils $eiUtils) {
		$this->eiUtils = $eiUtils;
		$this->genericLabel = $eiUtils->getGenericLabel();
	}
	
	public function getGenericLabel() {
		return $this->genericLabel;
	}
	
	public function getIdentityStringByEiId(string $eiId): string {
		if (isset($this->selectedIdentityStrings[$eiId])) {
			return $this->selectedIdentityStrings[$eiId];
		}
		
		return $this->eiUtils->createIdentityString(
				$this->eiUtils->lookupEiObjectById($this->eiUtils->eiIdToId($eiId)));
	}
	
	public function setSelectedIdentityString(string $eiId, string $identityString) {
		$this->selectedIdentityStrings[$eiId] = $identityString;
	}
	
	public function getSelectedIdentityStrings(): array {
		return $this->selectedIdentityStrings;
	}
	
	public function getEiTypeLabels() {
		$eiFrame = $this->eiUtils->getEiFrame();
		$contextEiMask = $eiFrame->getContextEiMask();
		$contextEiType = $eiFrame->getContextEiMask()->getEiEngine()->getEiType();
		
		$eiTypeLabels = array();
		
		if (!$contextEiType->isAbstract()) {
			$eiTypeLabels[$contextEiType->getId()] = $contextEiMask->getLabelLstr()->t($eiFrame->getN2nLocale());
		}
		
		foreach ($contextEiType->getAllSubEiTypes() as $subEiType) {
			if ($subEiType->isAbstract()) continue;
		
			$eiTypeLabels[$subEiType->getId()] = $contextEiMask->determineEiMask($subEiType)->getLabelLstr()
					->t($eiFrame->getN2nLocale());
		}
		
		return $eiTypeLabels;
	}
}
