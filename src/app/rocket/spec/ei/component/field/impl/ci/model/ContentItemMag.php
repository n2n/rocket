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
namespace rocket\spec\ei\component\field\impl\ci\model;

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
use n2n\web\dispatch\mag\MagCollection;
use rocket\spec\ei\component\field\impl\relation\model\mag\ToManyMag;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\ex\NotYetImplementedException;
use n2n\impl\web\dispatch\mag\model\MagForm;
use rocket\spec\ei\component\field\impl\ci\ContentItemsEiField;

class ContentItemMag extends MagAdapter {
	private $panelConfigs = array();
	private $targetReadEiState;
	private $targetEditEiState;
	
	private $draftMode = false;
	private $newMappingFormUrl;
	
	private $targetRelationEntries = array();
	
	public function __construct(string $propertyName, string $label, array $panelConfigs, 
			EiState $targetReadEiState, EiState $targetEditEiState) {
		parent::__construct($propertyName, $label);
	
		$this->panelConfigs = $panelConfigs;
		$this->targetReadEiState = $targetReadEiState;
		$this->targetEditEiState = $targetEditEiState;
		
		$this->setContainerAttrs(array('class' => 'rocket-control-group'));
	}

	public function setDraftMode(bool $draftMode) {
		$this->draftMode = $draftMode;
	}	

	public function setNewMappingFormUrl(Url $newMappingFormUrl = null) {
		$this->newMappingFormUrl = $newMappingFormUrl;
	}
	
	public function setValue($targetRelationEntries) {
		ArgUtils::valArray($targetRelationEntries, RelationEntry::class);
		
		$this->targetRelationEntries = $targetRelationEntries;
	}
	
	private function groupRelationEntries(array $targetRelationEntries) {
		$targetEditUtils = new EiuFrame($this->targetEditEiState);
		$panelEiFieldPath = ContentItemsEiField::getPanelEiFieldPath();
		$filtered = array();
		foreach ($targetRelationEntries as $targetRelationEntry) {
			if (!$targetRelationEntry->hasEiMapping()) {
				$targetRelationEntry = RelationEntry::fromM($targetEditUtils
						->createEiMapping($targetRelationEntry->getEiSelection()));
			}
			
			$panelName = $targetRelationEntry->getEiMapping()->getValue($panelEiFieldPath);
			if (!isset($filtered[$panelName])) {
				$filtered[$panelName] = array();
			}
			
			$filtered[$panelName][] = $targetRelationEntry;
		}
		return $filtered;
	}
	
	public function getValue() {
		return $this->targetRelationEntries;
	}
	
	public function getFormValue() {
		$magCollection = new MagCollection();
		
		$groupedTargetRelationEntries = $this->groupRelationEntries($this->targetRelationEntries);
		
		$orderEiFieldPath = ContentItemsEiField::getOrderIndexEiFieldPath();
		foreach ($this->panelConfigs as $panelConfig) {
			$panelName = $panelConfig->getName();
			
			$panelMag = new ToManyMag($panelName, $panelConfig->getLabel(), $this->targetReadEiState,
					$this->targetEditEiState, $panelConfig->getMin(), $panelConfig->getMax());
			$panelMag->setTargetOrderEiFieldPath($orderEiFieldPath);
			$panelMag->setDraftMode($this->draftMode);
			$panelMag->setNewMappingFormUrl($this->newMappingFormUrl);
			
			if (isset($groupedTargetRelationEntries[$panelName])) {
				$panelMag->setValue($groupedTargetRelationEntries[$panelName]);
			}
			
			$magCollection->addMag($panelMag);
		}
		
		return new MagForm($magCollection);
	}
	
	public function setFormValue($formValue) {
		ArgUtils::assertTrue($formValue instanceof MagForm);
		
		$panelEiFieldPath = ContentItemsEiField::getPanelEiFieldPath();
		$this->targetRelationEntries = array();
		foreach ($this->panelConfigs as $panelConfig) {
			$panelName = $panelConfig->getName();
			$panelMag = $formValue->getMagCollection()->getMagByPropertyName($panelName);
			foreach ($panelMag->getValue() as $targetRelationEntry) {
				$targetRelationEntry->getEiMapping()->setValue($panelEiFieldPath, $panelName, true);
				$this->targetRelationEntries[] = $targetRelationEntry;
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
		foreach ($this->panelConfigs as $panelConfig) {
			if (!$panelConfig->isRestricted()) continue;
			
			$allowedIds = $panelConfig->getAllowedContentItemIds();
		
			$toManyMappingResult = $bindingDefinition->getMappingResult()->__get($this->propertyName)
					->__get($panelConfig->getName());
			foreach ($toManyMappingResult->__get('newMappingForms') as $key => $mfMappingResult) {
				if (in_array($mfMappingResult->entryForm->chosenId, $allowedIds)) continue;
				
				$mfMappingResult->getBindingErrors()->addErrorCode('chosenId', 'ei_impl_content_item_type_disallowed');
			}
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::createUiField()
	 */
	public function createUiField(PropertyPath $propertyPath, HtmlView $view): UiComponent {
		$ciEiSpecLabels = array();
		
		$targetContextEiMask = $this->targetEditEiState->getContextEiMask();
		foreach ($this->targetEditEiState->getContextEiMask()->getEiEngine()->getEiSpec()->getAllSubEiSpecs() as $subEiSpec) {
			if ($subEiSpec->isAbstract()) continue;
			
			$ciEiSpecLabels[$subEiSpec->getId()] = $targetContextEiMask->determineEiMask($subEiSpec)->getLabelLstr()
					->t($view->getN2nLocale());
		}
		
		return $view->getImport('\rocket\spec\ei\component\field\impl\ci\view\contentItemsForm.html',
				array('panelConfigs' => $this->panelConfigs, 'propertyPath' => $propertyPath,
						'ciEiSpecLabels' => $ciEiSpecLabels));
	}
}
