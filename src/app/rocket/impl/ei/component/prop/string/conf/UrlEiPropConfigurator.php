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
namespace rocket\impl\ei\component\prop\string\conf;

use n2n\util\StringUtils;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\impl\web\dispatch\mag\model\StringArrayMag;
use n2n\util\type\TypeConstraint;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\ei\component\EiSetup;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\persistence\meta\structure\Column;
use n2n\impl\web\dispatch\mag\model\StringMag;

class UrlEiPropConfigurator extends AlphanumericEiPropConfigurator {
	const MAG_ALLOWED_PROTOCOLS_KEY = 'allowedProtocols';
	const MAG_RELATIVE_ALLOWED_KEY = 'relativeAllowed'; 
	const ATTR_AUTO_SCHEME_KEY = 'autoScheme';
	
	private static $commonNeedles = array('url', 'link');
	private static $commonNotNeedles = array('label', 'title', 'text');
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		
		if ($level <= CompatibilityLevel::NOT_COMPATIBLE) return $level;
		
		$propertyName = $propertyAssignation->getObjectPropertyAccessProxy()->getPropertyName();
		if (StringUtils::contains(self::$commonNeedles, $propertyName, false) 
				&& !StringUtils::contains(self::$commonNotNeedles, $propertyName, false)) {
			return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	public function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null) {
		parent::initAutoEiPropAttributes($n2nContext, $column);
		
		$this->attributes->set(self::ATTR_AUTO_SCHEME_KEY, 'http');
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		if ($this->attributes->contains(self::MAG_RELATIVE_ALLOWED_KEY)) {
			$this->eiComponent->setRelativeAllowed($this->attributes->getBool(self::MAG_RELATIVE_ALLOWED_KEY));
		}
		
		if ($this->attributes->contains(self::MAG_ALLOWED_PROTOCOLS_KEY)) {
			$this->eiComponent->setAllowedSchemes($this->attributes->getArray(self::MAG_ALLOWED_PROTOCOLS_KEY,
					true, array(), TypeConstraint::createSimple('string')));
		}
		
		if ($this->attributes->contains(self::ATTR_AUTO_SCHEME_KEY)) {
			$this->eiComponent->setAutoScheme($this->attributes->getString(self::ATTR_AUTO_SCHEME_KEY, 
					false, null, true));
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\string\conf\AlphanumericEiPropConfigurator::createMagDispatchable($n2nContext)
	 * @return MagDispatchable
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		
		$lar = new LenientAttributeReader($this->attributes);
		$magDispatchable->getMagCollection()->addMag(self::MAG_RELATIVE_ALLOWED_KEY, new BoolMag('Relative allowed',
				$lar->getBool(self::MAG_RELATIVE_ALLOWED_KEY, $this->eiComponent->isRelativeAllowed())));
	
		$magDispatchable->getMagCollection()->addMag(self::MAG_ALLOWED_PROTOCOLS_KEY, 
				new StringArrayMag('Allowed protocols', $lar->getArray(self::MAG_ALLOWED_PROTOCOLS_KEY, 
						TypeConstraint::createSimple('string'), $this->eiComponent->getAllowedSchemes())));
	
		$magDispatchable->getMagCollection()->addMag(self::ATTR_AUTO_SCHEME_KEY, 
				new StringMag('Auto scheme', $lar->getString(self::ATTR_AUTO_SCHEME_KEY, 
						$this->eiComponent->getAutoScheme())));
		
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
	
		$magCollection = $magDispatchable->getMagCollection();
		
		$this->attributes->set(self::MAG_RELATIVE_ALLOWED_KEY, $magCollection
				->getMagByPropertyName(self::MAG_RELATIVE_ALLOWED_KEY)->getValue());

		$this->attributes->set(self::MAG_ALLOWED_PROTOCOLS_KEY, $magCollection
				->getMagByPropertyName(self::MAG_ALLOWED_PROTOCOLS_KEY)->getValue());
		
		$this->attributes->set(self::ATTR_AUTO_SCHEME_KEY, $magCollection
				->getMagByPropertyName(self::ATTR_AUTO_SCHEME_KEY)->getValue());
	}
}
