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
namespace rocket\impl\ei\component\prop\ci\conf;

use rocket\impl\ei\component\prop\ci\ContentItemsEiProp;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\ci\model\ContentItem;
use rocket\ei\component\EiSetup;
use n2n\core\container\N2nContext;
use n2n\util\type\CastUtils;
use n2n\impl\web\dispatch\mag\model\MagCollectionArrayMag;
use rocket\impl\ei\component\prop\relation\conf\RelationEiPropConfigurator;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\spec\UnknownTypeException;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\util\type\attrs\LenientAttributeReader;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\ei\component\prop\indepenent\PropertyAssignation;

class ContentItemsEiPropConfigurator extends RelationEiPropConfigurator {
	const ATTR_PANELS_KEY = 'panels';
	
	private $contentItemsEiProp;
	
	public function __construct(ContentItemsEiProp $contentItemsEiProp) {
		parent::__construct($contentItemsEiProp);
		
		$this->contentItemsEiProp = $contentItemsEiProp;
		$this->setDisplayInOverviewDefault(false);
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
	
		CastUtils::assertTrue($this->eiComponent instanceof ContentItemsEiProp);
		
		$ciConfigUtils = null;
		try {
			$ciConfigUtils = CiConfigUtils::createFromN2nContext($n2nContext);
		} catch (UnknownTypeException $e) {
			return $magDispatchable;
		}
		
		$panelConfigMag = new MagCollectionArrayMag('Panels',
				function() use ($ciConfigUtils) {
					return new MagForm($ciConfigUtils->createPanelConfigMagCollection(true));
				});
		
		$magCollection->addMag(self::ATTR_PANELS_KEY, $panelConfigMag);
		
		$lar = new LenientAttributeReader($this->attributes);
// 		if ($lar->contains(self::ATTR_PANELS_KEY)) {
// 			$magValue = $lar->getArray(self::ATTR_PANELS_KEY, array(), TypeConstraint::createArrayLike('array',
// 					false, TypeConstraint::createSimple('scalar')));
			
// 			foreach ($magValue as $magValueField) {
// 				$magValueField[CiConfigUtils::ATTR_GRID_ENABLED_KEY] = isset($magValueField[CiConfigUtils::ATTR_GRID_KEY]);
// 			}
			
// 			if (!empty($magValue)) {
// 				$panelConfigMag->setValue($magValue);
// 				return $magDispatchable;
// 			}
// 		}
		
		$magValue = array();
		foreach ($lar->getArray(self::ATTR_PANELS_KEY) as $panelAttrs) {
			$magValue[] = $ciConfigUtils->buildPanelConfigMagCollectionValues($panelAttrs);
		}
		$panelConfigMag->setValue($magValue);
		
		return $magDispatchable;
	}
	
	
		
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$mag = $magDispatchable->getMagCollection()->getMagByPropertyName(self::ATTR_PANELS_KEY);
		CastUtils::assertTrue($mag instanceof MagCollectionArrayMag);
		
		$panelConfigAttrs = array();
		foreach ($mag->getValue() as $panelValues) {
			$panelConfigAttrs[] = CiConfigUtils::buildPanelConfigAttrs($panelValues);
		}
		
		if (!empty($panelConfigAttrs)) {
			$this->attributes->set(self::ATTR_PANELS_KEY, $panelConfigAttrs);
		}
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		parent::setup($eiSetupProcess);
	
		IllegalStateException::assertTrue($this->eiComponent instanceof ContentItemsEiProp);
		
// 		$this->eiComponent->setContentItemEiType($eiSetupProcess->getEiTypeByClass(
// 				ReflectionUtils::createReflectionClass('rocket\impl\ei\component\prop\ci\model\ContentItem')));
		
		if ($this->attributes->contains(self::ATTR_PANELS_KEY)) {
			$panelConfigs = array();
			foreach ((array) $this->attributes->get(self::ATTR_PANELS_KEY) as $panelAttrs) {
				$panelConfigs[] = CiConfigUtils::createPanelConfig($panelAttrs);
			}
			$this->eiComponent->setPanelConfigs($panelConfigs);
		}
	}

	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		if (CompatibilityLevel::NOT_COMPATIBLE === $level) {
			return $level;
		}

		if ($propertyAssignation->getEntityProperty()->getTargetEntityModel()->getClass()
				->getName() == ContentItem::class) {
			return CompatibilityLevel::COMMON;
		}

		return $level;
	}
}
