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
use n2n\util\ex\IllegalStateException;
use n2n\core\container\N2nContext;
use rocket\impl\ei\component\prop\string\PasswordEiProp;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\web\dispatch\mag\MagDispatchable;

class PasswordEiPropConfigurator extends AlphanumericEiPropConfigurator {
	const OPTION_ALGORITHM_KEY = 'algorithm';

	public function setup(EiSetup $setupProcess) {
		parent::setup($setupProcess);

		$eiComponent = $this->eiComponent;
		IllegalStateException::assertTrue($eiComponent instanceof PasswordEiProp);
		if ($this->attributes->contains(self::OPTION_ALGORITHM_KEY)) {
			try {
				$eiComponent->setAlgorithm($this->attributes->get(self::OPTION_ALGORITHM_KEY));
			} catch (\InvalidArgumentException $e) {
				$setupProcess->createException('Invalid algorithm defined for PassworEiProp.', $e);
				return;
			}
		}
	}

	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);

		$eiComponent = $this->eiComponent;
		IllegalStateException::assertTrue($eiComponent instanceof PasswordEiProp);

		$algorithms = PasswordEiProp::getAlgorithms();
		$magDispatchable->getMagCollection()->addMag(self::OPTION_ALGORITHM_KEY, new EnumMag('Algortithm', 
				array_combine($algorithms, $algorithms), $eiComponent->getAlgorithm()));
		return $magDispatchable;
	}
}
