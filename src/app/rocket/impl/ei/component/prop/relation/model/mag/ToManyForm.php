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
use n2n\web\dispatch\annotation\AnnoDispProperties;
use rocket\ei\util\frame\EiuFrame;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\map\bind\BindingErrors;
use rocket\core\model\Rocket;
use rocket\ei\manage\entry\EiEntry;
use n2n\web\dispatch\annotation\AnnoDispObjectArray;
use rocket\ei\manage\entry\UnknownEiObjectException;
use rocket\ei\manage\frame\Boundry;

class ToManyForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('selectedEntryPids'));
		$ai->p('currentMappingForms', new AnnoDispObjectArray(function (ToManyForm $toManyForm, $key) {
			return $toManyForm->eiuEntryFormFactory->getCurrentMappingForm($key);
		}));
		$ai->p('newMappingForms', new AnnoDispObjectArray(function (ToManyForm $toManyForm, $key) {
			return $toManyForm->eiuEntryFormFactory->getOrBuildNewMappingForm($key);
		}));
	}

	private $label;
	private $readUtils;
	private $min;
	private $max;
	private $eiuEntryFormFactory;
	private $entryLabeler;

	private $selectionModeEnabled = false;
	private $originalEntryPids;
	
	private $selectedEntryPids;
	private $currentMappingForms = array();
	private $newMappingForms = array();

	public function __construct(string $label, EiuFrame $readUtils, EiuFrame $editUtils, 
			int $min, int $max = null) {
		$this->label = $label;
		$this->readUtils = $readUtils;
		$this->min = $min;
		$this->max = $max;
		$this->eiuEntryFormFactory = new ToManyDynMappingFormFactory($editUtils);
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
		$this->eiuEntryFormFactory->setDraftMode($draftMode);
	}
	
	public function isDraftMode() {
		return $this->eiuEntryFormFactory->isDraftMode();
	}
	
	public function setSelectionModeEnabled(bool $selectionModeEnabled) {
		$this->selectionModeEnabled = $selectionModeEnabled;
	}
	
	public function isSelectionModeEnabled(): bool {
		return $this->selectionModeEnabled;
	}
	
	public function setOriginalEntryPids(array $originalEntryPids = null) {
		$this->originalEntryPids = $originalEntryPids;
	}
	
	public function getOriginalEntryPids() {
		return $this->originalEntryPids;
	}

	public function setSelectedEntryPids(array $selectedEntryPid = null) {
		$this->selectedEntryPids = $selectedEntryPid;
	}

	public function getSelectedEntryPids() {
		return $this->selectedEntryPids;
	}
	
	public function addEiEntry(EiEntry $currentEiEntry) {
		$this->eiuEntryFormFactory->addEiEntry($currentEiEntry);
		$this->currentMappingForms = $this->eiuEntryFormFactory->getCurrentMappingForms();
		$this->newMappingForms = $this->eiuEntryFormFactory->getNewMappingForms();
	}
	
	public function getCurrentMappingForms() {
		return $this->currentMappingForms;
	}

	public function setCurrentMappingForms(array $currentMappingForms) {
		$this->currentMappingForms = $currentMappingForms;
	}

	public function isNewMappingFormAvailable(): bool {
		return $this->eiuEntryFormFactory->isNewMappingFormAvailable();
	}

	public function setNewMappingFormAvailable($newMappingFormAvailable) {
		$this->eiuEntryFormFactory->setNewMappingFormAvailable($newMappingFormAvailable);
	}
	
	/**
	 * @return array|null
	 */
	public function getAllowedNewEiTypeIds() {
		return $this->eiuEntryFormFactory->getAllowedNewEiTypeIds();
	}
	
	/**
	 * @param array|null $allowedEiTypeIds
	 */
	public function setAllowedNewEiTypeIds(array $allowedEiTypeIds = null) {
		$this->eiuEntryFormFactory->setAllowedNewEiTypeIds($allowedEiTypeIds);
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
			$bd->closure(function (array $selectedEntryPids, BindingErrors $be) use ($that) {
				foreach ($selectedEntryPids as $selectedEntryPid) {
					if (in_array($selectedEntryPid, $that->originalEntryPids, true)) continue;
					
					$eiObject = null;
					try {
						$eiObject = $that->readUtils->lookupEiObjectById($that->readUtils->pidToId($selectedEntryPid),
								Boundry::NON_SECURITY_TYPES);
						$that->entryLabeler->setSelectedIdentityString($selectedEntryPid,
								$that->readUtils->createIdentityString($eiObject));
					} catch (UnknownEiObjectException $e) {
						$be->addErrorCode('entryPid', 'ei_impl_relation_unkown_entry_err',
								array('id_rep' => $selectedEntryPid), Rocket::NS);
					}
				}
			});
		}
		
		$bd->closure(function (array $selectedEntryPids, array $currentMappingForms, $newMappingForms,
				BindingErrors $be) use ($that) {
			$num = count($selectedEntryPids) + count($currentMappingForms) + count($newMappingForms);
				
			if ($num < $that->min) {
				$be->addErrorCode('entryPid', 'ei_impl_relation_min_err', 
						array('field' => $that->label, 'min' => $that->min, 'num' => $num, 
								'elements' => ($that->min < 2 ? $that->readUtils->getGenericLabel() 
										: $that->readUtils->getGenericPluralLabel())), 
						Rocket::NS);
			}
			
			if ($that->max !== null && $num > $that->max) {
				$be->addErrorCode('entryPid', 'ei_impl_relation_max_err', array('field' => $that->label,
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
	private $reduced = true;
	
	public function isSortable() {
		return $this->sortable;
	}
	
	public function setSortable(bool $sortable) {
		$this->sortable = $sortable;
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
		$eiType = $this->readUtils->getEiFrame()->getContextEiEngine()->getEiMask()->getEiType();
		
		$eiTypeIds = [$eiType->getId()];
		foreach ($eiType->getSubEiTypes() as $subEiType) {
			$eiTypeIds[] = $subEiType->getId();
		}
		return $eiTypeIds;
	}
}
