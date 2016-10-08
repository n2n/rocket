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
namespace rocket\spec\config\extr;

use n2n\util\config\Attributes;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use n2n\util\config\AttributesException;
use n2n\util\config\InvalidConfigurationException;
use rocket\spec\config\mask\model\GuiSection;
use rocket\spec\ei\manage\critmod\filter\data\FilterData;
use rocket\spec\config\InvalidSpecConfigurationException;
use rocket\spec\config\mask\model\GuiOrder;
use rocket\spec\config\mask\model\GuiFieldOrder;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\config\InvalidEiMaskConfigurationException;
use rocket\spec\config\mask\model\ControlOrder;
use n2n\reflection\property\TypeConstraint;
use rocket\spec\config\InvalidMenuItemConfigurationException;
use rocket\spec\ei\manage\critmod\sort\SortData;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\util\config\InvalidAttributeException;
use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;

class SpecExtractor {
	private $attributes;
	private $moduleNamespace;
	
	public function __construct(Attributes $attributes, string $moduleNamespace) {
		$this->attributes = $attributes;
		$this->moduleNamespace = $moduleNamespace;
	}

	private function createSpecException($id, \Exception $previous) {
		throw new InvalidSpecConfigurationException('Spec with following id is invalid configruated: ' . $id, 
				0, $previous);
	}
	
	private function createEiComponentException($componentName, \Exception $previous) {
		throw new InvalidEiComponentConfigurationException('Component invalid configurated: ' . $componentName, 
				0, $previous);
	}
	
	public function extractSpecs() {
		$specExtractions = array();
		foreach ($this->attributes->getArray(RawDef::SPECS_KEY, false, array(), 
				TypeConstraint::createArrayLike('array', true)) as $specId => $specRawData) {
			$specExtractions[$specId] = $this->createSpecExtraction($specId, new Attributes($specRawData));
		}
		return $specExtractions;
	}
	
	private function createSpecExtraction($id, Attributes $specAttributes): SpecExtraction {
		try {
			$type = $specAttributes->getEnum(RawDef::SPEC_TYPE_KEY, RawDef::getSpecTypes());
			
			if ($type == RawDef::SPEC_TYPE_ENTITY) {
				return $this->createEiSpecExtraction($id, $specAttributes);
			} else {
				return $this->createCustomSpecExtraction($id, $specAttributes);
			}
		} catch (AttributesException $e) {
			throw $this->createSpecException($id, $e);
		} catch (InvalidConfigurationException $e) {
			throw $this->createSpecException($id, $e);
		}
	}
	
	private function createCustomSpecExtraction($id, Attributes $customSpecAttributes) {
		$extraction = new CustomSpecExtraction($id, $this->moduleNamespace);
		$extraction->setControllerClassName($customSpecAttributes->getScalar(RawDef::SPEC_CUSTOM_CONTROLLER_CLASS_KEY));
		return $extraction;
	}
	
	private function createEiSpecExtraction($id, Attributes $eiSpecAttributes) {
		$extraction = new EiSpecExtraction($id, $this->moduleNamespace);
		$extraction->setEntityClassName($eiSpecAttributes->getString(RawDef::SPEC_EI_CLASS_KEY));
		$extraction->setEiDefExtraction($this->createEiDefExtraction($eiSpecAttributes));
		$extraction->setDataSourceName($eiSpecAttributes->getString(RawDef::SPEC_EI_DATA_SOURCE_NAME_KEY, false, null, true));
		
		if (null !== ($nssAttrs = $eiSpecAttributes->getArray(RawDef::SPEC_EI_NESTED_SET_STRATEGY_KEY, false, null))) {
			$nssAttributes = new Attributes($nssAttrs);
			try {
				$extraction->setNestedSetStrategy(new NestedSetStrategy(
						CrIt::p($nssAttributes->getString(RawDef::SPEC_EI_NESTED_SET_STRATEGY_LEFT_KEY)),
						CrIt::p($nssAttributes->getString(RawDef::SPEC_EI_NESTED_SET_STRATEGY_RIGHT_KEY))));
			} catch (\InvalidArgumentException $e) {
				throw new InvalidAttributeException(
						'NestedSetStrategy attribute could not be converted to CriteriaProperty', 0, $e);
			}
		}
		
		$extraction->setDefaultEiMaskId($eiSpecAttributes->getString(RawDef::SPEC_EI_DEFAULT_MASK_ID, false, null, true));
	
		return $extraction;
	}
	
	public function createEiDefExtraction(Attributes $eiDefAttributes) {
		$eiDefExtraction = new EiDefExtraction();
	
		$label = $eiDefAttributes->getScalar(RawDef::EI_DEF_LABEL_KEY);
		$eiDefExtraction->setLabel($label);
	
		$pluralLabel = $eiDefAttributes->getScalar(RawDef::EI_DEF_PLURAL_LABEL_KEY, false);
		if ($pluralLabel === null) $pluralLabel = $label;
		$eiDefExtraction->setPluralLabel($pluralLabel);
	
		$eiDefExtraction->setIdentityStringPattern(
				$eiDefAttributes->getString(RawDef::EI_DEF_REPRESENTATION_STRING_PATTERN_KEY, false, null, true));
	
		$eiDefExtraction->setDraftingAllowed($eiDefAttributes->getBool(RawDef::EI_DEF_DRAFTING_ALLOWED_KEY,
				false, $eiDefExtraction->isDraftingAllowed()));
	
		$eiDefExtraction->setPreviewControllerLookupId(
				$eiDefAttributes->getString(RawDef::EI_DEF_PREVIEW_CONTROLLER_LOOKUP_ID_KEY, false, null, true));
	
	
		foreach ($eiDefAttributes->getArray(RawDef::EI_DEF_FIELDS_KEY, false, array(), 
				TypeConstraint::createSimple('array')) as $eiFieldId => $fieldRawData) {
			try {
				$eiDefExtraction->addEiFieldExtraction($this->createEiFieldExtraction($eiFieldId, new Attributes($fieldRawData)));
			} catch (AttributesException $e) {
				throw $this->createEiComponentException('EiField ' . $eiFieldId, $e);
			}
		}
	
		foreach ($eiDefAttributes->getArray(RawDef::EI_DEF_COMMANDS_KEY, false, array(), 
				TypeConstraint::createSimple('array')) as $eiCommandId => $eiCommandRawData) {
			try {
				$eiDefExtraction->addEiCommandExtraction($this->createEiComponentExtraction($eiCommandId, 
						new Attributes($eiCommandRawData)));
			} catch (AttributesException $e) {
				throw $this->createEiComponentException('EiCommand ' . $eiCommandId, $e);
			}
		}
	
		foreach ($eiDefAttributes->getArray(RawDef::EI_DEF_MODIFICATORS_KEY, false, array(), 
				TypeConstraint::createSimple('array')) as $eiModificatorId => $eiModificatorRawData) {
			try {
				$eiDefExtraction->addEiModificatorExtraction($this->createEiComponentExtraction(
						$eiModificatorId, new Attributes($eiModificatorRawData)));
			} catch (AttributesException $e) {
				throw $this->createEiComponentException('EiModificator ' . $eiModificatorId, $e);
			}
		}

		$eiDefExtraction->setOverviewEiCommandId($eiDefAttributes->getString(
				RawDef::EI_DEF_OVERVIEW_COMMAND_ID_KEY, false));	
		$eiDefExtraction->setGenericDetailEiCommandId($eiDefAttributes->getString(
				RawDef::EI_DEF_ENTRY_DETAIL_COMMAND_ID_KEY, false));	
		$eiDefExtraction->setGenericEditEiCommandId($eiDefAttributes->getString(
				RawDef::EI_DEF_ENTRY_EDIT_COMMAND_ID_KEY, false));	
		$eiDefExtraction->setGenericEditEiCommandId($eiDefAttributes->getString(
				RawDef::EI_DEF_ENTRY_ADD_COMMAND_ID_KEY, false));	
		
		
		if (null !== ($filterData = $eiDefAttributes->getArray(RawDef::EI_DEF_FILTER_DATA_KEY, false, null, 
				TypeConstraint::createSimple('array')))) {
			$eiDefExtraction->setFilterGroupData(FilterGroupData::createFromArray(new Attributes($filterData)));
		}
		
		if (null !== ($defaultSortData = $eiDefAttributes->getScalarArray(RawDef::EI_DEF_DEFAULT_SORT_KEY, false, null))) {
			$eiDefExtraction->setDefaultSortData(SortData::create(new Attributes($defaultSortData)));
		}

		return $eiDefExtraction;
	}
	
	private function createEiFieldExtraction($id, Attributes $attributes)  {
		$extraction = new EiFieldExtraction();
		$extraction->setId($id);
		$extraction->setLabel($attributes->getScalar(RawDef::EI_FIELD_LABEL_KEY));
		$extraction->setClassName($attributes->getScalar(RawDef::EI_COMPONENT_CLASS_KEY));
		$extraction->setProps($attributes->getArray(RawDef::EI_COMPONENT_PROPS_KEY, false));
		$extraction->setEntityPropertyName($attributes->getString(RawDef::EI_FIELD_ENTITY_PROPERTY_KEY, false, null, true));
		$extraction->setObjectPropertyName($attributes->getString(RawDef::EI_FIELD_OBJECT_PROPERTY_KEY, false, null, true));
		return $extraction;
	}
	
	private function createEiComponentExtraction($eiCommandId, Attributes $attributes) {
		$extraction = new EiComponentExtraction();
		$extraction->setId($eiCommandId);
		$extraction->setClassName($attributes->getScalar(RawDef::EI_COMPONENT_CLASS_KEY));
		$extraction->setProps($attributes->getArray(RawDef::EI_COMPONENT_PROPS_KEY, false));
		return $extraction;
	}	
	
	
	private function createSpecCommonEiMaskException($eiSpecId, \Exception $previous) {
		throw new InvalidSpecConfigurationException('Spec with id \'' . $eiSpecId 
				. '\' contains invalid CommonEiMask configurations.', 0, $previous);
	}
	
	private function createCommonEiMaskException($commonEiMaskId, \Exception $previous) {
		throw new InvalidEiMaskConfigurationException('CommonEiMask with id \'' . $commonEiMaskId
				. '\' contains invalid configurations.', 0, $previous);
	}
	
	public function extractCommonEiMaskGroups() {
		$attributes = new Attributes($this->attributes->getArray(RawDef::COMMON_EI_MASKS_KEY, false));
		
		$commonEiMaskGroups = array();
		foreach ($attributes->getNames() as $eiSpecId) {
			try {
				$commonEiMasksAttributes = new Attributes($attributes->getArray($eiSpecId, false));
				$commonEiMaskGroups[$eiSpecId] = $this->createCommonEiMaskExtractions($commonEiMasksAttributes);
			} catch (AttributesException $e) {
				throw $this->createSpecCommonEiMaskException($eiSpecId, $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createSpecCommonEiMaskException($eiSpecId, $e);
			}
		}
		
		return $commonEiMaskGroups;
	}
	
	public function createCommonEiMaskExtractions(Attributes $commonEiMasksAttributes): array {
		$commonEiMasks = array();
		
		foreach ($commonEiMasksAttributes->getNames() as $commonEiMaskId) {
			try {
				$commonEiMasks[$commonEiMaskId] = $this->createCommonEiMaskExtraction($commonEiMaskId,
						new Attributes($commonEiMasksAttributes->getArray($commonEiMaskId)));
			} catch (InvalidConfigurationException $e) {
				throw $this->createCommonEiMaskException($commonEiMaskId, $e);
			} catch (AttributesException $e) {
				throw $this->createCommonEiMaskException($commonEiMaskId, $e);
			}
		}
		
		return $commonEiMasks;
	}
	
	private function createCommonEiMaskExtraction($id, Attributes $attributes): CommonEiMaskExtraction {
		$maskExtraction = new CommonEiMaskExtraction($id, $this->moduleNamespace);
		
		$maskExtraction->setEiDefExtraction($this->createEiDefExtraction($attributes));
		$maskExtraction->setGuiOrder($this->createGuiOrder($attributes));	
		return $maskExtraction;
	}
	
	private function createGuiOrder(Attributes $attributes): GuiOrder {
		$guiOrder = new GuiOrder();
		
		$guiOrder->setOverviewGuiFieldOrder($this->extractGuiFieldOrder(RawDef::OVERVIEW_GUI_FIELD_ORDER_KEY, $attributes));
		$guiOrder->setBulkyGuiFieldOrder($this->extractGuiFieldOrder(RawDef::BULKY_GUI_FIELD_ORDER_KEY, $attributes));
		$guiOrder->setDetailGuiFieldOrder($this->extractGuiFieldOrder(RawDef::DETAIL_GUI_FIELD_ORDER_KEY, $attributes));
		$guiOrder->setEditGuiFieldOrder($this->extractGuiFieldOrder(RawDef::EDIT_GUI_FIELD_ORDER_KEY, $attributes));
		$guiOrder->setAddGuiFieldOrder($this->extractGuiFieldOrder(RawDef::ADD_GUI_FIELD_ORDER_KEY, $attributes));
		
		if (null !== ($controlIds = $attributes->getScalarArray(RawDef::EI_DEF_PARTIAL_CONTROL_ORDER_KEY, false))) {
			$guiOrder->setPartialControlOrder(new ControlOrder($controlIds));
		}
		
		if (null !== ($controlIds = $attributes->getScalarArray(RawDef::EI_DEF_OVERALL_CONTROL_ORDER_KEY, false))) {
			$guiOrder->setOverallControlOrder(new ControlOrder($controlIds));
		}
		
		if (null !== ($controlIds = $attributes->getScalarArray(RawDef::EI_DEF_ENTRY_CONTROL_ORDER_KEY, false))) {
			$guiOrder->setEntryControlOrder(new ControlOrder($controlIds));
		}
		
		return $guiOrder;
	}
	
	private function extractGuiFieldOrder($key, Attributes $attributes) {
		$data = $attributes->getArray($key, false);
		if (empty($data)) return null;
		
		try {
			return $this->createGuiFieldOrder($data);
		} catch (AttributesException $e) {
			throw new InvalidEiMaskConfigurationException('Field contains invalid GuiFieldOrder configuration: ' 
					. $key, 0, $e);
		}
	}
	
	private function createGuiFieldOrder(array $data) {
		$guiFieldOrder = new GuiFieldOrder();
	
		foreach ($data as $key => $fieldId) {
			if (!is_array($fieldId)) {
				$guiFieldOrder->addGuiIdPath(GuiIdPath::createFromExpression($fieldId));
				continue;
			}
	
			$guiSectionAttributes = new Attributes($fieldId);
			
			$guiSection = new GuiSection();
			$guiSection->setType($guiSectionAttributes->getEnum(RawDef::GUI_FIELD_ORDER_GROUP_TYPE_KEY,
					GuiSection::getTypes(), false));
			$guiSection->setTitle($guiSectionAttributes->getScalar(RawDef::GUI_FIELD_ORDER_GROUP_TITLE_KEY));
			$guiSection->setGuiFieldOrder($this->createGuiFieldOrder($guiSectionAttributes->getArray(RawDef::GUI_FIELD_ORDER_KEY)));
			
			$guiFieldOrder->addGuiGroup($guiSection);
		}
	
		return $guiFieldOrder;
	}
	
	public function extractMenuItems(): array {
		$menuItemExtractions = array();
		foreach ($this->attributes->getArray(RawDef::MENU_ITEMS_KEY, false, array(), 
				TypeConstraint::createArrayLike('array', true)) as $menuItemId => $menuItemRawData) {
					
			$menuItemAttributes = null;
			if ($menuItemRawData !== null) {
				$menuItemAttributes = new Attributes($menuItemRawData);
			}
				
			$menuItemExtractions[$menuItemId] = $this->createMenuItemExtraction($menuItemId, new Attributes($menuItemRawData));
		}
		return $menuItemExtractions;
	}

	private function createMenuItemExtraction($menuItemId, Attributes $specAttributes): MenuItemExtraction {
		try {
			$menuItemExtraction = MenuItemExtraction::createFromId($menuItemId, $this->moduleNamespace);
			$menuItemExtraction->setLabel($specAttributes->getString(RawDef::MENU_ITEM_LABEL_KEY, false));
			return $menuItemExtraction;
		} catch (AttributesException $e) {
			throw $this->createMenuItemException($menuItemId, $e);
		} catch (\InvalidArgumentException $e) {
			throw $this->createMenuItemException($menuItemId, $e);
		}
	}

	private function createMenuItemException($id, \Exception $previous) {
		throw new InvalidMenuItemConfigurationException('MenuItem with following id is invalid configruated: ' . $id,
				0, $previous);
	}
}
