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
namespace rocket\spec\ei\component\field\impl\l10n;

use rocket\spec\ei\manage\critmod\filter\impl\field\EnumFilterField;
use rocket\spec\ei\manage\critmod\SimpleComparatorConstraint;
use n2n\l10n\N2nLocale;
use n2n\util\config\Attributes;
use rocket\spec\ei\manage\critmod\filter\ComparatorConstraint;

class N2nLocaleFilterField extends EnumFilterField {

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\critmod\filter\impl\field\FilterField::createComparatorConstraint()
	 */
	public function createComparatorConstraint(Attributes $attributes): ComparatorConstraint {
		return new SimpleComparatorConstraint($this->criteriaProperty,
				$attributes->getEnum(self::OPERATOR_OPTION, $this->getOperators()),
				N2nLocale::build($attributes->getString(self::ATTR_VALUE_KEY, false, null, true)));
	}
}
