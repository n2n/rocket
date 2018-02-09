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

use n2n\impl\web\dispatch\mag\model\MagAdapter;
use n2n\impl\web\dispatch\property\ObjectProperty;
use n2n\reflection\ArgUtils;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\map\PropertyPath;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\reflection\property\AccessProxy;
use n2n\web\ui\UiComponent;
use n2n\web\dispatch\property\ManagedProperty;
use n2n\util\uri\Url;
use rocket\spec\ei\EiPropPath;
use rocket\impl\ei\component\prop\relation\model\RelationEntry;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\web\dispatch\mag\UiOutfitter;

class ToManyMag extends MagAdapter {
	private $min;
	private $max;
	private $targetReadUtils;
	private $targetEditUtils;
	private $elementLabel;
	private $reduced = true;
	
	private $selectOverviewToolsUrl;
	private $newMappingFormUrl;
	private $allowedNewEiTypeIds = null;
	private $draftMode = false;
	private $targetOrderEiPropPath;
	
	private $targetRelationEntries = array();
	private $targetEiEntrys = array();
	
	public function __construct(string $label, EiFrame $targetReadEiFrame, 
			EiFrame $targetEditEiFrame, int $min, int $max = null) {
		parent::__construct($label);
	
		$this->targetReadUtils = new EiuFrame($targetReadEiFrame);
		$this->targetEditUtils = new EiuFrame($targetEditEiFrame);
		$this->min = $min;
		$this->max = $max;
		
		$this->updateContainerAttrs(true);
	}
	
	private function updateContainerAttrs(bool $group) {
// 		$this->setAttrs(array('class' => 'rocket-block'));
	}
	
	public function setReduced(bool $reduced) {
		$this->reduced = $reduced;
	}
	
	public function setSelectOverviewToolsUrl(Url $selectOverviewToolsUrl = null) {
		$this->selectOverviewToolsUrl = $selectOverviewToolsUrl;
		
		$this->updateContainerAttrs($selectOverviewToolsUrl === null);
	}
	
	public function getSelectOverviewToolsUrl(): Url {
		return $this->selectOverviewToolsUrl;
	}
	
	public function setNewMappingFormUrl(Url $newMappingFormUrl = null) {
		$this->newMappingFormUrl = $newMappingFormUrl;
	}
	
	public function getNewMappingFormUrl(): Url {
		return $this->newMappingFormUrl;
	}
	
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
	
	public function setTargetOrderEiPropPath(EiPropPath $targetOrderEiPropPath = null) {
		$this->targetOrderEiPropPath = $targetOrderEiPropPath;
	}
	
	public function setValue($targetRelationEntries) {
		ArgUtils::valArray($targetRelationEntries, RelationEntry::class);
			
		$this->targetRelationEntries = $targetRelationEntries;
	}
	
	public function getValue() {
		return $this->targetRelationEntries;
	}
	
	public function getRelatedTargetEiEntrys(): array {
		return $this->targetEiEntrys;
	}

	public function getFormValue() {
		$toManyForm = new ToManyForm($this->labelLstr, $this->targetReadUtils, $this->targetEditUtils, $this->min, 
				$this->max);
		$toManyForm->setSortable($this->targetOrderEiPropPath !== null);
		$toManyForm->setReduced($this->reduced);
	
		if ($this->selectOverviewToolsUrl !== null) {
			$toManyForm->setSelectionModeEnabled(true);
			$eiIds = array();
			foreach ($this->targetRelationEntries as $targetRelationEntry) {
				if (!$targetRelationEntry->isNew()) {
					$eiIds[] = $eiId = $this->targetReadUtils->idToEiId($targetRelationEntry->getId());
					$toManyForm->getEntryLabeler()->setSelectedIdentityString($eiId,
							$this->targetReadUtils->createIdentityString($targetRelationEntry->getEiObject()));
				} else if ($targetRelationEntry->hasEiEntry()) {
					$toManyForm->addEiEntry($targetRelationEntry->getEiEntry());
				} else {
					$toManyForm->addEiEntry($this->targetEditUtils->createEiEntry(
							$targetRelationEntry->getEiObject()));
				}
			}
			$toManyForm->setSelectedEntryEiIds($eiIds);
			$toManyForm->setOriginalEntryEiIds($eiIds);
		} else {
			foreach ($this->targetRelationEntries as $targetRelationEntry) {
				if ($targetRelationEntry->hasEiEntry()) {
					$toManyForm->addEiEntry($targetRelationEntry->getEiEntry());
				} else {
					$toManyForm->addEiEntry($this->targetEditUtils->createEiEntry(
							$targetRelationEntry->getEiObject()));
				}
			}
		}
	
		$toManyForm->setNewMappingFormAvailable($this->newMappingFormUrl !== null);
		$toManyForm->setAllowedNewEiTypeIds($this->allowedNewEiTypeIds);
		$toManyForm->setDraftMode($this->draftMode);
		
		return $toManyForm;
	}
	
	private function buildLiveEiIdRelationEntryMap(): array {
		$targetRelationEntries = array();
		foreach ($this->targetRelationEntries as $targetRelationEntry) {
			$targetRelationEntries[$this->targetReadUtils->idToEiId($targetRelationEntry->getId())] 
					= $targetRelationEntry;
		}
		return $targetRelationEntries;
	}
	
	public function setFormValue($formValue) {
		ArgUtils::assertTrue($formValue instanceof ToManyForm);

		$this->targetRelationEntries = array();

		if ($formValue->isSelectionModeEnabled()) {
			$oldTargetRelationEntries = $this->buildLiveEiIdRelationEntryMap();
			foreach ($formValue->getSelectedEntryEiIds() as $eiId) {
				if (isset($oldTargetRelationEntries[$eiId])) {
					$this->targetRelationEntries[$eiId] = $oldTargetRelationEntries[$eiId];
					continue;
				}
		
				$this->targetRelationEntries[$eiId] = RelationEntry::from($this->targetReadUtils->lookupEiObjectById(
						$this->targetReadUtils->eiIdToId($eiId), CriteriaConstraint::NON_SECURITY_TYPES));
			}
		}
		
		$orderIndex = 10;
		foreach ($formValue->buildEiEntrys() as $targetEiEntry) {
			if ($this->targetOrderEiPropPath !== null) {
				$eiu = new Eiu($targetEiEntry, $this->targetEditUtils->getEiFrame());
				$eiu->entry()->setScalarValue($this->targetOrderEiPropPath, $orderIndex += 10, true);
			}
			
			if ($targetEiEntry->isNew()) {
				$this->targetRelationEntries[] = RelationEntry::fromM($targetEiEntry);
				if ($targetEiEntry->getEiObject()->isDraft()) {
					$targetEiEntry->getEiObject()->getDraft()->setType(Draft::TYPE_UNLISTED);
				}
			} else if ($targetEiEntry->getEiObject()->isDraft()) {
				$this->targetRelationEntries['d' . $targetEiEntry->getEiObject()->getEiId()] = RelationEntry::fromM($targetEiEntry);
			} else {
				$this->targetRelationEntries['c' . $targetEiEntry->getEiId()] = RelationEntry::fromM($targetEiEntry);
			}
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::createManagedProperty()
	 */
	public function createManagedProperty(AccessProxy $accessProxy): ManagedProperty {
		return new ObjectProperty($accessProxy, false);
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::setupBindingDefinition()
	 */
	public function setupBindingDefinition(BindingDefinition $bindingDefinition) {
		if ($this->allowedNewEiTypeIds !== null) {
			$toManyMappingResult = $bindingDefinition->getMappingResult()->__get($this->propertyName);
			foreach ($toManyMappingResult->__get('newMappingForms') as $key => $mfMappingResult) {
				$chosenId = null;
				if ($mfMappingResult->entryForm->containsPropertyName('chosenId')) {
					$chosenId = $mfMappingResult->entryForm->chosenId;
				} else {
					$chosenId = $mfMappingResult->entryForm->getObject()->getChosenId();
				}
				
				if (in_array($chosenId, $this->allowedNewEiTypeIds)) continue;
				
				$mfMappingResult->getBindingErrors()->addErrorCode('chosenId', 'ei_impl_ei_type_disallowed');
			}
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::createUiField()
	 */
	public function createUiField(PropertyPath $propertyPath, HtmlView $view, UiOutfitter $uiOutfitter): UiComponent {
		return $view->getImport('\rocket\impl\ei\component\prop\relation\view\toManyForm.html',
				array('selectOverviewToolsUrl' => $this->selectOverviewToolsUrl, 
						'newMappingFormUrl' => $this->newMappingFormUrl, 'propertyPath' => $propertyPath));
	}
}
