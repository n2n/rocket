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
namespace rocket\spec\ei\component\field\impl\ci\conf;

use rocket\spec\ei\component\field\impl\ci\ContentItemsEiField;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\core\container\N2nContext;
use n2n\reflection\CastUtils;
use n2n\reflection\ReflectionUtils;
use n2n\impl\web\dispatch\mag\model\MagCollectionArrayMag;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\dispatch\mag\model\EnumArrayMag;
use rocket\spec\ei\component\field\impl\ci\model\PanelConfig;
use n2n\util\ex\NotYetImplementedException;
use n2n\util\config\Attributes;
use rocket\spec\ei\component\field\impl\relation\conf\RelationEiFieldConfigurator;
use rocket\spec\ei\component\field\impl\enum\MultiSelectEiField;
use rocket\core\model\Rocket;
use rocket\spec\ei\component\field\impl\ci\model\ContentItem;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use rocket\spec\ei\EiSpec;
use n2n\reflection\property\TypeConstraint;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\spec\config\UnknownSpecException;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\util\config\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\reflection\ArgUtils;

class ContentItemsEiFieldConfigurator extends RelationEiFieldConfigurator {
	const ATTR_PANELS_KEY = 'panels';
	
	private $contentItemsEiField;
	
	public function __construct(ContentItemsEiField $contentItemsEiField) {
		parent::__construct($contentItemsEiField);
		
		$this->contentItemsEiField = $contentItemsEiField;
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
	
		CastUtils::assertTrue($this->eiComponent instanceof ContentItemsEiField);
		
		$ciConfigUtils = null;
		try {
			$ciConfigUtils = CiConfigUtils::createFromN2nContext($n2nContext);
		} catch (UnknownSpecException $e) {
			return $magDispatchable;
		}
		
		$panelConfigMag = new MagCollectionArrayMag(self::ATTR_PANELS_KEY, 'Panels',
				function() use ($ciConfigUtils) {
					return new MagForm($ciConfigUtils->createPanelConfigMagCollection(true));
				});
		
		$magCollection->addMag($panelConfigMag);
		
		$lar = new LenientAttributeReader($this->attributes);
		if ($lar->contains(self::ATTR_PANELS_KEY)) {
			$magValue = $lar->getArray(self::ATTR_PANELS_KEY, array(), TypeConstraint::createArrayLike('array', 
					false, TypeConstraint::createSimple('scalar')));
			
			if (!empty($magValue)) {
				$panelConfigMag->setValue($magValue);
				return $magDispatchable;
			}
		}
		
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
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		parent::setup($eiSetupProcess);
	
		IllegalStateException::assertTrue($this->eiComponent instanceof ContentItemsEiField);
		
// 		$this->eiComponent->setContentItemEiSpec($eiSetupProcess->getEiSpecByClass(
// 				ReflectionUtils::createReflectionClass('rocket\spec\ei\component\field\impl\ci\model\ContentItem')));
		
		if ($this->attributes->contains(self::ATTR_PANELS_KEY)) {
			$panelConfigs = array();
			foreach ((array) $this->attributes->get(self::ATTR_PANELS_KEY) as $panelAttrs) {
				$panelConfigs[] = CiConfigUtils::createPanelConfig($panelAttrs);
			}
			$this->eiComponent->setPanelConfigs($panelConfigs);
		}
	}
}
