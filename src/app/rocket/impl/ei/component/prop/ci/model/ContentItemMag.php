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
namespace rocket\impl\ei\component\prop\ci\model;

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
use n2n\web\dispatch\mag\MagCollection;
use rocket\impl\ei\component\prop\relation\model\mag\ToManyMag;
use n2n\impl\web\dispatch\mag\model\MagForm;
use rocket\impl\ei\component\prop\ci\ContentItemsEiProp;
use n2n\web\dispatch\mag\UiOutfitter;
use rocket\ei\util\Eiu;

class ContentItemMag extends MagAdapter {
	private $panelConfigs = array();
	private $targetReadEiFrame;
	private $targetEditEiFrame;
	
	private $draftMode = false;
	private $reduced = true;
	private $newMappingFormUrl;
	
	private $targetRelationEntries = array();
	
	/**
	 * @param string $propertyName
	 * @param string $label
	 * @param PanelConfig[] $panelConfigs
	 * @param EiFrame $targetReadEiFrame
	 * @param EiFrame $targetEditEiFrame
	 */
	public function __construct(string $label, array $panelConfigs, 
			EiFrame $targetReadEiFrame, EiFrame $targetEditEiFrame) {
		parent::__construct($label);
	
		$this->panelConfigs = $panelConfigs;
		$this->targetReadEiFrame = $targetReadEiFrame;
		$this->targetEditEiFrame = $targetEditEiFrame;
	}

	public function setDraftMode(bool $draftMode) {
		$this->draftMode = $draftMode;
	}	
	
	public function setReduced(bool $reduced) {
		$this->reduced = $reduced;
	}

	public function setNewMappingFormUrl(Url $newMappingFormUrl = null) {
		$this->newMappingFormUrl = $newMappingFormUrl;
	}
	
	public function setValue($targetRelationEntries) {
		ArgUtils::valArray($targetRelationEntries, RelationEntry::class);
		
		$this->targetRelationEntries = $targetRelationEntries;
	}
	
	private function groupRelationEntries(array $targetRelationEntries) {
		$targetEiuFrame = (new Eiu($this->targetEditEiFrame))->frame();
		$panelEiPropPath = ContentItemsEiProp::getPanelEiPropPath();
		$filtered = array();
		foreach ($targetRelationEntries as $targetRelationEntry) {
			if (!$targetRelationEntry->hasEiEntry()) {
				$targetRelationEntry = RelationEntry::fromM($targetEiuFrame
						->entry($targetRelationEntry->getEiObject())->getEiEntry(true));
			}
			
			$panelName = $targetRelationEntry->getEiEntry()->getValue($panelEiPropPath);
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
		
		$orderEiPropPath = ContentItemsEiProp::getOrderIndexEiPropPath();
		foreach ($this->panelConfigs as $panelConfig) {
			$panelName = $panelConfig->getName();
			
			$panelMag = new ToManyMag($panelConfig->getLabel(), $this->targetReadEiFrame,
					$this->targetEditEiFrame, $panelConfig->getMin(), $panelConfig->getMax());
			$panelMag->setTargetOrderEiPropPath($orderEiPropPath);
			$panelMag->setDraftMode($this->draftMode);
			$panelMag->setReduced($this->reduced);
			
			$allowedEiTypeIds = $panelConfig->isRestricted() ? $panelConfig->getAllowedContentItemIds() : null;
			$panelMag->setNewMappingFormUrl($this->newMappingFormUrl->queryExt(array('chooseableEiTypeIds' => $allowedEiTypeIds)));
			$panelMag->setAllowedNewEiTypeIds($allowedEiTypeIds);
			
			
			if (isset($groupedTargetRelationEntries[$panelName])) {
				$panelMag->setValue($groupedTargetRelationEntries[$panelName]);
			}
			
			$magCollection->addMag($panelName, $panelMag);
		}
		
		return new MagForm($magCollection);
	}
	
	public function setFormValue($formValue) {
		ArgUtils::assertTrue($formValue instanceof MagForm);
		
		$panelEiPropPath = ContentItemsEiProp::getPanelEiPropPath();
		$this->targetRelationEntries = array();
		foreach ($this->panelConfigs as $panelConfig) {
			$panelName = $panelConfig->getName();
			$panelMag = $formValue->getMagCollection()->getMagByPropertyName($panelName);
			foreach ($panelMag->getValue() as $targetRelationEntry) {
				$targetRelationEntry->getEiEntry()->setValue($panelEiPropPath, $panelName, true);
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
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::createUiField()
	 */
	public function createUiField(PropertyPath $propertyPath, HtmlView $view, UiOutfitter $uiOutfitter): UiComponent {
		$ciEiTypeLabels = array();
		
		$targetContextEiMask = $this->targetEditEiFrame->getContextEiEngine()->getEiMask();
		foreach ($this->targetEditEiFrame->getContextEiEngine()->getEiMask()->getEiType()->getAllSubEiTypes() as $subEiType) {
			if ($subEiType->isAbstract()) continue;
			
			$ciEiTypeLabels[$subEiType->getId()] = $this->targetEditEiFrame->determineEiMask($subEiType)->getLabelLstr()
					->t($view->getN2nLocale());
		}
		
		return $view->getImport('\rocket\impl\ei\component\prop\ci\view\contentItemsForm.html',
				array('panelLayout' => new PanelLayout($this->panelConfigs), 'propertyPath' => $propertyPath,
						'ciEiTypeLabels' => $ciEiTypeLabels));
	}
}
