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
namespace rocket\spec\ei\component\field\impl\string\conf;

use rocket\spec\ei\component\EiSetupProcess;
use n2n\util\ex\IllegalStateException;
use n2n\core\container\N2nContext;
use rocket\spec\ei\component\field\impl\string\PasswordEiField;
use n2n\dispatch\mag\impl\model\EnumMag;
use rocket\spec\ei\component\field\impl\string\StringEiField;

class PasswordEiFieldConfigurator extends AlphanumericEiFieldConfigurator {
	const OPTION_ALGORITHM_KEY = 'algorithm';

	public function setup(EiSetupProcess $setupProcess) {
		parent::setup($setupProcess);

		IllegalStateException::assertTrue($this->eiComponent instanceof StringEiField);
		if ($this->attributes->contains(self::OPTION_ALGORITHM_KEY)) {
			try {
				$this->eiComponent->setAlgorithm($this->attributes->get(self::OPTION_ALGORITHM_KEY));
			} catch (\InvalidArgumentException $e) {
				$setupProcess->failed($this->eiComponent, 
						'Invalid algorithm defined for PassworEiField.', $e);
				return;
			}
		}
	}

	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magCollection = parent::createMagCollection($n2nContext);

		IllegalStateException::assertTrue($this->eiComponent instanceof PasswordEiField);

		$algorithms = PasswordEiField::getAlgorithms();
		$magCollection->addMag(new EnumMag(self::OPTION_ALGORITHM_KEY, 'Algortithm', 
				array_combine($algorithms, $algorithms), $this->eiComponent->getAlgorithm()));
		return $magCollection;
	}
}
