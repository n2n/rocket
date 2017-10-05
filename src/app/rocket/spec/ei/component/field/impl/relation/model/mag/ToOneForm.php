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

use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObject;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\map\bind\BindingErrors;
use rocket\core\model\Rocket;
use rocket\spec\ei\manage\mapping\EiEntry;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;

class ToOneForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('selectedEntryIdRep'));
		$ai->p('currentMappingForm', new AnnoDispObject(function (ToOneForm $toOneForm) {
			return $toOneForm->entryFormFactory->getCurrentMappingForm();
		}));
		$ai->p('newMappingForm', new AnnoDispObject(function (ToOneForm $toOneForm) {
			return $toOneForm->entryFormFactory->getOrBuildNewMappingForm();
		}));
	}

	private $label;
	private $mandatory;
	private $utils;
	private $entryFormFactory;
	private $entryLabeler;
	
	private $selectionModeEnabled = false;
	private $originalEntryIdRep; 
	
	private $selectedEntryIdRep;
	private $currentMappingForm;
	private $newMappingForm;

	public function __construct(string $label, bool $mandatory, EiuFrame $utils) {
		$this->label = $label;
		$this->mandatory = $mandatory;
		$this->utils = $utils;
		$this->entryFormFactory = new ToOneDynMappingFormFactory($utils);
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
	
	public function setOriginalEntryIdRep(string $originalEntryIdRep = null) {
		$this->originalEntryIdRep = $originalEntryIdRep;
	}
	
	public function getOriginalEntryIdRep() {
		return $this->originalEntryIdRep;
	}

	public function setSelectedEntryIdRep(string $selectedEntryIdRep = null) {
		$this->selectedEntryIdRep = $selectedEntryIdRep;
	}

	public function getSelectedEntryIdRep() {
		return $this->selectedEntryIdRep;
	}
	
	public function isMappingFormAvailable(): bool {
		return null !== $this->entryFormFactory->getCurrentMappingForm();
	}
	
	public function setDraftMode(bool $draftMode) {
		$this->entryFormFactory->setDraftMode($draftMode);
	}

	public function setEiEntry(EiEntry $eiEntry = null) {
		$this->entryFormFactory->setEiEntry($eiEntry);
		$this->currentMappingForm = $this->entryFormFactory->getCurrentMappingForm();
		$this->newMappingForm = $this->entryFormFactory->getNewMappingForm();
	}
	
	public function getCurrentMappingForm() {
		return $this->currentMappingForm;
	}

	public function setCurrentMappingForm(MappingForm $currentMappingForm = null) {
		$this->currentMappingForm = $currentMappingForm;
	}

	public function isNewMappingFormAvailable(): bool {
		return $this->entryFormFactory->isNewMappingFormAvailable();
	}

	public function setNewMappingFormAvailable($newMappingFormAvailable) {
		$this->entryFormFactory->setNewMappingFormAvailable($newMappingFormAvailable);
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
			$bd->closure(function ($selectedEntryIdRep, BindingErrors $be) use ($that) {
				if ($that->originalEntryIdRep === $selectedEntryIdRep || $selectedEntryIdRep === null) {
					return;
				}
						
				if (null !== ($eiObject = $that->utils->lookupEiObjectById(
						$that->utils->idRepToId($selectedEntryIdRep), CriteriaConstraint::NON_SECURITY_TYPES))) {
					$that->entryLabeler->setSelectedIdentityString($selectedEntryIdRep, 
							$that->utils->createIdentityString($eiObject));				
					return;
				}
					
				$be->addErrorCode('entryIdRep', 'ei_impl_relation_unkown_entry_err', array('id_rep' => $selectedEntryIdRep),
						Rocket::NS);
			});
		}
		
		if ($this->mandatory) {
			$that = $this;
			$bd->closure(function ($selectedEntryIdRep, $currentMappingForm, $newMappingForm,
					BindingErrors $be) use ($that) {
				if ($selectedEntryIdRep !== null || $currentMappingForm !== null || $newMappingForm !== null) return;

				// @todo find out how to register error on parent property / dispatchable
				$be->addErrorCode('entryIdRep', 'common_field_required_err', array('field' => $that->label), 
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
	
	public function isCompact() {
		return true;
	}
	
	
	
	public function isDraftMode() {
		return $this->entryFormFactory->isDraftMode();
	}
}
