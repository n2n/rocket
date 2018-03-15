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
namespace rocket\spec\extr;

use n2n\util\config\Attributes;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\util\config\AttributesException;
use n2n\util\config\InvalidConfigurationException;
use rocket\spec\InvalidSpecConfigurationException;
use rocket\ei\mask\model\DisplayScheme;
use rocket\ei\manage\gui\ui\DisplayStructure;
use rocket\ei\manage\gui\GuiIdPath;
use rocket\spec\InvalidEiMaskConfigurationException;
use rocket\ei\mask\model\ControlOrder;
use n2n\reflection\property\TypeConstraint;
use rocket\spec\InvalidLaunchPadConfigurationException;
use rocket\ei\manage\critmod\sort\SortData;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\util\config\InvalidAttributeException;
use rocket\ei\manage\critmod\filter\data\FilterGroupData;
use rocket\ei\manage\gui\ui\DisplayItem;
use n2n\util\StringUtils;
use rocket\spec\TypePath;

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
	
	/**
	 * @return array
	 */
	public function extractTypes() {
		$eiTypeExtractions = array();
		$customTypeExtractions = array();
		
		$typesKey = RawDef::TYPES_KEY;
		if (!$this->attributes->contains(RawDef::TYPES_KEY) && $this->attributes->contains('specs')) {
			$typesKey = 'specs';
		}
		
		foreach ($this->attributes->getArray($typesKey, false, array(), 
				TypeConstraint::createArrayLike('array', true)) as $typeId => $typeRawData) {
// 			$eiTypeExtractions[$specId] = $this->createTypeExtraction($specId, );
			$typeAttributes = new Attributes($typeRawData);
			
			try {
				$natureKey = RawDef::TYPE_NATURE_KEY;
				if (!$typeAttributes->contains(RawDef::TYPE_NATURE_KEY) && $typeAttributes->contains('type')) {
					$natureKey = 'type';
				}
				$nature = $typeAttributes->getEnum($natureKey, RawDef::getTypeNatures());
				
				if ($nature == RawDef::NATURE_ENTITY) {
					$eiTypeExtractions[$typeId] = $this->createEiTypeExtraction($typeId, $typeAttributes);
				} else {
					$customTypeExtractions[$typeId] = $this->createCustomTypeExtraction($typeId, $typeAttributes);
				}
			} catch (AttributesException $e) {
				throw $this->createSpecException($typeId, $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createSpecException($typeId, $e);
			}
		}
		
		return array('eiTypeExtractions' => $eiTypeExtractions, 'customTypeExtractions' => $customTypeExtractions);
	}
	
	
	private function createCustomTypeExtraction($id, Attributes $customSpecAttributes) {
		$extraction = new CustomTypeExtraction($id, $this->moduleNamespace);
		$extraction->setControllerLookupId($customSpecAttributes->getScalar(RawDef::CUSTOM_CONTROLLER_LOOKUP_ID_KEY));
		return $extraction;
	}
	
	private function createEiTypeExtraction($id, Attributes $eiTypeAttributes) {
		$extraction = new EiTypeExtraction($id, $this->moduleNamespace);
		$extraction->setEntityClassName($this->upgradeTypeName($eiTypeAttributes->getString(RawDef::EI_CLASS_KEY)));
		$extraction->setEiMaskExtraction($this->createEiMaskExtraction($eiTypeAttributes));
		$extraction->setDataSourceName($eiTypeAttributes->getString(RawDef::EI_DATA_SOURCE_NAME_KEY, false, null, true));
		
		if (null !== ($nssAttrs = $eiTypeAttributes->getArray(RawDef::EI_NESTED_SET_STRATEGY_KEY, false, null))) {
			$nssAttributes = new Attributes($nssAttrs);
			try {
				$extraction->setNestedSetStrategy(new NestedSetStrategy(
						CrIt::p($nssAttributes->getString(RawDef::EI_NESTED_SET_STRATEGY_LEFT_KEY)),
						CrIt::p($nssAttributes->getString(RawDef::EI_NESTED_SET_STRATEGY_RIGHT_KEY))));
			} catch (\InvalidArgumentException $e) {
				throw new InvalidAttributeException(
						'NestedSetStrategy attribute could not be converted to CriteriaProperty', 0, $e);
			}
		}
		
// 		$extraction->setDefaultEiMaskId($eiTypeAttributes->getString(RawDef::EI_DEFAULT_MASK_ID, false, null, true));
	
		return $extraction;
	}
	
	public function createEiMaskExtraction(Attributes $eiMaskAttributes) {
		$eiMaskExtraction = new EiMaskExtraction();
	
		$label = $eiMaskAttributes->getScalar(RawDef::EI_DEF_LABEL_KEY, false, null, true);
		$eiMaskExtraction->setLabel($label);
	
		$pluralLabel = $eiMaskAttributes->getScalar(RawDef::EI_DEF_PLURAL_LABEL_KEY, false, null, true);
		if ($pluralLabel === null) $pluralLabel = $label;
		$eiMaskExtraction->setPluralLabel($pluralLabel);
		
		$eiMaskExtraction->setIconType($eiMaskAttributes->getScalar(RawDef::EI_DEF_ICON_TYPE_KEY, false, null, true));
	
		$eiMaskExtraction->setIdentityStringPattern(
				$eiMaskAttributes->getString(RawDef::EI_DEF_REPRESENTATION_STRING_PATTERN_KEY, false, null, true));
	
		$eiMaskExtraction->setDraftingAllowed($eiMaskAttributes->getBool(RawDef::EI_DEF_DRAFTING_ALLOWED_KEY,
				false, $eiMaskExtraction->isDraftingAllowed()));
	
		$eiMaskExtraction->setPreviewControllerLookupId(
				$eiMaskAttributes->getString(RawDef::EI_DEF_PREVIEW_CONTROLLER_LOOKUP_ID_KEY, false, null, true));
	
	
		foreach ($eiMaskAttributes->getArray(RawDef::EI_DEF_FIELDS_KEY, false, array(), 
				TypeConstraint::createSimple('array')) as $eiPropId => $fieldRawData) {
			try {
				$eiMaskExtraction->addEiPropExtraction($this->createEiPropExtraction($eiPropId, new Attributes($fieldRawData)));
			} catch (AttributesException $e) {
				throw $this->createEiComponentException('EiProp ' . $eiPropId, $e);
			}
		}
	
		foreach ($eiMaskAttributes->getArray(RawDef::EI_DEF_COMMANDS_KEY, false, array(), 
				TypeConstraint::createSimple('array')) as $eiCommandId => $eiCommandRawData) {
			try {
				$eiMaskExtraction->addEiCommandExtraction($this->createEiComponentExtraction($eiCommandId, 
						new Attributes($eiCommandRawData)));
			} catch (AttributesException $e) {
				throw $this->createEiComponentException('EiCommand ' . $eiCommandId, $e);
			}
		}

		$eiMaskExtraction->setOverviewEiCommandId($eiMaskAttributes->getString(
				RawDef::EI_DEF_OVERVIEW_COMMAND_ID_KEY, false));	
		$eiMaskExtraction->setGenericDetailEiCommandId($eiMaskAttributes->getString(
				RawDef::EI_DEF_ENTRY_DETAIL_COMMAND_ID_KEY, false));	
		$eiMaskExtraction->setGenericEditEiCommandId($eiMaskAttributes->getString(
				RawDef::EI_DEF_ENTRY_EDIT_COMMAND_ID_KEY, false));	
		$eiMaskExtraction->setGenericEditEiCommandId($eiMaskAttributes->getString(
				RawDef::EI_DEF_ENTRY_ADD_COMMAND_ID_KEY, false));	
		
		if (null !== ($filterData = $eiMaskAttributes->getArray(RawDef::EI_DEF_FILTER_DATA_KEY, false, null, 
				TypeConstraint::createSimple('array')))) {
			$eiMaskExtraction->setFilterGroupData(FilterGroupData::create(new Attributes($filterData)));
		}
		
		if (null !== ($defaultSortData = $eiMaskAttributes->getScalarArray(RawDef::EI_DEF_DEFAULT_SORT_KEY, false, null))) {
			$eiMaskExtraction->setDefaultSortData(SortData::create(new Attributes($defaultSortData)));
		}
		
		$eiMaskExtraction->setDisplayScheme($this->createDisplayScheme($eiMaskAttributes));	

		return $eiMaskExtraction;
	}
	
	private function upgradeTypeName($typeName) {
	    if (!StringUtils::startsWith('rocket\spec\ei\component', $typeName)) {
	        return $typeName;
	    }
	    
	    return str_replace(
	    		array('rocket\\spec\\ei\\component\\field\\impl', 'rocket\\spec\\ei\\component\\prop\\impl', 'EiField',
	    				'rocket\\spec\\ei\\component\\command\\impl'), 
	    		array('rocket\\impl\\ei\\component\\prop', 'rocket\\impl\\ei\\component\\prop', 'EiProp',
	    				'rocket\\impl\\ei\\component\\command'), 
	    		$typeName);
	}
	
	private function createEiPropExtraction($id, Attributes $attributes)  {
		$extraction = new EiPropExtraction();
		$extraction->setId($id);
		$extraction->setLabel($attributes->getScalar(RawDef::EI_FIELD_LABEL_KEY));
		$extraction->setClassName($this->upgradeTypeName($attributes->getScalar(RawDef::EI_COMPONENT_CLASS_KEY)));
		$extraction->setProps($attributes->getArray(RawDef::EI_COMPONENT_PROPS_KEY, false));
		$extraction->setEntityPropertyName($attributes->getString(RawDef::EI_FIELD_ENTITY_PROPERTY_KEY, false, null, true));
		$extraction->setObjectPropertyName($attributes->getString(RawDef::EI_FIELD_OBJECT_PROPERTY_KEY, false, null, true));
		return $extraction;
	}
	
	private function createEiComponentExtraction($eiCommandId, Attributes $attributes) {
		$extraction = new EiComponentExtraction();
		$extraction->setId($eiCommandId);
		$extraction->setClassName($this->upgradeTypeName($attributes->getScalar(RawDef::EI_COMPONENT_CLASS_KEY)));
		$extraction->setProps($attributes->getArray(RawDef::EI_COMPONENT_PROPS_KEY, false));
		return $extraction;
	}	
	
	private function createEiModficatorExtraction(string $eiModificatorId, Attributes $attributes, 
			string $eiTypeId, string $eiMaskId = null) {
		$extraction = new EiModificatorExtraction($eiModificatorId, $this->moduleNamespace, 
				$eiTypeId, $eiMaskId);
		$extraction->setClassName($this->upgradeTypeName($attributes->getScalar(RawDef::EI_COMPONENT_CLASS_KEY)));
		$extraction->setProps($attributes->getArray(RawDef::EI_COMPONENT_PROPS_KEY, false));
		return $extraction;
	}	
	
	private function createSpecEiMaskException($eiTypeId, \Exception $previous) {
		throw new InvalidSpecConfigurationException('Spec with id \'' . $eiTypeId 
				. '\' contains invalid EiMask configurations.', 0, $previous);
	}
	
	private function createEiTypeExtensionException($eiMaskId, \Exception $previous) {
		throw new InvalidEiMaskConfigurationException('EiMask with id \'' . $eiMaskId
				. '\' contains invalid configurations.', 0, $previous);
	}
	
	private function createEiModificatorException($eiModificatorIdCombination, \Exception $previous) {
		throw new InvalidEiMaskConfigurationException('EiModificator with id \'' . $eiModificatorIdCombination
				. '\' contains invalid configurations.', 0, $previous);
	}
	
	/**
	 * @return \rocket\spec\extr\EiTypeExtensionExtraction[][]
	 */
	public function extractEiTypeExtensionGroups() {
		$eiTypeExtensionsKey = RawDef::EI_TYPE_EXTENSIONS_KEY;
		if (!$this->attributes->contains(RawDef::EI_TYPE_EXTENSIONS_KEY) 
				&& $this->attributes->contains('eiMasks')) {
			$eiTypeExtensionsKey = 'eiMasks';			
		}
		$attributes = new Attributes($this->attributes->getArray($eiTypeExtensionsKey, false));
		
		$eiTypeExtensionExtractionGroups = array();
		foreach ($attributes->getNames() as $extendedTypePathStr) {
			try {
				$extendedTypePath = TypePath::create($extendedTypePathStr);
				$eiTypeExtensionAttributes = new Attributes($attributes->getArray($extendedTypePathStr, false));
				$eiTypeExtensionExtractionGroups[$extendedTypePathStr] 
						= $this->createEiTypeExtensionExtractions($extendedTypePath, $eiTypeExtensionAttributes);
			} catch (\InvalidArgumentException $e) {
				throw $this->createSpecEiMaskException($extendedTypePathStr, $e);
			} catch (AttributesException $e) {
				throw $this->createSpecEiMaskException($extendedTypePathStr, $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createSpecEiMaskException($extendedTypePathStr, $e);
			}
		}
		
		return $eiTypeExtensionExtractionGroups;
	}
	
	private function createEiTypeExtensionExtractions(TypePath $extendedTypePath, Attributes $eiMasksAttributes): array {
		$eiTypeExtensionExtraction = array();
		
		foreach ($eiMasksAttributes->getNames() as $eiTypeExtensionId) {
			try {
				$eiTypeExtensionExtraction[$eiTypeExtensionId] = $this->createEiTypeExtensionExtraction(
						$extendedTypePath, $eiTypeExtensionId,
						new Attributes($eiMasksAttributes->getArray($eiTypeExtensionId)));
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiTypeExtensionException($eiTypeExtensionId, $e);
			} catch (AttributesException $e) {
				throw $this->createEiTypeExtensionException($eiTypeExtensionId, $e);
			}
		}
		
		return $eiTypeExtensionExtraction;
	}
	
	private function createEiTypeExtensionExtraction(TypePath $extendedTypePath, $id, Attributes $attributes): EiTypeExtensionExtraction {
		$eiTypeExtensionExtraction = new EiTypeExtensionExtraction($id, $this->moduleNamespace, $extendedTypePath);
		
		$eiTypeExtensionExtraction->setEiMaskExtraction($this->createEiMaskExtraction($attributes));
		
		return $eiTypeExtensionExtraction;
	}
	
	public function extractEiModificatorGroups() {
		$attributes = new Attributes($this->attributes->getArray(RawDef::EI_MODIFICATORS_KEY, false));
		
		$eiModificatorGroups = array();
		foreach ($attributes->getNames() as $idCombination) {
			try {
				$eiTypeId = RawDef::extractEiTypeIdFromIdCombination($idCombination);
				$eiMaskId = RawDef::extractEiMaskIdFromIdCombination($idCombination);
				
				$eiModificatorsAttributes = new Attributes($attributes->getArray($eiTypeId, false));
				$eiModificatorGroups[$eiTypeId] = $this->createEiModificatorExtractions($eiModificatorsAttributes, $eiTypeId, $eiMaskId);
			} catch (AttributesException $e) {
				throw $this->createEiModificatorException($idCombination, $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiModificatorException($idCombination, $e);
			}
		}
		
		return $eiModificatorGroups;
	}
	
	public function createEiModificatorExtractions(Attributes $eiModificatorsAttributes, string $eiTypeId, string $eiMaskId = null): array {
		$commonEiModificators = array();
		
		foreach ($eiModificatorsAttributes->getNames() as $modificatorId) {
			try {
				$commonEiModificators[$modificatorId] = $this->createEiModficatorExtraction($modificatorId,
						new Attributes($eiModificatorsAttributes->getArray($modificatorId)), $eiTypeId, $eiMaskId);
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiModificatorException(RawDef::buildEiTypeMaskId($eiTypeId, $eiMaskId), $e);
			} catch (AttributesException $e) {
				throw $this->createEiModificatorException(RawDef::buildEiTypeMaskId($eiTypeId, $eiMaskId), $e);
			}
		}
		
		return $commonEiModificators;
	}
	
	private function createDisplayScheme(Attributes $attributes): DisplayScheme {
		$guiOrder = new DisplayScheme();
		
		$guiOrder->setOverviewDisplayStructure($this->extractDisplayStructure(RawDef::OVERVIEW_GUI_FIELD_ORDER_KEY, $attributes));
		$guiOrder->setBulkyDisplayStructure($this->extractDisplayStructure(RawDef::BULKY_GUI_FIELD_ORDER_KEY, $attributes));
		$guiOrder->setDetailDisplayStructure($this->extractDisplayStructure(RawDef::DETAIL_GUI_FIELD_ORDER_KEY, $attributes));
		$guiOrder->setEditDisplayStructure($this->extractDisplayStructure(RawDef::EDIT_GUI_FIELD_ORDER_KEY, $attributes));
		$guiOrder->setAddDisplayStructure($this->extractDisplayStructure(RawDef::ADD_GUI_FIELD_ORDER_KEY, $attributes));
		
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
	
	private function extractDisplayStructure($key, Attributes $attributes) {
		$data = $attributes->getArray($key, false, null);
		if ($data === null) return null;
		
		try {
			return $this->createDisplayStructure($data);
		} catch (AttributesException $e) {
			throw new InvalidEiMaskConfigurationException('Field contains invalid DisplayStructure configuration: ' 
					. $key, 0, $e);
		}
	}
	
	private function createDisplayStructure(array $data) {
		$displayStructure = new DisplayStructure();
	
		foreach ($data as $key => $fieldId) {
			//Old specs (guiId)
			if (!is_array($fieldId)) {
				$displayStructure->addGuiIdPath(GuiIdPath::create($fieldId));
				continue;
			}
	
			$displayStructureAttributes = new Attributes($fieldId);
			
			//Old specs (fieldOrder)
			$title = $displayStructureAttributes->getScalar(RawDef::GUI_FIELD_ORDER_GROUP_TITLE_KEY, false);
			if (null !== $title) {
			    $dsa = $displayStructureAttributes->getArray('guiFieldOrder', false, null);
			    if ($dsa === null) {
			        $dsa = $displayStructureAttributes->getArray(RawDef::GUI_FIELD_ORDER_KEY);
			    }
			    $childDisplayStructure = $this->createDisplayStructure($dsa);
				$groupType = $displayStructureAttributes->getEnum(RawDef::GUI_FIELD_ORDER_GROUP_TYPE_KEY, DisplayItem::getGroupTypes(), 
						false, DisplayItem::TYPE_SIMPLE_GROUP, true);
				if ($groupType === null) {
					$groupType = DisplayItem::TYPE_SIMPLE_GROUP;
				}
				
				$displayStructure->addDisplayStructure($childDisplayStructure, $groupType, $title);
				continue;
			}
			
			$label = $displayStructureAttributes->getScalar(RawDef::DISPLAY_ITEM_LABEL_KEY, false, null, true);
			$guiIdPath = $displayStructureAttributes->getScalar(RawDef::DISPLAY_ITEM_GUI_ID_PATH_KEY, false, null, true);
			if (null !== $guiIdPath) {
				$displayStructure->addGuiIdPath(GuiIdPath::create($guiIdPath), 
						$displayStructureAttributes->getEnum(RawDef::DISPLAY_ITEM_GROUP_TYPE_KEY, DisplayItem::getTypes(),
						false, null, true), $label);
				continue;
			}
			
			$childDisplayStructure = $this->createDisplayStructure(
					$displayStructureAttributes->getArray(RawDef::DISPLAY_ITEM_DISPLAY_STRUCTURE_KEY));
			$displayStructure->addDisplayStructure($childDisplayStructure, $displayStructureAttributes->getEnum(RawDef::DISPLAY_ITEM_GROUP_TYPE_KEY, 
					DisplayItem::getGroupTypes()), $label);
		}
	
		return $displayStructure;
	}
	
	/**
	 * @return \rocket\spec\extr\LaunchPadExtraction[]
	 */
	public function extractLaunchPads() {
		$launchPadsKey = RawDef::LAUNCH_PADS_KEY;
		if (!$this->attributes->contains(RawDef::LAUNCH_PADS_KEY)
				&& $this->attributes->contains('menuItems')) {
			$launchPadsKey = 'menuItems';
		}
		
		$launchPadExtractions = array();
		foreach ($this->attributes->getArray($launchPadsKey, false, array(), 
				TypeConstraint::createArrayLike('array', true)) as $typePathStr => $launchPadRawData) {
					
			$launchPadAttributes = null;
			if ($launchPadRawData !== null) {
				$launchPadAttributes = new Attributes($launchPadRawData);
			}
			
			$launchPadExtractions[$typePathStr] = $this->createLaunchPadExtraction($typePathStr, new Attributes($launchPadRawData));
		}
		return $launchPadExtractions;
	}

	/**
	 * @param string $launchPadId
	 * @param Attributes $specAttributes
	 * @return LaunchPadExtraction
	 */
	private function createLaunchPadExtraction($launchPadId, Attributes $specAttributes) {
		try {
			$launchPadExtraction = new LaunchPadExtraction(TypePath::create($launchPadId), $this->moduleNamespace);
			$launchPadExtraction->setLabel($specAttributes->getString(RawDef::LAUNCH_PAD_LABEL_KEY, false));
			return $launchPadExtraction;
		} catch (\InvalidArgumentException $e) {
			throw $this->createLaunchPadException($launchPadId, $e);
		} catch (AttributesException $e) {
			throw $this->createLaunchPadException($launchPadId, $e);
		} catch (\InvalidArgumentException $e) {
			throw $this->createLaunchPadException($launchPadId, $e);
		}
	}

	private function createLaunchPadException($id, \Exception $previous) {
		throw new InvalidLaunchPadConfigurationException('LaunchPad with following id is invalid configruated: ' . $id,
				0, $previous);
	}
}