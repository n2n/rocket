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
namespace rocket\spec\ei\component\field\impl\relation\model\filter;

use rocket\spec\ei\manage\critmod\filter\EiMappingFilterField;
use rocket\spec\ei\manage\mapping\EiMappingConstraint;
use n2n\util\config\Attributes;
use rocket\spec\ei\manage\critmod\filter\ComparatorConstraint;
use rocket\spec\ei\manage\mapping\EiFieldConstraint;

class ToOneEiMappingFilterField extends RelationFilterField implements EiMappingFilterField {
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\critmod\filter\EiMappingFilterField::createEiFieldConstraint()
	 */
	public function createEiFieldConstraint(): EiFieldConstraint {
		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\critmod\filter\FilterField::createComparatorConstraint($attributes)
	 */
	public function createComparatorConstraint(Attributes $attributes): ComparatorConstraint {
		
	}


}
