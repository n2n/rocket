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
namespace rocket\ei\util\entry\form;

use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\Dispatchable;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\map\bind\MappingDefinition;
use n2n\impl\web\dispatch\map\val\ValEnum;
use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\annotation\AnnoDispObjectArray;
use n2n\web\dispatch\map\PropertyPathPart;
use n2n\util\type\CastUtils;
use n2n\web\ui\ViewFactory;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\util\entry\view\EiuEntryFormViewModel;
use n2n\l10n\DynamicTextCollection;

class EiuEntryForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('chosenId'));
		$ai->p('eiuEntryTypeForms', new AnnoDispObjectArray());
	}
		
	private $eiuFrame;
	private $contextEiMask;
	
	private $chosenId;
	private $eispecChoosable = false;
	private $eiTypeChoicesMap;
	private $eiuEntryTypeForms;
		
	private $contextPropertyPath = null;
// 	private $selectedTypeId;
// 	private $mainEiuEntryFormPart;
// 	private $levelEiuEntryFormParts = array();
		
	/**
	 * 
	 * @param EiuFrame $eiuFrame
	 */
	public function __construct(EiuFrame $eiuFrame) {
		$this->eiuFrame = $eiuFrame;
	}
	
	/**
	 *
	 * @param PropertyPath $propertyPath
	 * @return \rocket\ei\util\entry\form\EiuEntryForm
	 */
	public function setContextPropertyPath(PropertyPath $propertyPath = null) {
		$this->contextPropertyPath = $propertyPath;
		return $this;
	}
	
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath
	 */
	public function getContextPropertyPath() {
		return $this->contextPropertyPath;
	}
	
	/**
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	public function getEiuFrame() {
		return $this->eiuFrame;
	}
	/**
	 * @return EiuEntryTypeForm[]
	 */
	public function getEiuEntryTypeForms() {
		return $this->eiuEntryTypeForms;
	}
	
	/**
	 * @param EiuEntryTypeForm[] $eiuEntryTypeForms
	 */
	public function setEiuEntryTypeForms(array $eiuEntryTypeForms) {
		$this->eiuEntryTypeForms = $eiuEntryTypeForms; 
		if ($this->chosenId === null) {
			$this->chosenId = key($eiuEntryTypeForms);
		}
	}
	
	public function containsEiTypeId(string $eiTypeId) {
		return isset($this->eiuEntryTypeForms[$eiTypeId]);
	}
	
	public function getChosenId() {
		return $this->chosenId;
	}
	
	public function setChosenId($chosenId) {
		$this->chosenId = $chosenId;
	}
		
	public function setChoosable($eiTypeChoosable) {
		$this->eispecChoosable = (boolean) $eiTypeChoosable;
	}
	
	public function isChoosable() {
		return $this->eispecChoosable;
	}
	
	public function setChoicesMap(array $eiTypeChoicesMap) {
		$this->eiTypeChoicesMap = $eiTypeChoicesMap;
	}
	
	public function getChoicesMap() {
		return $this->eiTypeChoicesMap;
	}
	
	private function _mapping(MappingDefinition $md, DynamicTextCollection $dtc) {
		$md->getMappingResult()->setLabels(['chosenId' => $dtc->t('type_txt')]);
		
		if (!$md->isDispatched()) return;
		
		if (!$this->isChoosable()) {
			$md->ignore('chosenId');
		}
		
// 		$eiTypeId = $md->getDispatchedValue('chosenId');
// 		if ($this->chosenId == $eiTypeId) return;
		
// 		if (isset($this->chooseables[$eiTypeId])) {
// 			$this->initNewEiuEntryFormModel($this->chooseables[$eiTypeId]);
// 		}
	}
	
	private function _validation(BindingDefinition $bd) {
		$bd->val('chosenId', new ValEnum(array_keys($this->eiuEntryTypeForms)));
		
		if (!$this->isChoosable()) return;
		
		$that = $this;
		$bd->closure(function ($eiuEntryTypeForms) use ($bd, $that) {
// 			foreach ($bd->getMappingResult()->eiuEntryTypeForms as $entryModelForm) {
// 				test('hii' . $entryModelForm->getBindingErrors()->isEmpty());
// 			}
			
// 			foreach ($eiuEntryTypeForms as $entryModelForm) {
				
				
// 				test('hi ' . get_class($entryModelForm->getObject()) . ' ' . spl_object_hash($entryModelForm->getBindingErrors()));
// 				test($entryModelForm->getBindingErrors()->isEmpty());
// 			}
			
			$chosenId = $bd->getMappingResult()->chosenId;
			if (!isset($that->eiuEntryTypeForms[$chosenId])) return;
			
			foreach (array_keys($that->eiuEntryTypeForms) as $eiTypeId) {
				if ($chosenId !== $eiTypeId) {
					foreach ($bd->getBindingTree()->lookupAll($bd->getPropertyPath()
							->ext(new PropertyPathPart('eiuEntryTypeForms', true, $eiTypeId))) as $childBd) {
						$childBd->getMappingResult()->getBindingErrors()->removeAllErrors();
					}
				}
			}
		});
	}
	/**
	 * @return EiuEntry
	 * @throws IllegalStateException
	 */
	public function buildEiuEntry() {
		IllegalStateException::assertTrue(isset($this->eiuEntryTypeForms[$this->chosenId]));
		$this->eiuEntryTypeForms[$this->chosenId]->save();
		return $this->eiuEntryTypeForms[$this->chosenId]->getEiuEntryGui()->entry();
	}
	
	/**
	 * @return EiuEntryTypeForm
	 */
	public function getChosenEiuEntryTypeForm() {	
		IllegalStateException::assertTrue(isset($this->eiuEntryTypeForms[$this->chosenId]));
		return $this->eiuEntryTypeForms[$this->chosenId];
	}
	
	public function createView(HtmlView $contextView = null, bool $groupRequired = false, string $displayContainerType = null,
			string $displayContainerLabel = null, array $displayContainerAttrs = null) {
		if ($contextView !== null) {
			return $contextView->getImport('\rocket\ei\util\entry\view\eiuEntryForm.html',
					array('eiuEntryFormViewModel' => new EiuEntryFormViewModel($this, $groupRequired,
							$displayContainerType, $displayContainerLabel, $displayContainerAttrs)));
		}
		
		$viewFactory = $this->eiuFrame->getN2nContext()->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		return $viewFactory->create('rocket\ei\util\entry\view\eiuEntryForm.html',
				array('eiuEntryFormViewModel' => new EiuEntryFormViewModel($this, $groupRequired,
						$displayContainerType, $displayContainerLabel, $displayContainerAttrs)));
	}
}

// class EiuEntryFormResult {
// 	private $validationResult;
// 	private $eiEntry;
	
// 	public function __construct(ValidationResult $validationResult, EiEntry $eiEntry = null) {
// 		$this->validationResult = $validationResult;
// 		$this->eiEntry = $eiEntry;
// 	}
	
// 	public function isValid() {
// 		return $this->validationResult->isValid();
// 	}
	
// 	public function getEiEntry() {
// 		return $this->eiEntry;
// 	}
// }


// class EiuEntryForm implements EditEntryModel, Dispatchable {
// 	private static function _annos(AnnoInit $ai) {
// 		$ai->p('magForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
// 		$ai->p('subMagForms', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY);
// 	}
	
// 	private $eiFrame;
// 	private $eiObject;
// 	private $readOnly;
	
// 	private $eiType;
// 	private $subs;
	
// 	private $visibleEiProps = array();
// 	private $subVisibleEiProps = array();
	
// 	protected $selectedTypeId;
// 	protected $MagForm;
// 	protected $subMagForms = array();
// 	/**
// 	 * @param EiFrame $eiFrame 
// 	 * @param EiType $eiType The Script of the Entity which ....
// 	 * @param EiObject $eiObject
// 	 * @param string $readOnly
// 	 */
// 	public function __construct(EiFrame $eiFrame,  $eiType, EiObject $eiObject = null, $readOnly = false) {
// 		$this->eiFrame = $eiFrame;
// 		$this->eiType = $eiType;
// 		$this->eiObject = $eiObject;
// 		$this->readOnly = $readOnly;
// 		$this->selectedTypeId = $eiType->getId();
// 		$this->eiTypes[$this->selectedTypeId] = $eiType;
				
// 		$this->MagForm = new MagForm($this->createMagCollection($eiType, false), new Attributes());
		
// 		if ($this->isNew()) {
// 			$subs = $eiType->getAllSubs();
			
// 			if (sizeof($subs) || $this->eiType->getEntityModel()->isAbstract()) {
// 				$this->subs = array();
			
// 				foreach ($subs as $id => $sub) {
// 					if ($sub->getEntityModel()->isAbstract()) continue;
// 					$this->subs[$id] = $sub;
// 					$this->subMagForms[$id] = new MagForm($this->createMagCollection($sub, true), new Attributes());
// 				}
// 			}
// 		} else {
// 			$object = $eiObject->getEntityObj();
// 			$entityModel = EntityModelManager::getInstance()->getEntityModelByObject($object);
				
// 			$subs = $eiType->getAllSubs();

// 			if (!$eiObject->isDraft() && !$eiObject->hasTranslation() 
// 					&& (sizeof($subs) || $this->eiType->getEntityModel()->isAbstract())) {
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
				
// 			$this->readFromObject($eiObject->getEntityObj());
// 		}
// 	}
	
// 	private function createMagCollection(EiType $eiType, $levelOnly) {
// 		$eiTypeId = $eiType->getId();
// 		if ($levelOnly && !isset($this->subVisibleEiProps[$eiTypeId])) {
// 			$this->subVisibleEiProps[$eiTypeId] = array();
// 		}
		
// 		$magCollection = new MagCollection();
// 		foreach ($eiType->getEiPropCollection()->toArray() as $eiPropId => $eiProp) {
// 			if (!($eiProp instanceof Displayable) || !$eiProp->isDisplayInEditViewEnabled()) continue;

// 			if (!$levelOnly) {
// 				$this->visibleEiProps[$eiPropId] = $eiProp;
// 			}
			
// 			if (!($eiProp instanceof Editable) || $eiProp->isReadOnly() || $this->readOnly
// 					|| (isset($this->eiObject) && !$this->eiObject->isWritingAllowed($eiProp))) continue;
			
// 			if ($levelOnly) {
// 				if ($this->MagForm->containsPropertyName($eiProp->getPropertyName())) continue;
// 				$this->subVisibleEiProps[$eiTypeId][$eiPropId] = $eiProp;
// 			}
			
// 			if (isset($this->eiObject)) {
// 				if ($this->eiObject->isDraft() && !($eiProp instanceof DraftableEiProp)) {
// 					continue;
// 				}
			
// 				if ($this->eiObject->hasTranslation() && !($eiProp instanceof TranslatableEiProp
// 						&& $eiProp->isTranslationEnabled())) {
// 					continue;
// 				}
// 			}
				
// 			$magCollection->addMag($eiProp->getPropertyName(), 
// 					$eiProp->createOption($this->eiFrame, $this->eiObject));
// 		}
		
// 		return $magCollection;
// 	}
	
// 	private function readFromObject(Entity $object) {
// 		$selected = $this->getSelected();
// 		$selectedId = $selected->getId();
// 		if (isset($this->subMagForms[$selectedId])) {
// 			$this->readProperties($selected, $this->subMagForms[$selectedId], $object);
// 		}
		
// 		$this->readProperties($this->eiType, $this->MagForm, $object);
// 	}
	
// 	private function readProperties(EiType $eiType, MagDispatchable $MagForm, Entity $object) {
// 		foreach ($eiType->getEiPropCollection()->toArray() as $eiProp) {
// 			if (!($eiProp instanceof Editable)
// 				|| !$MagForm->containsPropertyName($eiProp->getPropertyName())) continue;
				
// 			$propertyName = $eiProp->getPropertyName();
// 			$accessProxy = $eiProp->getPropertyAccessProxy();
			
// 			$MagForm->setAttributeValue($propertyName,
// 					$eiProp->propertyValueToOptionAttributeValue(
// 							$accessProxy->getValue($object), $this->eiFrame, $this->eiObject));
// 		}
// 	}
	
// 	public function writeToObject(Entity $object) {
// 		$selected = $this->getSelected();
// 		$selectedId = $selected->getId();
// 		if (isset($this->subMagForms[$selectedId])) {
// 			$this->writeProperties($selected, $this->subMagForms[$selectedId], $object);
// 		}
		
// 		$this->writeProperties($this->eiType, $this->MagForm, $object);
// 	}
	
// 	public function writeProperties(EiType $eiType, MagDispatchable $MagForm, Entity $object) {
// 		foreach ($eiType->getEiPropCollection()->toArray() as $eiProp) {
// 			if (!($eiProp instanceof Editable)) continue;
						
// 			$propertyName = $eiProp->getPropertyName();
// 			if (!$MagForm->containsPropertyName($propertyName)) continue;
				
// 			$accessProxy = $eiProp->getPropertyAccessProxy();
// 			$accessProxy->setValue($object, $eiProp->optionAttributeValueToPropertyValue(
// 					$MagForm->getAttributeValue($propertyName), $MagForm->getAttributes(),
// 					$object, $this->eiFrame, $this->eiObject));
// 		}
// 	}
	
// 	public function getEiFrame() {
// 		return $this->eiFrame;
// 	}
	
// 	public function getEiType() {
// 		return $this->eiType;
// 	}
	
// 	public function getEiObject() {
// 		return $this->eiObject;
// 	}
	
// 	public function createPropertyPath($propertyName, PropertyPath $basePropertyPath = null) {
// 		if (isset($basePropertyPath)) {
// 			return $basePropertyPath->createExtendedPath(array('magForm', $propertyName));
// 		}
		
// 		return PropertyPath::createFromPropertyExpressionArray(array('magForm', $propertyName));
// 	}
	
// 	public function isNew() {
// 		return !isset($this->eiObject);
// 	}
	
// 	public function getVisibleEiProps() {
// 		return $this->visibleEiProps;
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
		
// 		return $this->eiType;
// 	}
	
// 	public function isTypeSelectionAvailable() {
// 		return isset($this->subs);
// 	}
	
// 	public function getSelectedTypeOptions() {
// 		$options = array();
		
// 		if (!$this->eiType->getEntityModel()->isAbstract()) {
// 			$options[$this->eiType->getId()] = $this->eiType->getLabel();
// 		}
		
// 		foreach ($this->subs as $id => $sub) {
// 			$options[$id] = $sub->getLabel();
// 		}

// 		return $options;
// 	}
	
// 	public function getSubIds() {
// 		return array_keys($this->subVisibleEiProps);
// 	}
	
// 	public function getSubVisibleEiProps($scriptId) {
// 		if (isset($this->subVisibleEiProps[$scriptId])) {
// 			return $this->subVisibleEiProps[$scriptId]; 
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
