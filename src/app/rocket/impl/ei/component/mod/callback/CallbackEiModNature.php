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

namespace rocket\impl\ei\component\mod\callback;

use rocket\impl\ei\component\mod\adapter\EiModNatureAdapter;
use rocket\op\ei\util\Eiu;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\reflection\attribute\ClassAttribute;
use rocket\attribute\impl\EiModCallback;
use n2n\util\magic\MagicObjectUnavailableException;
use n2n\util\ex\err\ConfigurationError;
use rocket\op\ei\component\modificator\EiMod;
use rocket\attribute\impl\EiSetup;
use rocket\attribute\impl\EiEntrySetup;

class CallbackEiModNature extends EiModNatureAdapter {
	private CallbackFinder $finder;

	function __construct(private readonly object $obj) {
		$this->finder = new CallbackFinder(new \ReflectionClass($obj), false);
	}

	function setup(Eiu $eiu): void {
		$this->trigger(EiSetup::class, $eiu);
	}

	function setupEiEntry(Eiu $eiu) {
		$this->trigger(EiEntrySetup::class, $eiu);
	}

	private function trigger(string $attributeName, Eiu $eiu) {
		foreach ($this->finder->find($attributeName, $eiu) as $invoker) {
			$invoker->invoke($this->obj);
		}
	}
}