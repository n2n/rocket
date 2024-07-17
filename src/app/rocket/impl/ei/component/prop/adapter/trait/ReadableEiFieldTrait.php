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

namespace rocket\impl\ei\component\prop\adapter\trait;

use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\op\ei\component\prop\EiPropNature;
use n2n\reflection\property\AccessProxy;
use rocket\op\ei\util\factory\EifField;
use rocket\impl\ei\component\prop\adapter\PropertyNatureTrait;
use n2n\util\type\ArgUtils;
use n2n\validation\validator\Validator;
use n2n\util\type\TypeConstraint;

trait ReadableEiFieldTrait {
	use PropertyNatureTrait;


	protected function buildEifField(Eiu $eiu): ?EifField {
		if (!$eiu->prop()->isNativeReadable()) {
			return null;
		}

		$validators = $this->buildEiFieldValidators($eiu);
		ArgUtils::valArrayReturn($validators, $this, 'buildEiFieldValidators', Validator::class);

		return $eiu->factory()
				->newField($eiu->prop()->getNativeReadTypeConstraint(), function() use ($eiu) {
					return $eiu->prop()->readNativeValue();
				})
				->val(...$validators);
	}

	protected function buildEiFieldValidators(Eiu $eiu): array {
		return [];
	}

	/**
	 * @see EiPropNature::buildEiField()
	 */
	function buildEiField(Eiu $eiu): ?EiFieldNature {
		return $this->buildEifField($eiu)?->toEiField();
	}
}