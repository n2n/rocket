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

namespace rocket\impl\ei\component\provider;

use rocket\op\spec\setup\EiPresetProp;
use n2n\util\type\TypeConstraints;
use n2n\util\type\NamedTypeConstraint;

class NatureProviderUtils {

	static function compileTypeNames(EiPresetProp $eiPresetProp, bool &$nullAllowed) {
		$accessProxy = $eiPresetProp->getPropertyAccessProxy();

		$nullCheckConstraints = [];
		if ($accessProxy->isWritable()) {
			$nullCheckConstraints = $accessProxy->getSetterConstraint()->getNamedTypeConstraints();
		} else {
			$nullCheckConstraints = $accessProxy->getGetterConstraint()->getNamedTypeConstraints();
		}
		$nullAllowed = !empty(array_filter($nullCheckConstraints, fn (NamedTypeConstraint $ntc) => $ntc->allowsNull()));

		$namedTypeConstraints = [];

		if ($accessProxy->isWritable()) {
			array_push($namedTypeConstraints,
					...$accessProxy->getSetterConstraint()->getNamedTypeConstraints());
		}

		if (!$eiPresetProp->isEditable()) {
			array_push($namedTypeConstraints,
					...$accessProxy->getGetterConstraint()->getNamedTypeConstraints());
		}

		if (null !== ($type = $accessProxy->getProperty()?->getType())) {
			array_push($namedTypeConstraints,
					...TypeConstraints::type($type)->getNamedTypeConstraints());
		}

		$typeNames = [];
		foreach ($namedTypeConstraints as $namedTypeConstraint) {
			if ($namedTypeConstraint->isMixed()) {
				continue;
			}

			$typeName = $namedTypeConstraint->getTypeName();
			$typeNames[$typeName] = $typeName;
		}

		return $typeNames;
	}
}