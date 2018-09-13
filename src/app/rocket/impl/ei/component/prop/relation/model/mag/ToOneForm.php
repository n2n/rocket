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

use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObject;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use rocket\ei\util\frame\EiuFrame;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\map\bind\BindingErrors;
use rocket\core\model\Rocket;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\frame\Boundry;

class ToOneForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('selectedEntryPid'));
		$ai->p('currentMappingForm', new AnnoDispObject(function (ToOneForm $toOneForm) {
			return $toOneForm->eiuEntryFormFactory->getCurrentMappingForm();
		}));
		$ai->p('newMappingForm', new AnnoDispObject(function (ToOneForm $toOneForm) {
			return $toOneForm->eiuEntryFormFactory->getOrBuildNewMappingForm();
		}));
	}

	private $label;
	private $mandatory;
	private $utils;
	private $eiuEntryFormFactory;
	private $entryLabeler;
	
	private $selectionModeEnabled = false;
	private $originalEntryPid; 
	
	private $selectedEntryPid;
	private $currentMappingForm;
	private $newMappingForm;

	public function __construct(string $label, bool $mandatory, EiuFrame $utils) {
		$this->label = $label;
		$this->mandatory = $mandatory;
		$this->utils = $utils;
		$this->eiuEntryFormFactory = new ToOneDynMappingFormFactory($utils, $label);
		$this->entryLabeler = new EntryLabeler($utils);
	}
	
	public function isMandatory(): bool {
		return $this->mandatory;
	}
	
	public function getEntryLabeler(): EntryLabeler {
		return $this->entryLabeler;
	}
	
	public function setSelectionModeEnabled(bool $selectionModeEnabled) {
		$this->selectionModeEnabled = $selectionModeEnabled;
	}
	
	public function isSelectionModeEnabled(): bool {
		return $this->selectionModeEnabled;
	}
	
	public function setOriginalEntryPid(string $originalEntryPid = null) {
		$this->originalEntryPid = $originalEntryPid;
	}
	
	public function getOriginalEntryPid() {
		return $this->originalEntryPid;
	}

	public function setSelectedEntryPid(string $selectedEntryPid = null) {
		$this->selectedEntryPid = $selectedEntryPid;
	}

	public function getSelectedEntryPid() {
		return $this->selectedEntryPid;
	}
	
	public function isMappingFormAvailable(): bool {
		return null !== $this->eiuEntryFormFactory->getCurrentMappingForm();
	}
	
	public function setDraftMode(bool $draftMode) {
		$this->eiuEntryFormFactory->setDraftMode($draftMode);
	}

	public function setEiEntry(EiEntry $eiEntry = null) {
		$this->eiuEntryFormFactory->setEiEntry($eiEntry);
		$this->currentMappingForm = $this->eiuEntryFormFactory->getCurrentMappingForm();
		$this->newMappingForm = $this->eiuEntryFormFactory->getNewMappingForm();
	}
	
	public function getCurrentMappingForm() {
		return $this->currentMappingForm;
	}

	public function setCurrentMappingForm(MappingForm $currentMappingForm = null) {
		$this->currentMappingForm = $currentMappingForm;
	}

	public function isNewMappingFormAvailable(): bool {
		return $this->eiuEntryFormFactory->isNewMappingFormAvailable();
	}

	public function setNewMappingFormAvailable($newMappingFormAvailable) {
		$this->eiuEntryFormFactory->setNewMappingFormAvailable($newMappingFormAvailable);
	}

	public function getNewMappingForm() {
		return $this->newMappingForm;
	}

	public function setNewMappingForm(MappingForm $newMappingForm = null) {
		$this->newMappingForm = $newMappingForm;
	}
	
	private function _validation(BindingDefinition $bd) {
		if ($this->selectionModeEnabled) {
			$that = $this;
			$bd->closure(function ($selectedEntryPid, BindingErrors $be) use ($that) {
				if ($that->originalEntryPid === $selectedEntryPid || $selectedEntryPid === null) {
					return;
				}
						
				if (null !== ($eiObject = $that->utils->lookupEiObjectById(
						$that->utils->pidToId($selectedEntryPid), Boundry::NON_SECURITY_TYPES))) {
					$that->entryLabeler->setSelectedIdentityString($selectedEntryPid, 
							$that->utils->createIdentityString($eiObject));				
					return;
				}
					
				$be->addErrorCode('entryPid', 'ei_impl_relation_unkown_entry_err', array('id_rep' => $selectedEntryPid),
						Rocket::NS);
			});
		}
		
		if ($this->mandatory) {
			$that = $this;
			$bd->closure(function ($selectedEntryPid, $currentMappingForm, $newMappingForm,
					BindingErrors $be) use ($that) {
				if ($selectedEntryPid !== null || $currentMappingForm !== null || $newMappingForm !== null) return;

				// @todo find out how to register error on parent property / dispatchable
				$be->addErrorCode('entryPid', 'common_field_required_err', array('field' => $that->label), 
							Rocket::NS);
			});
		}
	}
	
	public function buildEiEntry() {
		if ($this->newMappingForm !== null) {
			return $this->newMappingForm->buildEiEntry();
		}
		
		if ($this->currentMappingForm !== null) {
			return $this->currentMappingForm->buildEiEntry();
		}
		
		return null;
	}
	
	public function isReduced() {
		return $this->reduced;
	}
	
	public function setReduced(bool $reduced) {
		$this->reduced = $reduced;
	}
	
	/**
	 * @return string[]
	 */
	public function getEiTypeIds() {
		$eiType = $this->utils->getEiFrame()->getContextEiEngine()->getEiMask()->getEiType();
		
		$eiTypeIds = [$eiType->getId()];
		foreach ($eiType->getSubEiTypes() as $subEiType) {
			$eiTypeIds[] = $subEiType->getId();	
		}
		return $eiTypeIds;
	}
	
	/**
	 * @return boolean
	 */
	public function isDraftMode() {
		return $this->eiuEntryFormFactory->isDraftMode();
	}
}
