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

class ToOneDynMappingFormFactory {
	private $eiFrame;
	private $genericLabel;
	private $inaccessibleCurrentEiObject;
	private $currentEiEntry;
	private $currentMappingForm;
	private $newMappingFormAvailable;
	private $newMappingForm;
	private $draftMode = false;
	
	public function __construct(EiuFrame $eiFrameUtils, $genericLabel) {
		$this->eiFrame = $eiFrameUtils;
		$this->genericLabel = $genericLabel;
	}
	
	public function setEiEntry(EiEntry $eiEntry = null) {
		$this->currentMappingForm = null;
		$this->newMappingForm = null;
		
		if ($eiEntry === null) {
			return;
		}
		
// 		if (!$eiEntry->isAccessible()) {
// 			$this->currentMappingForm = new MappingForm(
// 					$this->eiFrame->createIdentityString($eiEntry->getEiObject()),
// 					$eiEntry);
// 			return;
// 		}
		
		if ($eiEntry->getEiObject()->isNew()) {
			$this->newMappingForm = new MappingForm(
					$this->eiFrame->getGenericLabel(), $this->eiFrame->getGenericIconType(), null,
					$this->eiFrame->newEntryForm($eiEntry->getEiObject()->isDraft(), null, null, null, [$eiEntry]));
			return;
		}
		
		$this->currentMappingForm = new MappingForm(
				$this->eiFrame->getGenericLabel($eiEntry), 
				$this->eiFrame->getGenericIconType($eiEntry), null,
				$this->eiFrame->entryForm($eiEntry));
	}

	public function getCurrentMappingForm() {
		return $this->currentMappingForm;
	}
	
	public function setNewMappingFormAvailable(bool $newMappingFormAvailable) {
		$this->newMappingFormAvailable = $newMappingFormAvailable;
	}
	
	public function isNewMappingFormAvailable(): bool {
		return $this->newMappingFormAvailable;
	}
	
	public function setDraftMode(bool $draftMode) {
		$this->draftMode = $draftMode;
	}
	
	public function isDraftMode() {
		return $this->draftMode;
	}
	
	public function getNewMappingForm() {
		return $this->newMappingForm;
	}
	
	public function getOrBuildNewMappingForm() {
		if (!$this->newMappingFormAvailable) return null;
			
		if ($this->newMappingForm === null) {
			$this->newMappingForm = new MappingForm($this->eiFrame->getGenericLabel(), 
					$this->eiFrame->getGenericIconType(), null,
					$this->eiFrame->newEntryForm($this->draftMode));
		}
		
		return $this->newMappingForm;
	}
}
