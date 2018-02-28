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
namespace rocket\spec\ei\manage\generic;

use rocket\spec\ei\EiPropPath;
use n2n\util\col\HashMap;
use n2n\util\col\Map;

class ScalarEiDefinition {
	private $scalarEiProperties;
	
	public function __construct() {
		$this->scalarEiProperties = new HashMap(EiPropPath::class, ScalarEiProperty::class);
	}
	
	/**
	 * @return Map
	 */
	public function getMap() {
		return $this->scalarEiProperties;
	}
	
	public function getScalarEiPropertyByFieldPath($eiPropPath): ScalarEiProperty {
		if (null !== ($genericEiProperty = $this->scalarEiProperties
				->offsetGet(EiPropPath::create($eiPropPath)))) {
			return $genericEiProperty;
		}
	
		throw new UnknownScalarEiPropertyException('Unknown ScalarEiProperty: ' . $eiPropPath);
	}
}


