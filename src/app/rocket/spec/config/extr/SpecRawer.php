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
use rocket\spec\config\mask\model\GuiOrder;
use rocket\spec\config\mask\model\GuiFieldOrder;

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
			} else if ($specExtraction instanceof EiSpecExtraction) {
				$specsRawData[$specExtraction->getId()] = $this->buildEiSpecExtractionRawData($specExtraction);
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
	
	private function buildEiSpecExtractionRawData(EiSpecExtraction $extraction) {
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
	
	public function rawCommonEiMasks(array $groupedCommonEiMaskExtractions) {
		$rawData = array();
		foreach ($groupedCommonEiMaskExtractions as $eiSpecId => $commonEiMaskExtractions) {
			if (empty($commonEiMaskExtractions)) continue;
			
			$commonEiMasksRawData = array();
			foreach ($commonEiMaskExtractions as $commonEiMaskExtraction) {
				$commonEiMasksRawData[$commonEiMaskExtraction->getId()] = $this->buildCommonEiMaskExtractionRawData($commonEiMaskExtraction);
			}
			
			$rawData[$eiSpecId] = $commonEiMasksRawData;
		}
		
		$this->attributes->set(RawDef::COMMON_EI_MASKS_KEY, $rawData);
	}
	
	private function buildCommonEiMaskExtractionRawData(CommonEiMaskExtraction $eiMaskExtraction) {
		$maskRawData = $this->buildEiDefExtractionRawData($eiMaskExtraction->getEiDefExtraction());
		
		return array_merge($maskRawData, $this->buildGuiOrderRawData($eiMaskExtraction->getGuiOrder()));
	}
	
	

	private function buildEiDefExtractionRawData(EiDefExtraction $extraction) {
		$rawData[RawDef::EI_DEF_LABEL_KEY] = $extraction->getLabel();
		$rawData[RawDef::EI_DEF_PLURAL_LABEL_KEY] = $extraction->getPluralLabel();
		
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
		foreach ($extraction->getEiFieldExtractions() as $eiFieldExtraction) {
			$rawData[RawDef::EI_DEF_FIELDS_KEY][$eiFieldExtraction->getId()] 
					= $this->buildEiFieldExtractionRawData($eiFieldExtraction);
		}
	
		$rawData[RawDef::EI_DEF_COMMANDS_KEY] = array();
		foreach ($extraction->getEiCommandExtractions() as $eiComponentExtraction) {
			$rawData[RawDef::EI_DEF_COMMANDS_KEY][$eiComponentExtraction->getId()] 
					= $this->buildEiComponentExtractionRawData($eiComponentExtraction);
		}
	
		$rawData[RawDef::EI_DEF_MODIFICATORS_KEY] = array();
		foreach ($extraction->getEiModificatorExtractions() as $eiComponentExtraction) {
			$rawData[RawDef::EI_DEF_MODIFICATORS_KEY][$eiComponentExtraction->getId()] 
					= $this->buildEiComponentExtractionRawData($eiComponentExtraction);
		}
		
		return $rawData;
	}
	
	private function buildEiFieldExtractionRawData(EiFieldExtraction $extraction) {
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
	
	private function buildGuiOrderRawData(GuiOrder $guiOrder) {
		$rawData = array();
		
		if (null !== ($overviewGuiFieldOrder = $this->buildGuiFieldOrderRawData($guiOrder->getOverviewGuiFieldOrder()))) {
			$rawData[RawDef::OVERVIEW_GUI_FIELD_ORDER_KEY] = $overviewGuiFieldOrder;
		}
		
		if (null !== ($bulkyGuiFieldOrder = $this->buildGuiFieldOrderRawData($guiOrder->getBulkyGuiFieldOrder()))) {
			$rawData[RawDef::BULKY_GUI_FIELD_ORDER_KEY] = $bulkyGuiFieldOrder;
		}
		
		if (null !== ($bulkyGuiFieldOrder = $this->buildGuiFieldOrderRawData($guiOrder->getDetailGuiFieldOrder()))) {
			$rawData[RawDef::DETAIL_GUI_FIELD_ORDER_KEY] = $bulkyGuiFieldOrder;
		}
		
		if (null !== ($bulkyGuiFieldOrder = $this->buildGuiFieldOrderRawData($guiOrder->getEditGuiFieldOrder()))) {
			$rawData[RawDef::EDIT_GUI_FIELD_ORDER_KEY] = $bulkyGuiFieldOrder;
		}
		
		if (null !== ($bulkyGuiFieldOrder = $this->buildGuiFieldOrderRawData($guiOrder->getAddGuiFieldOrder()))) {
			$rawData[RawDef::ADD_GUI_FIELD_ORDER_KEY] = $bulkyGuiFieldOrder;
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
	
	
	
	
	private function buildGuiFieldOrderRawData(GuiFieldOrder $guiFieldOrder = null) {
		if ($guiFieldOrder === null) return null;
	
		$guiOrderData = array();
		foreach ($guiFieldOrder->getOrderItems() as $orderItem) {
			if (!$orderItem->isSection()) {
				$guiOrderData[] = (string) $orderItem->getGuiIdPath();
				continue;
			}
			
			$guiSection = $orderItem->getGuiSection();
			$guiOrderData[] = array(
					RawDef::GUI_FIELD_ORDER_GROUP_TYPE_KEY => $guiSection->getType(),
					RawDef::GUI_FIELD_ORDER_GROUP_TITLE_KEY => $guiSection->getTitle(),
					RawDef::GUI_FIELD_ORDER_KEY => $this->buildGuiFieldOrderRawData($guiSection->getGuiFieldOrder()));
		}
		
		return $guiOrderData;
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
