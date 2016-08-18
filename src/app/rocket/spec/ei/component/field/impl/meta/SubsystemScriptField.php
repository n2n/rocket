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
namespace rocket\spec\ei\component\field\impl\meta;

use n2n\web\ui\view\impl\html\HtmlView;
use n2n\persistence\orm\property\impl\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\component\field\impl\TranslatableEiFieldAdapter;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\core\N2N;
use n2n\util\config\Attributes;
use n2n\persistence\orm\NestedSetUtils;
use rocket\spec\ei\component\field\impl\string\PathPartEiField;
use n2n\l10n\DynamicTextCollection;
use n2n\web\dispatch\mag\impl\model\EnumMag;
use n2n\l10n\N2nLocale;
use n2n\core\container\N2nContext;
use n2n\persistence\orm\OrmUtils;
use n2n\web\dispatch\mag\MagCollection;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\SimpleSelectorConstraint;
use rocket\spec\ei\manage\gui\Editable;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use rocket\spec\ei\component\command\impl\tree\field\TreeRootIdEiField;
use rocket\spec\ei\component\command\impl\tree\field\TreeLeftEiField;
use rocket\spec\ei\component\command\impl\tree\field\TreeRightEiField;

// class SubsystemEiField extends TranslatableEiFieldAdapter {
	
// 	private $subsystems;
// 	private $scriptManager;
	
// 	public function setEntityProperty(EntityProperty $entityProperty = null) {
// 		ArgUtils::assertTrue($entityProperty instanceof N2nLocaleEntityProperty);
// 		$this->entityProperty = $entityProperty;
// 	}
	
// 	public function setPropertyAccessProxy(PropertyAccessProxy $propertyAccessProxy) {
// 		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('n2n\\l10n\\N2nLocale',
// 				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
// 		$this->propertyAccessProxy = $propertyAccessProxy;
// 	}
	
// 	public function setup(EiSetupProcess $setupProcess) {
// 		$this->scriptManager = $setupProcess->getSpecManager();
// 		$subsystemConfigs = N2N::getAppConfig()->http()->getSubystemConfigs();
// 		$this->subsystems = (count($subsystemConfigs) > 0) ? 
// 				array(null => 'Alle Subsysteme') : array();
		
// 		foreach ($subsystemConfigs as $subsystemConfig) {
// 			$displayName = $subsystemConfig->getName();
// 			$displayName .= ' (' .$subsystemConfig->getHostName();
// 			if (null !== $contextPath = $subsystemConfig->getContextPath()) {
// 				$displayName .= '/' . $subsystemConfig->getContextPath();
// 			}
// 			$displayName .= ')';
// 			$this->subsystems[$subsystemConfig->getName()] = $displayName;
// 		}
// 	}
	
// 	public function getTypeName(): string {
// 		return 'Subsystem';
// 	}
	
// 	public function isMandatory(EntrySourceInfo $entrySourceInfo) {
// 		if (empty($this->subsystems)) return false;
// 		return parent::isMandatory($eiMapping, $entrySourceInfo);
// 	}
	
// 	public function isCompatibleWith(EntityProperty $entityProperty) {
// 		return $entityProperty instanceof ScalarEntityProperty;
// 	}
	
// 	public function isDisplayInAddViewEnabled() {
// 		if (empty($this->subsystems)) return false;
// 		return parent::isDisplayInAddViewEnabled();
// 	}
	
// 	public function isDisplayInEditViewEnabled() {
// 		if (empty($this->subsystems)) return false;
// 		return parent::isDisplayInEditViewEnabled();
// 	}
	
// 	public function isDisplayInDetailViewEnabled() {
// 		if (empty($this->subsystems)) return false;
// 		return parent::isDisplayInDetailViewEnabled();
// 	}
	
// 	public function isDisplayInListViewEnabled() {
// 		if (empty($this->subsystems)) return false;
// 		return parent::isDisplayInListViewEnabled();
// 	}
	
// 	public function createOutputUiComponent(
// 			HtmlView $view, EntrySourceInfo $entrySourceInfo) {
// 		$html = $view->getHtmlBuilder();
// 		$subsystemName = $this->read($eiMapping->getEiSelection()->getEntityObj());
// 		if ($entrySourceInfo->hasListModel() || !isset($this->subsystems[$subsystemName])) {
// 			return $html->getEsc($subsystemName);
// 		}
// 		return $html->getEsc($this->subsystems[$subsystemName]);
// 	}
	
// 	public function optionAttributeValueToPropertyValue(Attributes $attributes,
// 			EiMapping $eiMapping, EntrySourceInfo $entrySourceInfo) {

// 		$newValue = $attributes->get($this->id);
// 		$oldValue = $eiMapping->getValue(EiFieldPath::from($this));
	
// 		$eiMapping->setValue($this->id, $attributes->get($this->id));
// 		$dependantPathPartEiFields = $this->determineDependantPathPartEiFields();
// 		$eiSelection = $eiMapping->getEiSelection();

// 		if (count($dependantPathPartEiFields) === 0 || $eiSelection->isNew() 
// 				|| $oldValue === $newValue) return;
		
// 		if ($this->isTreeScript()) {
// 			//set Subsystem for Childelements and 
// 			$accessProxy = $this->getEntityProperty()->getAccessProxy();
// 			$currentEntity = $eiSelection->getCurrentEntity();
// 			$top = $this->getEiSpec()->getSupremeEiSpec();
			
// 			$em = $entrySourceInfo->getEiState()->getEntityManager();
// 			$nestedSetUtils = new NestedSetUtils($em, $this->getEiSpec()->getEntityModel()->getClass());
// 			foreach ($nestedSetUtils->fetch($currentEntity, true) as $nsItem) {
// 				$object = $nsItem->getObject();
// 				if ($accessProxy->getValue($object) !== $oldValue) {
// 					continue;
// 				}
// 				$accessProxy->setValue($object, $newValue);
// 				foreach ($dependantPathPartEiFields as $eiField) {
// 					$urlPartAccessProxy = $eiField->getPropertyAccessProxy();
// 					$oldUrlPartValue = $urlPartAccessProxy->getValue($object);
// 					$objAttributes = $this->createAttributesForObject($object);
// 					$objAttributes->set($this->getPropertyName(), $newValue);
// 					$newUrlpartValue = $eiField->determineUrlPart($em, $oldUrlPartValue, $oldUrlPartValue, $objAttributes, 
// 							new EiSelection(OrmUtils::extractId($object), $object));
// 					$eiField->getPropertyAccessProxy()->setValue($object, $newUrlpartValue);
// 				}
// 				$em->flush();
// 			}
// 		}
// 	}
	
// 	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
// 		$attrs = array();
// 		if (!$eiMapping->getEiSelection()->isNew()) {
// 			$attrs['class'] = 'rocket-critical-input';
// 			$dtc = new DynamicTextCollection(array('page', 'rocket'));
// 			$attrs['data-confirm-message'] = $dtc->translate('spec_field_subsystem_unlock_confirm_message');
// 			$attrs['data-edit-label'] =  $dtc->translate('common_edit_label');
// 			$attrs['data-cancel-label'] =  $dtc->translate('common_cancel_label');
// 		} else {
// 			$eiState = $entrySourceInfo->getEiState();
// 			$cmds = $eiState->getControllerContext()->getCmds();
// 			if (null !== ($entity = $eiState->getEntityManager()->find(
// 					$this->getEiSpec()->getEntityModel()->getClass(), end($cmds)))) {
// 				$eiMapping->setValue($this->getId(), $this->getPropertyAccessProxy()->getValue($entity));
// 			}
// 		}
// 		return new EnumMag($propertyName, $this->getLabel(), $this->subsystems, null, 
// 				$this->isMandatory($entrySourceInfo), $attrs);
// 	}
	
// 	public function createRestrictionMagCollection(N2nLocale $n2nLocale, N2nContext $n2nContext) {
// 		$magCollection = new MagCollection();
// 		$magCollection->addMag('restrictedSubsystemName', new EnumMag('SubsystemName', 
// 				$this->subsystems));
// 		return $magCollection;
// 	}
	
// 	public function createRestrictionSelectionConstraint(Attributes $restrictionAttributes, EiState $eiState) {
// 		$restrictedSubsystemName = $restrictionAttributes->get('restrictedSubsystemName');
// 		if ($restrictedSubsystemName === null) return null;
	
// 		$targetEntityModel = $this->target->getEntityModel();
// 		return new SimpleSelectorConstraint($this->getEntityProperty(),
// 				$eiState->getEntityManager()->find(
// 						$targetEntityModel->getClass(), $restrictedSubsystemName),
// 				function ($value1, $value2) use ($targetEntityModel) {
// 					return OrmUtils::areObjectsEqual($value1, $value2, $targetEntityModel);
// 				});
// 	}
	
// 	private function createAttributesForObject($object) {
// 		$attributes = new Attributes();
// 		foreach ($this->getEiSpec()->getSupremeEiSpec()->getEiFieldCollection() as $eiField) {
// 			if ($eiField instanceof Editable) {
// 				$accessProxy = $eiField->getPropertyAccessProxy();
// 				$attributes->set($eiField->getPropertyName(), $accessProxy->getValue($object));
// 			} 
// 		}
// 		return $attributes;
// 	}
	
// 	private function isTreeScript() {
// 		foreach ($this->getEiSpec()->getEiFieldCollection() as $field) {
// 			if ($field instanceof TreeRootIdEiField) return true;
// 			if ($field instanceof TreeLeftEiField) return true;
// 			if ($field instanceof TreeRightEiField) return true;
// 		}
// 		return false;
// 	}
	
// 	private function determineDependantPathPartEiFields() {
// 		$dependantPathPartEiFields = array();
// 		foreach ($this->getEiSpec()->getSupremeEiSpec()->getEiFieldCollection() as $eiField) {
// 			if (!$eiField instanceof PathPartEiField ||
// 					$this->getPropertyName() !== $eiField->getUniquePerPropertyName()) continue;
// 			$dependantPathPartEiFields[] = $eiField;
// 		}
// 		return $dependantPathPartEiFields;
// 	}
// }
