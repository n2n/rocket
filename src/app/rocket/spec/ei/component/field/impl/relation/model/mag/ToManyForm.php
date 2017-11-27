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
use n2n\web\dispatch\annotation\AnnoDispProperties;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\map\bind\BindingErrors;
use rocket\core\model\Rocket;
use rocket\spec\ei\manage\mapping\EiEntry;
use n2n\web\dispatch\annotation\AnnoDispObjectArray;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use rocket\spec\ei\manage\util\model\UnknownEntryException;

class ToManyForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('selectedEntryIdReps'));
		$ai->p('currentMappingForms', new AnnoDispObjectArray(function (ToManyForm $toManyForm, $key) {
			return $toManyForm->entryFormFactory->getCurrentMappingForm($key);
		}));
		$ai->p('newMappingForms', new AnnoDispObjectArray(function (ToManyForm $toManyForm, $key) {
			return $toManyForm->entryFormFactory->getOrBuildNewMappingForm($key);
		}));
	}

	private $label;
	private $readUtils;
	private $min;
	private $max;
	private $entryFormFactory;
	private $entryLabeler;

	private $selectionModeEnabled = false;
	private $originalEntryIdReps;
	
	private $selectedEntryIdReps;
	private $currentMappingForms;
	private $newMappingForms;

	public function __construct(string $label, EiuFrame $readUtils, EiuFrame $editUtils, 
			int $min, int $max = null) {
		$this->label = $label;
		$this->readUtils = $readUtils;
		$this->min = $min;
		$this->max = $max;
		$this->entryFormFactory = new ToManyDynMappingFormFactory($editUtils);
		$this->entryLabeler = new EntryLabeler($readUtils);
	}

	public function getMin(): int {
		return $this->min;
	}
	
	public function getMax() {
		return $this->max;
	}
	
	public function getEntryLabeler(): EntryLabeler {
		return $this->entryLabeler;
	}
	
	public function setDraftMode(bool $draftMode) {
		$this->entryFormFactory->setDraftMode($draftMode);
	}
	
	public function isDraftMode() {
		return $this->entryFormFactory->isDraftMode();
	}
	
	public function setSelectionModeEnabled(bool $selectionModeEnabled) {
		$this->selectionModeEnabled = $selectionModeEnabled;
	}
	
	public function isSelectionModeEnabled(): bool {
		return $this->selectionModeEnabled;
	}
	
	public function setOriginalEntryIdReps(array $originalEntryIdReps = null) {
		$this->originalEntryIdReps = $originalEntryIdReps;
	}
	
	public function getOriginalEntryIdReps() {
		return $this->originalEntryIdReps;
	}

	public function setSelectedEntryIdReps(array $selectedEntryIdRep = null) {
		$this->selectedEntryIdReps = $selectedEntryIdRep;
	}

	public function getSelectedEntryIdReps() {
		return $this->selectedEntryIdReps;
	}
	
	public function addEiEntry(EiEntry $currentEiEntry) {
		$this->entryFormFactory->addEiEntry($currentEiEntry);
		$this->currentMappingForms = $this->entryFormFactory->getCurrentMappingForms();
		$this->newMappingForms = $this->entryFormFactory->getNewMappingForms();
	}
	
	public function getCurrentMappingForms() {
		return $this->currentMappingForms;
	}

	public function setCurrentMappingForms(array $currentMappingForms) {
		$this->currentMappingForms = $currentMappingForms;
	}

	public function isNewMappingFormAvailable(): bool {
		return $this->entryFormFactory->isNewMappingFormAvailable();
	}

	public function setNewMappingFormAvailable($newMappingFormAvailable) {
		$this->entryFormFactory->setNewMappingFormAvailable($newMappingFormAvailable);
	}
	
	/**
	 * @return array|null
	 */
	public function getAllowedNewEiTypeIds() {
		return $this->entryFormFactory->getAllowedNewEiTypeIds();
	}
	
	/**
	 * @param array|null $allowedEiTypeIds
	 */
	public function setAllowedNewEiTypeIds(array $allowedEiTypeIds = null) {
		$this->entryFormFactory->setAllowedNewEiTypeIds($allowedEiTypeIds);
	}

	public function getNewMappingForms() {
		return $this->newMappingForms;
	}

	public function setNewMappingForms(array $newMappingForms) {
		$this->newMappingForms = $newMappingForms;
	}
	
	private function _validation(BindingDefinition $bd) {
		$that = $this;
		
		if ($this->selectionModeEnabled) {
			$bd->closure(function (array $selectedEntryIdReps, BindingErrors $be) use ($that) {
				foreach ($selectedEntryIdReps as $selectedEntryIdRep) {
					if (in_array($selectedEntryIdRep, $that->originalEntryIdReps, true)) continue;
					
					$eiObject = null;
					try {
						$eiObject = $that->readUtils->lookupEiObjectById($that->readUtils->idRepToId($selectedEntryIdRep),
								CriteriaConstraint::NON_SECURITY_TYPES);
						$that->entryLabeler->setSelectedIdentityString($selectedEntryIdRep,
								$that->readUtils->createIdentityString($eiObject));
					} catch (UnknownEntryException $e) {
						$be->addErrorCode('entryIdRep', 'ei_impl_relation_unkown_entry_err',
								array('id_rep' => $selectedEntryIdRep), Rocket::NS);
					}
				}
			});
		}
		
		$bd->closure(function (array $selectedEntryIdReps, array $currentMappingForms, $newMappingForms,
				BindingErrors $be) use ($that) {
			$num = count($selectedEntryIdReps) + count($currentMappingForms) + count($newMappingForms);
				
			if ($num < $that->min) {
				$be->addErrorCode('entryIdRep', 'ei_impl_relation_min_err', 
						array('field' => $that->label, 'min' => $that->min, 'num' => $num, 
								'elements' => ($that->min < 2 ? $that->readUtils->getGenericLabel() 
										: $that->readUtils->getGenericPluralLabel())), 
						Rocket::NS);
			}
			
			if ($that->max !== null && $num > $that->max) {
				$be->addErrorCode('entryIdRep', 'ei_impl_relation_max_err', array('field' => $that->label,
						'max' => $that->max, 'num' => $num, 
								'elements' => ($that->min < 2 ? $that->readUtils->getGenericLabel() 
										: $that->readUtils->getGenericPluralLabel())), Rocket::NS);
			}
		});
	}
	
	public function buildEiEntrys(): array {
		$eiEntrys = array();
		$keyOrderIndexMap = array();
		
		foreach ($this->currentMappingForms as $currentMappingForm) {
			$keyOrderIndexMap[] = $currentMappingForm->getOrderIndex();
			$eiEntrys[] = $currentMappingForm->buildEiEntry();
		}
		
		foreach ($this->newMappingForms as $newMappingForm) {
			$keyOrderIndexMap[] = $newMappingForm->getOrderIndex();
			$eiEntrys[] = $newMappingForm->buildEiEntry();
		}
		
		asort($keyOrderIndexMap, SORT_NUMERIC);
		
		$sortedEiEntrys = array();
		foreach ($keyOrderIndexMap as $key => $orderIndex) {
			$sortedEiEntrys[] = $eiEntrys[$key];
		}
		return $sortedEiEntrys;
	}
	
	private $sortable = true;
	private $compact = true;
	
	public function isSortable() {
		return $this->sortable;
	}
	
	public function setSortable(bool $sortable) {
		$this->sortable = $sortable;
	}
	
	public function isCompact() {
		return $this->compact;
	}
	
	public function setCompact(bool $compact) {
		$this->compact = $compact;
	}
}
