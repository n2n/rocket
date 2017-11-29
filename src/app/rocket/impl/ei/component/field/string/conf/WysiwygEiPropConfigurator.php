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
namespace rocket\impl\ei\component\field\string\conf;

use rocket\impl\ei\component\field\adapter\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\field\string\wysiwyg\WysiwygEiProp;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\StringMag;
use rocket\impl\ei\component\field\string\wysiwyg\WysiwygHtmlBuilder;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\reflection\CastUtils;
use n2n\impl\web\dispatch\mag\model\StringArrayMag;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\spec\ei\component\EiSetupProcess;
use rocket\spec\ei\component\field\indepenent\PropertyAssignation;
use rocket\spec\ei\component\field\indepenent\CompatibilityLevel;
use n2n\util\StringUtils;
use n2n\web\dispatch\mag\MagDispatchable;

class WysiwygEiPropConfigurator extends AdaptableEiPropConfigurator {
	const OPTION_MODE_KEY = 'mode';
	const OPTION_LINK_KEY = 'linkConfig';
	const OPTION_CSS_CONFIG_KEY = 'cssConfig';
	const OPTION_TABLE_EDITING_KEY = 'tableEditing';
	const OPTION_BBCODE_KEY = 'bbcode';
	
	public function __construct(WysiwygEiProp $wysiwygEiProp) {
		parent::__construct($wysiwygEiProp);
		
		$this->autoRegister($wysiwygEiProp);
	}
	
	private function getChoicesMap() {
		$modes = WysiwygHtmlBuilder::getModes();
		return array_combine($modes, $modes);
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
	
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof WysiwygEiProp);
		
		$magCollection->addMag(new EnumMag(self::OPTION_MODE_KEY, 'Mode',
				$this->getChoicesMap(), $this->attributes->get(self::OPTION_MODE_KEY, false, $eiComponent->getMode())));
		
		$magCollection->addMag(new StringArrayMag(self::OPTION_LINK_KEY, 'Link Configuration',
				$this->attributes->get(self::OPTION_LINK_KEY, false, $eiComponent->getLinkConfigClassNames())));
		
		$magCollection->addMag(new StringMag(self::OPTION_CSS_CONFIG_KEY, 'Css Configuration',
				$this->attributes->get(self::OPTION_CSS_CONFIG_KEY, false, $eiComponent->getCssConfigClassName())));
		
		$magCollection->addMag(new BoolMag(self::OPTION_TABLE_EDITING_KEY, 'Table Editing',
				$this->attributes->get(self::OPTION_TABLE_EDITING_KEY, false, $eiComponent->isTableEditingEnabled())));
		
		$magCollection->addMag(new BoolMag(self::OPTION_BBCODE_KEY, 'BBcode',
				$this->attributes->get(self::OPTION_BBCODE_KEY, false, $eiComponent->isBbcodeEnabled())));
				
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
		
		$this->attributes->set(self::OPTION_MODE_KEY,
				$magCollection->getMagByPropertyName(self::OPTION_MODE_KEY)->getValue());
		
		$this->attributes->set(self::OPTION_LINK_KEY,
				$magCollection->getMagByPropertyName(self::OPTION_LINK_KEY)->getValue());

		$this->attributes->set(self::OPTION_CSS_CONFIG_KEY,
				$magCollection->getMagByPropertyName(self::OPTION_CSS_CONFIG_KEY)->getValue());
		
		$this->attributes->set(self::OPTION_TABLE_EDITING_KEY,
				$magCollection->getMagByPropertyName(self::OPTION_TABLE_EDITING_KEY)->getValue());
		
		$this->attributes->set(self::OPTION_BBCODE_KEY,
				$magCollection->getMagByPropertyName(self::OPTION_BBCODE_KEY)->getValue());
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		if (CompatibilityLevel::NOT_COMPATIBLE === $level) {
			return $level;
		}
		
		if (StringUtils::endsWith('Html', $propertyAssignation->getEntityProperty()->getName())) {
			return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		$wysiwygEiProp = $this->eiComponent;
		CastUtils::assertTrue($wysiwygEiProp instanceof WysiwygEiProp);
		
		if ($this->attributes->contains(self::OPTION_MODE_KEY)) {
			try {
				$wysiwygEiProp->setMode($this->attributes->get(self::OPTION_MODE_KEY));
			} catch (\InvalidArgumentException $e) {
				throw $eiSetupProcess->createException('Invalid value for option ' . self::OPTION_MODE_KEY, $e);
			}
		}
		
		if ($this->attributes->contains(self::OPTION_LINK_KEY)) {
			$wysiwygEiProp->setLinkConfigClassNames((array) $this->attributes->get(self::OPTION_LINK_KEY));
		}
		
		if ($this->attributes->contains(self::OPTION_CSS_CONFIG_KEY)) {
			$wysiwygEiProp->setCssConfigClassName($this->attributes->get(self::OPTION_CSS_CONFIG_KEY));			
		}
		
		if ($this->attributes->contains(self::OPTION_TABLE_EDITING_KEY)) {
			$wysiwygEiProp->setTableEditingEnabled($this->attributes->get(self::OPTION_TABLE_EDITING_KEY));
		}
		
		if ($this->attributes->contains(self::OPTION_BBCODE_KEY)) {
			$wysiwygEiProp->setBbcodeEnabled($this->attributes->get(self::OPTION_BBCODE_KEY));
		}
	}
}
