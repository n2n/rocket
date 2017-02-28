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
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\reflection\property\AccessProxy;
use n2n\web\ui\UiComponent;
use n2n\web\dispatch\property\ManagedProperty;
use n2n\util\uri\Url;
use rocket\spec\ei\manage\critmod\filter\impl\controller\GlobalFilterFieldController;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\spec\ei\component\field\impl\relation\model\RelationEntry;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;

class ToOneMag extends MagAdapter {
	private $mandatory;
	private $targetReadUtils;
	private $targetEditUtils;
	private $elementLabel;
	
	private $selectOverviewToolsUrl;
	private $newMappingFormUrl;
	private $draftMode;
	
	private $targetRelationEntry;
	
	public function __construct(string $propertyName, string $label, bool $mandatory, EiFrame $targetReadEiFrame,
			EiFrame $targetEditEiFrame) {
		parent::__construct($propertyName, $label);
	
		$this->mandatory = $mandatory;
		$this->targetReadUtils = new EiuFrame($targetReadEiFrame);
		$this->targetEditUtils = new EiuFrame($targetEditEiFrame);
	
		$this->updateContainerAttrs(true);
	}
	
	private function updateContainerAttrs(bool $group) {
		if ($group) {
			$this->setAttrs(array('class' => 'rocket-control-group'));
		} else {
			$this->setAttrs(array());
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
	
	public function getNewEntryFormUrl(): Url {
		return $this->newMappingFormUrl;
	}

	public function setDraftMode(bool $draftMode) {
		$this->draftMode = $draftMode;
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
		
		if (null !== ($targetEiMapping = $formValue->buildEiMapping())) {
			$this->targetRelationEntry = RelationEntry::fromM($targetEiMapping);
			return;
		} 
				
		if ($formValue->isSelectionModeEnabled() 
				&& null !== ($entryIdRep = $formValue->getSelectedEntryIdRep())) {
			if ($this->targetRelationEntry !== null && !$this->targetRelationEntry->isNew()
					&& $this->targetReadUtils->idToIdRep($this->targetRelationEntry->getId()) === $entryIdRep) {
				return;
			}
				
			$this->targetRelationEntry = RelationEntry::from($this->targetReadUtils->lookupEiSelectionById(
					$this->targetReadUtils->idRepToId($entryIdRep), CriteriaConstraint::NON_SECURITY_TYPES));
			return;
		}
		
		$this->targetRelationEntry = null;		
	}
	
	public function getFormValue() {
		$toOneForm = new ToOneForm($this->labelLstr, $this->mandatory, $this->targetReadUtils, $this->targetEditUtils);
		$toOneForm->setSelectionModeEnabled($this->selectOverviewToolsUrl !== null);
		$toOneForm->setNewMappingFormAvailable($this->newMappingFormUrl !== null);
		$toOneForm->setDraftMode($this->draftMode);
		
		if ($this->targetRelationEntry === null) return $toOneForm;
		
		if ($toOneForm->isSelectionModeEnabled() && !$this->targetRelationEntry->isNew()) {
			$idRep = $this->targetReadUtils->idToIdRep($this->targetRelationEntry->getId());
			$toOneForm->setOriginalEntryIdRep($idRep);
			$toOneForm->setSelectedEntryIdRep($idRep);
			$toOneForm->getEntryLabeler()->setSelectedIdentityString($idRep,
					$this->targetReadUtils->createIdentityString($this->targetRelationEntry->getEiSelection()));
		} else if ($this->targetRelationEntry->hasEiMapping()) {
			$toOneForm->setEiMapping($this->targetRelationEntry->getEiMapping());
		} else {
			$toOneForm->setEiMapping($this->targetEditUtils->createEiMapping(
					$this->targetRelationEntry->getEiSelection()));
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
	public function createUiField(PropertyPath $propertyPath, HtmlView $view): UiComponent {
		$eiFrame = $this->targetReadUtils->getEiFrame();
		$targetControllerContext = $eiFrame->getControllerContext();
		$request = $view->getRequest();
		
		$filterAjahHook = GlobalFilterFieldController::buildFilterAjahHook($view->lookup(ScrRegistry::class), 
				$eiFrame->getContextEiMask());
		
		return $view->getImport('\rocket\spec\ei\component\field\impl\relation\view\toOneForm.html',
				array('selectOverviewToolsUrl' => $this->selectOverviewToolsUrl, 
						'newMappingFormUrl' => $this->newMappingFormUrl, 'propertyPath' => $propertyPath));
	}
}
