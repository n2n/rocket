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
use n2n\util\type\ArgUtils;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\map\PropertyPath;
use rocket\ei\manage\frame\EiFrame;
use n2n\reflection\property\AccessProxy;
use n2n\web\ui\UiComponent;
use n2n\web\dispatch\property\ManagedProperty;
use n2n\util\uri\Url;
use rocket\impl\ei\component\prop\relation\model\RelationEntry;
use n2n\web\dispatch\mag\UiOutfitter;
use rocket\ei\util\Eiu;
use rocket\ei\manage\frame\Boundry;

class ToOneMag extends MagAdapter {
	private $mandatory;
	private $targetReadUtils;
	private $targetEiuFrame;
	private $elementLabel;
	
	private $selectOverviewToolsUrl;
	private $newMappingFormUrl;
	private $draftMode;
	private $reduced = true;
	
	private $targetRelationEntry;
	
	public function __construct(string $label, bool $mandatory, EiFrame $targetReadEiFrame,
			EiFrame $targetEditEiFrame) {
		parent::__construct($label);
	
		$this->mandatory = $mandatory;
		$this->targetReadUtils = (new Eiu($targetReadEiFrame))->frame();
		$this->targetEiuFrame = (new Eiu($targetEditEiFrame))->frame();
	
// 		$this->updateContainerAttrs(true);
	}
	
// 	private function updateContainerAttrs(bool $group) {
// 		if ($group) {
// 			$this->setAttrs(array('class' => 'rocket-group'));
// 		} else {
// 			$this->setAttrs(array());
// 		}
// 	}
	
	public function setSelectOverviewToolsUrl(Url $selectOverviewToolsUrl = null) {
		$this->selectOverviewToolsUrl = $selectOverviewToolsUrl;

// 		$this->updateContainerAttrs($selectOverviewToolsUrl === null);
	}
	
	public function getSelectOverviewToolsUrl(): Url {
		return $this->selectOverviewToolsUrl;
	}
	
	public function setNewMappingFormUrl(Url $newMappingFormUrl = null) {
		$this->newMappingFormUrl = $newMappingFormUrl;
	}
	
	public function getNewEiuEntryFormUrl(): Url {
		return $this->newMappingFormUrl;
	}

	public function setDraftMode(bool $draftMode) {
		$this->draftMode = $draftMode;
	}
	
	public function setReduced(bool $reduced) {
		$this->reduced = $reduced;
	}
	
	public function setValue($value) {
		ArgUtils::valType($value, RelationEntry::class, true);
			
		$this->targetRelationEntry = $value;
	}
	
	public function getValue() {
		return $this->targetRelationEntry;
	}
	
	
	public function setFormValue($formValue) {
		ArgUtils::assertTrue($formValue instanceof ToOneForm);

		$this->targetRelationEntry = null;
		
		if (null !== ($targetEiEntry = $formValue->buildEiEntry())) {
			$this->targetRelationEntry = RelationEntry::fromM($targetEiEntry);
			return;
		} 
				
		if ($formValue->isSelectionModeEnabled() 
				&& null !== ($entryPid = $formValue->getSelectedEntryPid())) {
			if ($this->targetRelationEntry !== null && !$this->targetRelationEntry->isNew()
					&& $this->targetReadUtils->idToPid($this->targetRelationEntry->getId()) === $entryPid) {
				return;
			}
				
			$this->targetRelationEntry = RelationEntry::from($this->targetReadUtils->lookupEiObjectById(
					$this->targetReadUtils->pidToId($entryPid), Boundry::NON_SECURITY_TYPES));
			return;
		}
		
		$this->targetRelationEntry = null;		
	}
	
	public function getFormValue() {
		$toOneForm = new ToOneForm($this->labelLstr, $this->mandatory, $this->targetReadUtils, $this->targetEiuFrame);
		$toOneForm->setSelectionModeEnabled($this->selectOverviewToolsUrl !== null);
		$toOneForm->setNewMappingFormAvailable($this->newMappingFormUrl !== null);
		$toOneForm->setDraftMode($this->draftMode);
		$toOneForm->setReduced($this->reduced);
		
		if ($this->targetRelationEntry === null) {
			if (!$toOneForm->isSelectionModeEnabled() && $this->mandatory
					&& !$this->targetEiuFrame->getContextEiType()->hasSubEiTypes()) {
				$toOneForm->setEiEntry($this->targetEiuFrame->newEntry($this->draftMode)->getEiEntry());
				$toOneForm->setNewMappingFormAvailable(true);
			}
			
			return $toOneForm;
		}
		
		if ($toOneForm->isSelectionModeEnabled() && !$this->targetRelationEntry->isNew()) {
			$pid = $this->targetReadUtils->idToPid($this->targetRelationEntry->getId());
			$toOneForm->setOriginalEntryPid($pid);
			$toOneForm->setSelectedEntryPid($pid);
			$toOneForm->getEntryLabeler()->setSelectedIdentityString($pid,
					$this->targetReadUtils->createIdentityString($this->targetRelationEntry->getEiObject()));
		} else if ($this->targetRelationEntry->hasEiEntry()) {
			$toOneForm->setEiEntry($this->targetRelationEntry->getEiEntry());
		} else {
			$toOneForm->setEiEntry($this->targetEiuFrame->entry($this->targetRelationEntry->getEiObject())->getEiEntry());
		}
		
		if (null !== $toOneForm->getNewMappingForm()) {
			$toOneForm->setNewMappingFormAvailable(true);
		}
				
		return $toOneForm;
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
	public function createUiField(PropertyPath $propertyPath, HtmlView $view, UiOutfitter $uiOutfitter): UiComponent {
		$eiFrame = $this->targetReadUtils->getEiFrame();
		$targetControllerContext = $eiFrame->getControllerContext();
		$request = $view->getRequest();
		
// 		$filterJhtmlHook = ScrFilterPropController::buildFilterJhtmlHook($view->lookup(ScrRegistry::class), 
// 				$eiFrame->getContextEiEngine()->getEiMask());
		
		$newMappingFormUrl = null;
		if ($this->targetEiuFrame->getContextEiType()->hasSubEiTypes()
				|| $this->selectOverviewToolsUrl !== null
				|| !$this->mandatory) {
			$newMappingFormUrl = $this->newMappingFormUrl;
		}
		
		return $view->getImport('\rocket\impl\ei\component\prop\relation\view\toOneForm.html',
				array('selectOverviewToolsUrl' => $this->selectOverviewToolsUrl, 
						'newMappingFormUrl' => $newMappingFormUrl, 'propertyPath' => $propertyPath));
	}
}
