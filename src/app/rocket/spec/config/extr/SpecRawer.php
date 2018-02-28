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
use n2n\reflection\ArgUtils;
use rocket\spec\ei\mask\model\DisplayScheme;
use rocket\spec\ei\manage\gui\ui\DisplayStructure;

class SpecRawer {
	private $attributes;
	
	public function __construct(Attributes $attributes) {
		$this->attributes = $attributes;
	}
	// PUT
	
	public function rawSpecs(array $specExtractions) {
		$specsRawData = array();
		foreach ($specExtractions as $specExtraction) {
			if ($specExtraction instanceof CustomSpecExtraction) {
				$specsRawData[$specExtraction->getId()] = $this->buildCustomSpecExtractionRawData($specExtraction);
			} else if ($specExtraction instanceof EiTypeExtraction) {
				$specsRawData[$specExtraction->getId()] = $this->buildEiTypeExtractionRawData($specExtraction);
			} else {
				throw new \InvalidArgumentException();
			}
		}
		
		$this->attributes->set(RawDef::SPECS_KEY, $specsRawData);
	}
	
	private function buildCustomSpecExtractionRawData(CustomSpecExtraction $customSpecExtraction) {
		$rawData = array();
		$rawData[RawDef::SPEC_TYPE_KEY] = RawDef::SPEC_TYPE_CUSTOM;
		$rawData[RawDef::SPEC_CUSTOM_CONTROLLER_CLASS_KEY] = $customSpecExtraction->getControllerClassName();
		return $rawData;
	}
	
	private function buildEiTypeExtractionRawData(EiTypeExtraction $extraction) {
		$rawData = array();	
		$rawData[RawDef::SPEC_TYPE_KEY] = RawDef::SPEC_TYPE_ENTITY;
		$rawData[RawDef::SPEC_EI_CLASS_KEY] = $extraction->getEntityClassName();
		$rawData[RawDef::SPEC_EI_DATA_SOURCE_NAME_KEY] = $extraction->getDataSourceName();
		
		if (null !== ($nestedSetStrategy = $extraction->getNestedSetStrategy())) {
			$rawData[RawDef::SPEC_EI_NESTED_SET_STRATEGY_KEY] = array(
					RawDef::SPEC_EI_NESTED_SET_STRATEGY_LEFT_KEY
							=> (string) $nestedSetStrategy->getLeftCriteriaProperty(),
					RawDef::SPEC_EI_NESTED_SET_STRATEGY_RIGHT_KEY
							=> (string) $nestedSetStrategy->getRightCriteriaProperty());
		}
		
		$rawData = array_merge($rawData, $this->buildEiDefExtractionRawData($extraction->getEiDefExtraction()));
		$rawData[RawDef::SPEC_EI_DEFAULT_MASK_ID] = $extraction->getDefaultEiMaskId();
		return $rawData;
	}
	
	public function rawEiMasks(array $groupedEiMaskExtractions) {
		$rawData = array();
		foreach ($groupedEiMaskExtractions as $eiTypeId => $eiMaskExtractions) {
			if (empty($eiMaskExtractions)) continue;
			
			$eiMasksRawData = array();
			foreach ($eiMaskExtractions as $eiMaskExtraction) {
				$eiMasksRawData[$eiMaskExtraction->getId()] = $this->buildEiMaskExtractionRawData($eiMaskExtraction);
			}
			
			$rawData[$eiTypeId] = $eiMasksRawData;
		}
		
		$this->attributes->set(RawDef::EI_MASKS_KEY, $rawData);
	}
	
	public function rawEiModificatorExtractionGroups(array $eiModificatorExtractionGroups) {
		if (empty($eiModificatorExtractionGroups)) return;
		
		$rawData = array();
		
		foreach ($eiModificatorExtractionGroups as $eiTypeId => $eiModificatorExtractionGroup) {
			if (empty($eiModificatorExtractionGroup)) continue;
			
			
			foreach ($eiModificatorExtractionGroup as $eiModificatorExtraction) {
				$idCombination = RawDef::buildEiTypeMaskId($eiModificatorExtraction->getEiTypeId(), 
						$eiModificatorExtraction->getEiMaskId());
				if (!isset($rawData[$idCombination])) {
					$rawData[$idCombination] = array();
				}
				
				$rawData[$idCombination][$eiModificatorExtraction->getId()] = $this->buildEiModificatorExtractionRawData($eiModificatorExtraction);
			}
		}
		
		$this->attributes->set(RawDef::EI_MODIFICATORS_KEY, $rawData);
	}
	
	private function buildEiMaskExtractionRawData(EiMaskExtraction $eiMaskExtraction) {
		$maskRawData = $this->buildEiDefExtractionRawData($eiMaskExtraction->getEiDefExtraction());
		
		return array_merge($maskRawData, $this->buildDisplaySchemeRawData($eiMaskExtraction->getDisplayScheme()));
	}

	private function buildEiDefExtractionRawData(EiDefExtraction $extraction) {
		$rawData[RawDef::EI_DEF_LABEL_KEY] = $extraction->getLabel();
		$rawData[RawDef::EI_DEF_PLURAL_LABEL_KEY] = $extraction->getPluralLabel();
		$rawData[RawDef::EI_DEF_ICON_TYPE_KEY] = $extraction->getIconType();
		
		if (null !== ($identityStringPattern = $extraction->getIdentityStringPattern())) {
			$rawData[RawDef::EI_DEF_REPRESENTATION_STRING_PATTERN_KEY] = $identityStringPattern;
		}
		
		if (null !== ($draftingAllowed = $extraction->isDraftingAllowed())) {
			$rawData[RawDef::EI_DEF_DRAFTING_ALLOWED_KEY] = $draftingAllowed;
		}
		
		if (null !== ($previewControllerLookupId = $extraction->getPreviewControllerLookupId())) {
			$rawData[RawDef::EI_DEF_PREVIEW_CONTROLLER_LOOKUP_ID_KEY] = $previewControllerLookupId;
		}
		
		if (null !== ($filterData = $extraction->getFilterGroupData())) {
			$rawData[RawDef::EI_DEF_FILTER_DATA_KEY] = $filterData->toArray();
		}
		
		if (null !== ($defaultSortDirection = $extraction->getDefaultSortData())) {
			$rawData[RawDef::EI_DEF_DEFAULT_SORT_KEY] = $defaultSortDirection->toAttrs();
		}
		
		$rawData[RawDef::EI_DEF_FIELDS_KEY] = array();
		foreach ($extraction->getEiPropExtractions() as $eiPropExtraction) {
			$rawData[RawDef::EI_DEF_FIELDS_KEY][$eiPropExtraction->getId()] 
					= $this->buildEiPropExtractionRawData($eiPropExtraction);
		}
	
		$rawData[RawDef::EI_DEF_COMMANDS_KEY] = array();
		foreach ($extraction->getEiCommandExtractions() as $eiComponentExtraction) {
			$rawData[RawDef::EI_DEF_COMMANDS_KEY][$eiComponentExtraction->getId()] 
					= $this->buildEiComponentExtractionRawData($eiComponentExtraction);
		}
		
		return $rawData;
	}
	
	private function buildEiPropExtractionRawData(EiPropExtraction $extraction) {
		$rawData = array();
		$rawData[RawDef::EI_COMPONENT_CLASS_KEY] = $extraction->getClassName();
		$rawData[RawDef::EI_COMPONENT_PROPS_KEY] = $extraction->getProps();
		
		if (null !== ($label = $extraction->getLabel())) {
			$rawData[RawDef::EI_FIELD_LABEL_KEY] = $label;
		}
		
		if (null !== ($objectPropertyName = $extraction->getObjectPropertyName())) {
			$rawData[RawDef::EI_FIELD_OBJECT_PROPERTY_KEY] = $objectPropertyName;
		}

		if (null !== ($entityPropertyName = $extraction->getEntityPropertyName())) {
			$rawData[RawDef::EI_FIELD_ENTITY_PROPERTY_KEY] = $entityPropertyName;
		}
		
		return $rawData;
	}
	
	private function buildEiComponentExtractionRawData(EiComponentExtraction $extraction) {
		return array(
				RawDef::EI_COMPONENT_CLASS_KEY => $extraction->getClassName(),
				RawDef::EI_COMPONENT_PROPS_KEY => $extraction->getProps());
	}
	
	private function buildEiModificatorExtractionRawData(EiModificatorExtraction $eiModificatorExtraction) {
		return array(
				RawDef::EI_COMPONENT_CLASS_KEY => $eiModificatorExtraction->getClassName(),
				RawDef::EI_COMPONENT_PROPS_KEY => $eiModificatorExtraction->getProps());
	}
	
	private function buildDisplaySchemeRawData(DisplayScheme $guiOrder) {
		$rawData = array();
		
		if (null !== ($overviewDisplayStructure = $this->buildDisplayStructureRawData($guiOrder->getOverviewDisplayStructure()))) {
			$rawData[RawDef::OVERVIEW_GUI_FIELD_ORDER_KEY] = $overviewDisplayStructure;
		}
		
		if (null !== ($bulkyDisplayStructure = $this->buildDisplayStructureRawData($guiOrder->getBulkyDisplayStructure()))) {
			$rawData[RawDef::BULKY_GUI_FIELD_ORDER_KEY] = $bulkyDisplayStructure;
		}
		
		if (null !== ($bulkyDisplayStructure = $this->buildDisplayStructureRawData($guiOrder->getDetailDisplayStructure()))) {
			$rawData[RawDef::DETAIL_GUI_FIELD_ORDER_KEY] = $bulkyDisplayStructure;
		}
		
		if (null !== ($bulkyDisplayStructure = $this->buildDisplayStructureRawData($guiOrder->getEditDisplayStructure()))) {
			$rawData[RawDef::EDIT_GUI_FIELD_ORDER_KEY] = $bulkyDisplayStructure;
		}
		
		if (null !== ($bulkyDisplayStructure = $this->buildDisplayStructureRawData($guiOrder->getAddDisplayStructure()))) {
			$rawData[RawDef::ADD_GUI_FIELD_ORDER_KEY] = $bulkyDisplayStructure;
		}
				
		if (null !== ($controlOrder = $guiOrder->getPartialControlOrder())) {
			$rawData[RawDef::EI_DEF_PARTIAL_CONTROL_ORDER_KEY] = $controlOrder->getControlIds();
		}
		
		if (null !== ($controlOrder = $guiOrder->getOverallControlOrder())) {
			$rawData[RawDef::EI_DEF_OVERALL_CONTROL_ORDER_KEY] = $controlOrder->getControlIds();
		}
		
		if (null !== ($controlOrder = $guiOrder->getEntryControlOrder())) {
			$rawData[RawDef::EI_DEF_ENTRY_CONTROL_ORDER_KEY] = $controlOrder->getControlIds();
		}
	
		return $rawData;
	}
	
	private function buildDisplayStructureRawData(DisplayStructure $displayStructure = null) {
		if ($displayStructure === null) return null;
	
		$displaStructureData = array();
		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			$displayItemData = array(
					RawDef::DISPLAY_ITEM_LABEL_KEY => $displayItem->getLabel(),
					RawDef::DISPLAY_ITEM_GROUP_TYPE_KEY => $displayItem->getType());
			if (!$displayItem->hasDisplayStructure()) {
				$displayItemData[RawDef::DISPLAY_ITEM_GUI_ID_PATH_KEY] = (string) $displayItem->getGuiIdPath();
			} else {
				$displayItemData[RawDef::DISPLAY_ITEM_DISPLAY_STRUCTURE_KEY] = 
						$this->buildDisplayStructureRawData($displayItem->getDisplayStructure());
			}
			
			$displaStructureData[] = $displayItemData;
		}
		
		return $displaStructureData;
	}
	
	public function rawMenuItems(array $menuItemExtractions) {
		ArgUtils::valArray($menuItemExtractions, MenuItemExtraction::class);
	
		$menuItemsRawData = array();
		foreach ($menuItemExtractions as $menuItemExtraction) {
			$menuItemsRawData[$menuItemExtraction->getId()] = $this->buildMenuItemExtractionRawData($menuItemExtraction);
		}
	
		$this->attributes->set(RawDef::MENU_ITEMS_KEY, $menuItemsRawData);
	}
	

	private function buildMenuItemExtractionRawData(MenuItemExtraction $menuItemExtraction) {
		if (null !== ($label = $menuItemExtraction->getLabel())) {
			return array(RawDef::MENU_ITEM_LABEL_KEY => $menuItemExtraction->getLabel());
		}
		
		return array();
	}
}
