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
namespace rocket\spec\ei\manage\util\model;

use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\Dispatchable;
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\map\bind\MappingDefinition;
use n2n\impl\web\dispatch\map\val\ValEnum;
use rocket\spec\ei\manage\model\EntryModel;
use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\annotation\AnnoDispObjectArray;
use n2n\web\dispatch\map\PropertyPathPart;
use rocket\spec\ei\manage\EiFrame;

class EntryForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('chosenId'));
		$ai->p('entryModelForms', new AnnoDispObjectArray());
	}
		
	private $eiFrame;
	private $contextEiMask;
	private $eiFrameUtils;
	
	private $chosenId;
	private $eispecChoosable = false;
	private $eiSpecChoicesMap;
	private $entryModelForms;
		
// 	private $selectedTypeId;
// 	private $mainEntryFormPart;
// 	private $levelEntryFormParts = array();
		
	
	public function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
	}
	
	public function getEiFrame(): EiFrame {
		return $this->eiFrame;
	}
	/**
	 * @return EditEntryModelForm
	 */
	public function getEntryModelForms() {
		return $this->entryModelForms;
	}
	
	public function setEntryModelForms(array $entryModelForms) {
		$this->entryModelForms = $entryModelForms; 
		if ($this->chosenId === null) {
			$this->chosenId = key($entryModelForms);
		}
	}
	
	public function getChosenId() {
		return $this->chosenId;
	}
	
	public function setChosenId($chosenId) {
		$this->chosenId = $chosenId;
	}
		
	public function setChoosable($eiSpecChoosable) {
		$this->eispecChoosable = (boolean) $eiSpecChoosable;
	}
	
	public function isChoosable() {
		return $this->eispecChoosable;
	}
	
	public function setChoicesMap(array $eiSpecChoicesMap) {
		$this->eiSpecChoicesMap = $eiSpecChoicesMap;
	}
	
	public function getChoicesMap() {
		return $this->eiSpecChoicesMap;
	}
	
	private function _mapping(MappingDefinition $md) {
		if (!$md->isDispatched()) return;
		
		if (!$this->isChoosable()) {
			$md->ignore('chosenId');
		}
		
// 		$eiSpecId = $md->getDispatchedValue('chosenId');
// 		if ($this->chosenId == $eiSpecId) return;
		
// 		if (isset($this->chooseables[$eiSpecId])) {
// 			$this->initNewEntryFormModel($this->chooseables[$eiSpecId]);
// 		}
	}
	
	private function _validation(BindingDefinition $bd) {
		$bd->val('chosenId', new ValEnum(array_keys($this->entryModelForms)));
		
		if (!$this->isChoosable()) return;
		
		$that = $this;
		$bd->closure(function ($entryModelForms) use ($bd, $that) {
// 			foreach ($bd->getMappingResult()->entryModelForms as $entryModelForm) {
// 				test('hii' . $entryModelForm->getBindingErrors()->isEmpty());
// 			}
			
// 			foreach ($entryModelForms as $entryModelForm) {
				
				
// 				test('hi ' . get_class($entryModelForm->getObject()) . ' ' . spl_object_hash($entryModelForm->getBindingErrors()));
// 				test($entryModelForm->getBindingErrors()->isEmpty());
// 			}
			
			$chosenId = $bd->getMappingResult()->chosenId;
			if (!isset($that->entryModelForms[$chosenId])) return;
			
			foreach (array_keys($that->entryModelForms) as $eiSpecId) {
				if ($chosenId !== $eiSpecId) {
					foreach ($bd->getBindingTree()->lookupAll($bd->getPropertyPath()
							->ext(new PropertyPathPart('entryModelForms', true, $eiSpecId))) as $childBd) {
						$childBd->getMappingResult()->getBindingErrors()->removeAllErrors();
					}
				}
			}
		});
	}
	/**
	 * @return EiMapping
	 * @throws IllegalStateException
	 */
	public function buildEiMapping() {
		IllegalStateException::assertTrue(isset($this->entryModelForms[$this->chosenId]));
		$this->entryModelForms[$this->chosenId]->save();
		return $this->entryModelForms[$this->chosenId]->getEiuEntryGui()->getEiuEntry()->getEiMapping();
	}
	
	/**
	 * @return EntryModel
	 */
	public function getChosenEntryModelForm() {	
		IllegalStateException::assertTrue(isset($this->entryModelForms[$this->chosenId]));
		return $this->entryModelForms[$this->chosenId];
	}
}

// class EntryFormResult {
// 	private $validationResult;
// 	private $eiMapping;
	
// 	public function __construct(ValidationResult $validationResult, EiMapping $eiMapping = null) {
// 		$this->validationResult = $validationResult;
// 		$this->eiMapping = $eiMapping;
// 	}
	
// 	public function isValid() {
// 		return $this->validationResult->isValid();
// 	}
	
// 	public function getEiMapping() {
// 		return $this->eiMapping;
// 	}
// }


// class EntryForm implements EditEntryModel, Dispatchable {
// 	private static function _annos(AnnoInit $ai) {
// 		$ai->p('magForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
// 		$ai->p('subMagForms', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY);
// 	}
	
// 	private $eiFrame;
// 	private $eiEntry;
// 	private $readOnly;
	
// 	private $eiSpec;
// 	private $subs;
	
// 	private $visibleEiFields = array();
// 	private $subVisibleEiFields = array();
	
// 	protected $selectedTypeId;
// 	protected $MagForm;
// 	protected $subMagForms = array();
// 	/**
// 	 * @param EiFrame $eiFrame 
// 	 * @param EiSpec $eiSpec The Script of the Entity which ....
// 	 * @param EiEntry $eiEntry
// 	 * @param string $readOnly
// 	 */
// 	public function __construct(EiFrame $eiFrame,  $eiSpec, EiEntry $eiEntry = null, $readOnly = false) {
// 		$this->eiFrame = $eiFrame;
// 		$this->eiSpec = $eiSpec;
// 		$this->eiEntry = $eiEntry;
// 		$this->readOnly = $readOnly;
// 		$this->selectedTypeId = $eiSpec->getId();
// 		$this->eiSpecs[$this->selectedTypeId] = $eiSpec;
				
// 		$this->MagForm = new MagForm($this->createMagCollection($eiSpec, false), new Attributes());
		
// 		if ($this->isNew()) {
// 			$subs = $eiSpec->getAllSubs();
			
// 			if (sizeof($subs) || $this->eiSpec->getEntityModel()->isAbstract()) {
// 				$this->subs = array();
			
// 				foreach ($subs as $id => $sub) {
// 					if ($sub->getEntityModel()->isAbstract()) continue;
// 					$this->subs[$id] = $sub;
// 					$this->subMagForms[$id] = new MagForm($this->createMagCollection($sub, true), new Attributes());
// 				}
// 			}
// 		} else {
// 			$object = $eiEntry->getEntityObj();
// 			$entityModel = EntityModelManager::getInstance()->getEntityModelByObject($object);
				
// 			$subs = $eiSpec->getAllSubs();

// 			if (!$eiEntry->isDraft() && !$eiEntry->hasTranslation() 
// 					&& (sizeof($subs) || $this->eiSpec->getEntityModel()->isAbstract())) {
// 				$this->subs = array();
// 			}
			
// 			foreach ($subs as $id => $sub) {
// 				$subEntityModel = $sub->getEntityModel();
// 				if ($subEntityModel->isAbstract()) continue;
			
// 				if ($entityModel->equals($subEntityModel)) {
// 					$this->selectedTypeId = $sub->getId();
// 				}
				
// 				if (isset($this->subs)) { 
// 					$this->subs[$id] = $sub;
// 					$this->subMagForms[$id] = new MagForm($this->createMagCollection($sub, true), new Attributes());
// 				}
// 			}
				
// 			$this->readFromObject($eiEntry->getEntityObj());
// 		}
// 	}
	
// 	private function createMagCollection(EiSpec $eiSpec, $levelOnly) {
// 		$eiSpecId = $eiSpec->getId();
// 		if ($levelOnly && !isset($this->subVisibleEiFields[$eiSpecId])) {
// 			$this->subVisibleEiFields[$eiSpecId] = array();
// 		}
		
// 		$magCollection = new MagCollection();
// 		foreach ($eiSpec->getEiFieldCollection()->toArray() as $eiFieldId => $eiField) {
// 			if (!($eiField instanceof Displayable) || !$eiField->isDisplayInEditViewEnabled()) continue;

// 			if (!$levelOnly) {
// 				$this->visibleEiFields[$eiFieldId] = $eiField;
// 			}
			
// 			if (!($eiField instanceof Editable) || $eiField->isReadOnly() || $this->readOnly
// 					|| (isset($this->eiEntry) && !$this->eiEntry->isWritingAllowed($eiField))) continue;
			
// 			if ($levelOnly) {
// 				if ($this->MagForm->containsPropertyName($eiField->getPropertyName())) continue;
// 				$this->subVisibleEiFields[$eiSpecId][$eiFieldId] = $eiField;
// 			}
			
// 			if (isset($this->eiEntry)) {
// 				if ($this->eiEntry->isDraft() && !($eiField instanceof DraftableEiField)) {
// 					continue;
// 				}
			
// 				if ($this->eiEntry->hasTranslation() && !($eiField instanceof TranslatableEiField
// 						&& $eiField->isTranslationEnabled())) {
// 					continue;
// 				}
// 			}
				
// 			$magCollection->addMag($eiField->getPropertyName(), 
// 					$eiField->createOption($this->eiFrame, $this->eiEntry));
// 		}
		
// 		return $magCollection;
// 	}
	
// 	private function readFromObject(Entity $object) {
// 		$selected = $this->getSelected();
// 		$selectedId = $selected->getId();
// 		if (isset($this->subMagForms[$selectedId])) {
// 			$this->readProperties($selected, $this->subMagForms[$selectedId], $object);
// 		}
		
// 		$this->readProperties($this->eiSpec, $this->MagForm, $object);
// 	}
	
// 	private function readProperties(EiSpec $eiSpec, MagDispatchable $MagForm, Entity $object) {
// 		foreach ($eiSpec->getEiFieldCollection()->toArray() as $eiField) {
// 			if (!($eiField instanceof Editable)
// 				|| !$MagForm->containsPropertyName($eiField->getPropertyName())) continue;
				
// 			$propertyName = $eiField->getPropertyName();
// 			$accessProxy = $eiField->getPropertyAccessProxy();
			
// 			$MagForm->setAttributeValue($propertyName,
// 					$eiField->propertyValueToOptionAttributeValue(
// 							$accessProxy->getValue($object), $this->eiFrame, $this->eiEntry));
// 		}
// 	}
	
// 	public function writeToObject(Entity $object) {
// 		$selected = $this->getSelected();
// 		$selectedId = $selected->getId();
// 		if (isset($this->subMagForms[$selectedId])) {
// 			$this->writeProperties($selected, $this->subMagForms[$selectedId], $object);
// 		}
		
// 		$this->writeProperties($this->eiSpec, $this->MagForm, $object);
// 	}
	
// 	public function writeProperties(EiSpec $eiSpec, MagDispatchable $MagForm, Entity $object) {
// 		foreach ($eiSpec->getEiFieldCollection()->toArray() as $eiField) {
// 			if (!($eiField instanceof Editable)) continue;
						
// 			$propertyName = $eiField->getPropertyName();
// 			if (!$MagForm->containsPropertyName($propertyName)) continue;
				
// 			$accessProxy = $eiField->getPropertyAccessProxy();
// 			$accessProxy->setValue($object, $eiField->optionAttributeValueToPropertyValue(
// 					$MagForm->getAttributeValue($propertyName), $MagForm->getAttributes(),
// 					$object, $this->eiFrame, $this->eiEntry));
// 		}
// 	}
	
// 	public function getEiFrame() {
// 		return $this->eiFrame;
// 	}
	
// 	public function getEiSpec() {
// 		return $this->eiSpec;
// 	}
	
// 	public function getEiEntry() {
// 		return $this->eiEntry;
// 	}
	
// 	public function createPropertyPath($propertyName, PropertyPath $basePropertyPath = null) {
// 		if (isset($basePropertyPath)) {
// 			return $basePropertyPath->createExtendedPath(array('magForm', $propertyName));
// 		}
		
// 		return PropertyPath::createFromPropertyExpressionArray(array('magForm', $propertyName));
// 	}
	
// 	public function isNew() {
// 		return !isset($this->eiEntry);
// 	}
	
// 	public function getVisibleEiFields() {
// 		return $this->visibleEiFields;
// 	}
	
// 	public function containsPropertyName($propertyName) {
// 		return $this->MagForm->containsPropertyName($propertyName);
// 	}
	
// 	public function getPropertyValueByName($name) {
// 		return $this->MagForm->getPropertyValue($name);
// 	}
// 	/**
// 	 * @return 
// 	 */
// 	public function getSelected() {
// 		if (isset($this->subs[$this->selectedTypeId])) {
// 			return $this->subs[$this->selectedTypeId];
// 		}
		
// 		return $this->eiSpec;
// 	}
	
// 	public function isTypeSelectionAvailable() {
// 		return isset($this->subs);
// 	}
	
// 	public function getSelectedTypeOptions() {
// 		$options = array();
		
// 		if (!$this->eiSpec->getEntityModel()->isAbstract()) {
// 			$options[$this->eiSpec->getId()] = $this->eiSpec->getLabel();
// 		}
		
// 		foreach ($this->subs as $id => $sub) {
// 			$options[$id] = $sub->getLabel();
// 		}

// 		return $options;
// 	}
	
// 	public function getSubIds() {
// 		return array_keys($this->subVisibleEiFields);
// 	}
	
// 	public function getSubVisibleEiFields($scriptId) {
// 		if (isset($this->subVisibleEiFields[$scriptId])) {
// 			return $this->subVisibleEiFields[$scriptId]; 
// 		}
		
// 		throw IllegalStateException::createDefault(); 
// 	}
	
// 	public function containsSubPropertyName($scriptId, $propertyName) {
// 		if (isset($this->subMagForms[$scriptId])) {
// 			return $this->subMagForms[$scriptId]->containsPropertyName($propertyName);
// 		}
		
// 		throw IllegalStateException::createDefault();
// 	}
	
// 	public function createSubPropertyPath($scriptId, $propertyName, PropertyPath $basePropertyPath = null) {
// 		if (isset($basePropertyPath)) {
// 			return $basePropertyPath->createExtendedPath(
// 					array(new PropertyPathPart('subMagForms', true, $scriptId), $propertyName));
// 		}
		
// 		return PropertyPath::createFromPropertyExpressionArray(
// 				array(new PropertyPathPart('subMagForms', true, $scriptId), $propertyName));
// 	}
	
// 	public function getSelectedTypeId() {
// 		return $this->selectedTypeId;
// 	}
	
// 	public function setSelectedTypeId($selectedTypeId) {
// 		$this->selectedTypeId = $selectedTypeId;
// 	}
	
// 	public function getMagForm() {
// 		return $this->MagForm;
// 	}
	
// 	public function setMagForm(MagForm $MagForm) {
// 		$this->MagForm = $MagForm;
// 	}
	
// 	public function getSubMagForms() {
// 		return $this->subMagForms;
// 	}
	
// 	public function setSubMagForms(array $subMagForms) {
// 		$this->subMagForms = $subMagForms;
// 	}
	
// 	private function _validation(BindingConstraints $bc) {
// 		if (!$this->isTypeSelectionAvailable()) return;
// 		$bc->val('selectedTypeId', new ValEnum(array_keys($this->getSelectedTypeOptions())));
		
// 		$selectedTypeId = $bc->getRawValue('selectedTypeId');
// 		foreach ($this->subMagForms as $key => $subMagDispatchable) {
// 			if ($key == $selectedTypeId) continue;
		
// 			$bc->ignore('subMagForms', $key);
// 		}
// 	}
// }
