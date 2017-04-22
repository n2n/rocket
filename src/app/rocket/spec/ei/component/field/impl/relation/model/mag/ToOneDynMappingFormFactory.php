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

class ToOneDynMappingFormFactory {
	private $eiFrameUtils;
	private $inaccessibleCurrentEiObject;
	private $currentEiMapping;
	private $currentMappingForm;
	private $newMappingFormAvailable;
	private $newMappingForm;
	private $draftMode = false;
	
	public function __construct(EiuFrame $eiFrameUtils) {
		$this->eiFrameUtils = $eiFrameUtils;
	}
	
	public function setEiMapping(EiMapping $eiMapping = null) {
		$this->currentMappingForm = null;
		$this->newMappingForm = null;
		
		if ($eiMapping === null) {
			return;
		}
		
		if (!$eiMapping->isAccessible()) {
			$this->currentMappingForm = new MappingForm(
					$this->eiFrameUtils->createIdentityString($eiMapping->getEiObject()),
					$eiMapping);
			return;
		}
		
		if ($eiMapping->getEiObject()->isNew()) {
			$this->newMappingForm = new MappingForm(
					$this->eiFrameUtils->getGenericLabel(), null,
					$this->eiFrameUtils->createEntryFormFromMapping($eiMapping));
			return;
		}
		
		$this->currentMappingForm = new MappingForm(
				$this->eiFrameUtils->getGenericLabel($eiMapping), null,
				$this->eiFrameUtils->createEntryFormFromMapping($eiMapping));
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
	
	public function getNewMappingForm() {
		return $this->newMappingForm;
	}
	
	public function getOrBuildNewMappingForm() {
		if (!$this->newMappingFormAvailable) return null;
			
		if ($this->newMappingForm === null) {
			$this->newMappingForm = new MappingForm($this->eiFrameUtils->getGenericLabel(), null,
					$this->eiFrameUtils->createNewEntryForm($this->draftMode));
		}
		
		return $this->newMappingForm;
	}
}
