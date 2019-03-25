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

use rocket\ei\component\EiSetup;
use n2n\core\container\N2nContext;
use rocket\impl\ei\component\prop\string\StringEiProp;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\util\StringUtils;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\persistence\meta\structure\Column;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\util\type\CastUtils;
class StringEiPropConfigurator extends AlphanumericEiPropConfigurator {
	const OPTION_MULTILINE_KEY = 'multiline';
	
	public function setup(EiSetup $setupProcess) {
		parent::setup($setupProcess);
	
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof StringEiProp);
		
		if ($this->attributes->contains(self::OPTION_MULTILINE_KEY)) {
			$eiComponent->setMultiline($this->attributes->getBool(self::OPTION_MULTILINE_KEY));
		}
	}
	
	private static $multilineNeedles = array('description', 'lead', 'intro', 'content');
	
	public function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null) {
		parent::initAutoEiPropAttributes($n2nContext, $column);
		
		if (StringUtils::contains(self::$multilineNeedles, $this->requirePropertyName(), false)) {
			$this->attributes->set(self::OPTION_MULTILINE_KEY, true);
			$this->attributes->set(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, false);
		}
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator::testCompatibility()
	 */
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$this->setMaxCompatibilityLevel(CompatibilityLevel::SUITABLE);
		return parent::testCompatibility($propertyAssignation);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\string\conf\AlphanumericEiPropConfigurator::createMagDispatchable($n2nContext)
	 * @return MagDispatchable
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof StringEiProp);
		
		$magDispatchable->getMagCollection()->addMag(self::OPTION_MULTILINE_KEY, new BoolMag('Multiline',
				$this->attributes->getBool(self::OPTION_MULTILINE_KEY, false, $eiComponent->isMultiline())));
		
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$multilineMag = $magDispatchable->getMagCollection()->getMagByPropertyName(self::OPTION_MULTILINE_KEY);

		$this->attributes->set(self::OPTION_MULTILINE_KEY, $multilineMag->getValue());
	}
}
