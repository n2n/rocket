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

use rocket\ei\util\frame\EiuFrame;
use rocket\ei\manage\entry\EiEntry;

class ToManyDynMappingFormFactory {
	private $eiuFrame;
	private $inaccessibleCurrentEiObject;
	private $currentEiEntry;
	private $currentMappingForms = array();
	private $newMappingFormAvailable;
	private $newMappingForms = array();
	private $allowedNewEiTypeIds = null;
	private $draftMode = false;
	
	private $nextOrderIndex = 0;
	
	public function __construct(EiuFrame $utils) {
		$this->eiuFrame = $utils;
	}
	
	private function getKey(EiEntry $eiEntry) {
		$eiuEntry = $this->eiuFrame->entry($eiEntry);
		if ($eiuEntry->isDraft()) {
			return 'd' . $eiuEntry->getDraft()->getId();
		}
		
		return 'c' . $eiuEntry->getEiEntityObj()->getId();
	}
	
	public function addEiEntry(EiEntry $currentEiEntry) {
// 		if (!$currentEiEntry->isAccessible()) {
// 			$this->currentMappingForms[$this->getKey($currentEiEntry)] = new MappingForm(
// 					$this->eiuFrame->createIdentityString($currentEiEntry->getEiObject()),
// 					$this->eiuFrame->getGenericIconType($currentEiEntry),
// 					null, $this->nextOrderIndex++);
// 			return;
// 		}
		
		if ($currentEiEntry->getEiObject()->isNew()) {
			$this->newMappingForms[] = new MappingForm(
					$this->eiuFrame->getGenericLabel(), $this->eiuFrame->getGenericIconType(), null,
					$this->eiuFrame->newEntryForm($currentEiEntry->getEiObject()->isDraft(), null, null, null, [$currentEiEntry]), 
					$this->nextOrderIndex++);
			return;
		}
		
		$this->currentMappingForms[$this->getKey($currentEiEntry)] = new MappingForm(
				$this->eiuFrame->getGenericLabel($currentEiEntry), 
				$this->eiuFrame->getGenericIconType($currentEiEntry), null, 
				$this->eiuFrame->entryForm($currentEiEntry), $this->nextOrderIndex++);
	}

	public function getCurrentMappingForm(string $pid) {
		if (isset($this->currentMappingForms[$pid])) {
			return $this->currentMappingForms[$pid];
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
	
	/**
	 * @param array|null $allowedEiTypeIds
	 */
	public function setAllowedNewEiTypeIds(array $allowedEiTypeIds = null) {
		$this->allowedNewEiTypeIds = $allowedEiTypeIds;
	}
	
	/**
	 * @return array|null
	 */
	public function getAllowedNewEiTypeIds() {
		return  $this->allowedNewEiTypeIds;
	}
	
	public function setDraftMode(bool $draftMode) {
		$this->draftMode = $draftMode;
	}
	
	public function isDraftMode() {
		return $this->draftMode;
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
				$this->eiuFrame->getGenericLabel(), $this->eiuFrame->getGenericIconType(), null,
				$this->eiuFrame->newEntryForm($this->draftMode, null, null, $this->allowedNewEiTypeIds));
	}
}
