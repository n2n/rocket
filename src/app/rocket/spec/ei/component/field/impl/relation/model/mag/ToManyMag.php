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

use n2n\web\dispatch\mag\Mag;
use n2n\impl\web\dispatch\mag\model\MagAdapter;
use n2n\impl\web\dispatch\property\ObjectProperty;
use n2n\reflection\ArgUtils;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\map\PropertyPath;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\reflection\property\AccessProxy;
use n2n\web\ui\UiComponent;
use n2n\web\dispatch\property\ManagedProperty;
use n2n\util\uri\Url;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\component\field\impl\relation\model\RelationEntry;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use rocket\spec\ei\manage\draft\Draft;

class ToManyMag extends MagAdapter {
	private $min;
	private $max;
	private $targetReadUtils;
	private $targetEditUtils;
	private $elementLabel;
	
	private $selectOverviewToolsUrl;
	private $newMappingFormUrl;
	private $draftMode = false;
	private $targetOrderEiFieldPath;
	
	private $targetRelationEntries = array();
	private $targetEiMappings = array();
	
	public function __construct(string $propertyName, string $label, EiState $targetReadEiState, 
			EiState $targetEditEiState, int $min, int $max = null) {
		parent::__construct($propertyName, $label);
	
		$this->targetReadUtils = new EiuFrame($targetReadEiState);
		$this->targetEditUtils = new EiuFrame($targetEditEiState);
		$this->min = $min;
		$this->max = $max;
		
		$this->updateContainerAttrs(true);
	}
	
	private function updateContainerAttrs(bool $group) {
		if ($group) {
			$this->setAttrs(array('class' => 'rocket-control-group rocket-block'));
		} else {
			$this->setAttrs(array('class' => 'rocket-block'));
		}
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

	public function setDraftMode(bool $draftMode) {
		$this->draftMode = $draftMode;
	}
	
	public function setTargetOrderEiFieldPath(EiFieldPath $targetOrderEiFieldPath = null) {
		$this->targetOrderEiFieldPath = $targetOrderEiFieldPath;
	}
	
	public function setValue($targetRelationEntries) {
		ArgUtils::valArray($targetRelationEntries, RelationEntry::class);
			
		$this->targetRelationEntries = $targetRelationEntries;
	}
	
	public function getValue() {
		return $this->targetRelationEntries;
	}
	
	public function getRelatedTargetEiMappings(): array {
		return $this->targetEiMappings;
	}

	public function getFormValue() {
		$toManyForm = new ToManyForm($this->labelLstr, $this->targetReadUtils, $this->targetEditUtils, $this->min, $this->max);
	
		if ($this->selectOverviewToolsUrl !== null) {
			$toManyForm->setSelectionModeEnabled(true);
			$idReps = array();
			foreach ($this->targetRelationEntries as $targetRelationEntry) {
				if (!$targetRelationEntry->isNew()) {
					$idReps[] = $idRep = $this->targetReadUtils->idToIdRep($targetRelationEntry->getId());
					$toManyForm->getEntryLabeler()->setSelectedIdentityString($idRep,
							$this->targetReadUtils->createIdentityString($targetRelationEntry->getEiSelection()));
				} else if ($targetRelationEntry->hasEiMapping()) {
					$toManyForm->addEiMapping($targetRelationEntry->getEiMapping());
				} else {
					$toManyForm->addEiMapping($this->targetEditUtils->createEiMapping(
							$targetRelationEntry->getEiSelection()));
				}
			}
			$toManyForm->setSelectedEntryIdReps($idReps);
			$toManyForm->setOriginalEntryIdReps($idReps);
		} else {
			foreach ($this->targetRelationEntries as $targetRelationEntry) {
				if ($targetRelationEntry->hasEiMapping()) {
					$toManyForm->addEiMapping($targetRelationEntry->getEiMapping());
				} else {
					$toManyForm->addEiMapping($this->targetEditUtils->createEiMapping(
							$targetRelationEntry->getEiSelection()));
				}
			}
		}
	
		$toManyForm->setNewMappingFormAvailable($this->newMappingFormUrl !== null);
		$toManyForm->setDraftMode($this->draftMode);
		
		return $toManyForm;
	}
	
	private function buildLiveIdRepRelationEntryMap(): array {
		$targetRelationEntries = array();
		foreach ($this->targetRelationEntries as $targetRelationEntry) {
			$targetRelationEntries[$this->targetReadUtils->idToIdRep($targetRelationEntry->getId())] 
					= $targetRelationEntry;
		}
		return $targetRelationEntries;
	}
	
	public function setFormValue($formValue) {
		ArgUtils::assertTrue($formValue instanceof ToManyForm);

		$this->targetRelationEntries = array();

		if ($formValue->isSelectionModeEnabled()) {
			$oldTargetRelationEntries = $this->buildLiveIdRepRelationEntryMap();
			foreach ($formValue->getSelectedEntryIdReps() as $idRep) {
				if (isset($oldTargetRelationEntries[$idRep])) {
					$this->targetRelationEntries[$idRep] = $oldTargetRelationEntries[$idRep];
					continue;
				}
		
				$this->targetRelationEntries[$idRep] = RelationEntry::from($this->targetReadUtils->lookupEiSelectionById(
						$this->targetReadUtils->idRepToId($idRep), CriteriaConstraint::NON_SECURITY_TYPES));
			}
		}
		
		$orderIndex = 10;
		foreach ($formValue->buildEiMappings() as $targetEiMapping) {
			if ($this->targetOrderEiFieldPath !== null) {
				$targetEiMapping->setValue($this->targetOrderEiFieldPath, $orderIndex += 10, true);
			}
			
			if ($targetEiMapping->isNew()) {
				$this->targetRelationEntries[] = RelationEntry::fromM($targetEiMapping);
				if ($targetEiMapping->getEiSelection()->isDraft()) {
					$targetEiMapping->getEiSelection()->getDraft()->setType(Draft::TYPE_UNLISTED);
				}
			} else if ($targetEiMapping->getEiSelection()->isDraft()) {
				$this->targetRelationEntries['d' . $targetEiMapping->getEiSelection()->getIdRep()] = RelationEntry::fromM($targetEiMapping);
			} else {
				$this->targetRelationEntries['c' . $targetEiMapping->getIdRep()] = RelationEntry::fromM($targetEiMapping);
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
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::createUiField()
	 */
	public function createUiField(PropertyPath $propertyPath, HtmlView $view): UiComponent {
		return $view->getImport('\rocket\spec\ei\component\field\impl\relation\view\toManyForm.html',
				array('selectOverviewToolsUrl' => $this->selectOverviewToolsUrl, 
						'newMappingFormUrl' => $this->newMappingFormUrl, 'propertyPath' => $propertyPath));
	}
}
