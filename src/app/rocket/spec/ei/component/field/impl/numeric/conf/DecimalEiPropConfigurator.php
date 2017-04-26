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
namespace rocket\spec\ei\component\field\impl\numeric\conf;

use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\reflection\CastUtils;
use rocket\spec\ei\component\field\impl\numeric\DecimalEiProp;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\config\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\StringMag;

class DecimalEiPropConfigurator extends NumericEiPropConfigurator {
	const OPTION_DECIMAL_PLACES_KEY = 'decimalPlaces';
	const OPTION_PREFIX_KEY = 'prefix';
	
	public function getTypeName(): string {
		return 'Decimal';
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$lar = new LenientAttributeReader($this->attributes);
		
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
		$magCollection->addMag(new NumericMag(self::OPTION_DECIMAL_PLACES_KEY, 
				'Positions after decimal point', $lar->getNumeric(self::OPTION_DECIMAL_PLACES_KEY, 0), true, 0));
		$magCollection->addMag(new StringMag(self::OPTION_PREFIX_KEY, 'Prefix',
				$lar->getString(self::OPTION_PREFIX_KEY, false)));
		return $magDispatchable;
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		parent::setup($eiSetupProcess);
			
		CastUtils::assertTrue($this->eiComponent instanceof DecimalEiProp);

		$this->eiComponent->setDecimalPlaces($this->attributes->getInt(self::OPTION_DECIMAL_PLACES_KEY, false, 0));
		$this->eiComponent->setPrefix($this->attributes->getString(self::OPTION_PREFIX_KEY, false));
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
	
		$magCollection = $magDispatchable->getMagCollection();
	
		$this->attributes->appendAll($magCollection->readValues(
				array(self::OPTION_DECIMAL_PLACES_KEY, self::OPTION_PREFIX_KEY), true), true);
	}
}
