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
namespace rocket\spec\ei\component\field\impl\relation\model\mag;

use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\util\model\EiuEntry;

class ToManyDynMappingFormFactory {
	private $utils;
	private $inaccessibleCurrentEiSelection;
	private $currentEiMapping;
	private $currentMappingForms;
	private $newMappingFormAvailable;
	private $newMappingForms;
	private $draftMode = false;
	
	private $nextOrderIndex = 0;
	
	public function __construct(EiuFrame $utils) {
		$this->utils = $utils;
	}
	
	private function getKey(EiMapping $eiMapping) {
		$ei = new EiuEntry($eiMapping);
		if ($ei->isDraft()) {
			return 'd' . $ei->getDraft()->getId();
		}
		
		return 'c' . $ei->getLiveEntry()->getId();
	}
	
	public function addEiMapping(EiMapping $currentEiMapping) {
		if (!$currentEiMapping->isAccessible()) {
			$this->currentMappingForms[$this->getKey($currentEiMapping)] = new MappingForm(
					$this->utils->createIdentityString($currentEiMapping->getEiSelection()),
					null, $this->nextOrderIndex++);
			return;
		}
		
		if ($currentEiMapping->getEiSelection()->isNew()) {
			$this->newMappingForms[] = new MappingForm(
					$this->utils->getGenericLabel(), null,
					$this->utils->createEntryFormFromMapping($currentEiMapping), $this->nextOrderIndex++);
			return;
		}
		
		$this->currentMappingForms[$this->getKey($currentEiMapping)] = new MappingForm(
				$this->utils->getGenericLabel($currentEiMapping), null, 
				$this->utils->createEntryFormFromMapping($currentEiMapping), $this->nextOrderIndex++);
	}

	public function getCurrentMappingForm(string $idRep) {
		if (isset($this->currentMappingForms[$idRep])) {
			return $this->currentMappingForms[$idRep];
		}
		
		return null;
	}
	
	public function getCurrentMappingForms(): array {
		return $this->currentMappingForms;
	}
	
	public function setNewMappingFormAvailable(bool $newMappingFormAvailable) {
		$this->newMappingFormAvailable = $newMappingFormAvailable;
	}
	
	public function isNewMappingFormAvailable() {
		return $this->newMappingFormAvailable;
	}
	
	public function setDraftMode(bool $draftMode) {
		$this->draftMode = $draftMode;
	}
	
	public function getNewMappingForms() {
		return $this->newMappingForms;
	}
	
	public function getOrBuildNewMappingForm(string $key) {
		if (!$this->newMappingFormAvailable) return null;
			
		if (isset($this->newMappingForms[$key])) {
			return $this->newMappingForms[$key];
		}
		
		return $this->newMappingForms[$key] = new MappingForm(
				$this->utils->getGenericLabel(), null,
				$this->utils->createNewEntryForm($this->draftMode));
	}
}
