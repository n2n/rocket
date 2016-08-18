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

use rocket\spec\ei\EiFieldPath;
use n2n\util\col\HashMap;

class GenericEiDefinition {
	private $genericEiProperties;
	
	public function __construct() {
		$this->genericEiProperties = new HashMap(EiFieldPath::class, GenericEiProperty::class);
	}
	
	public function getGenericEiProperties() {
		return $this->genericEiProperties;
	}
	
	/**
	 * @param unknown $eiFieldPath
	 * @throws UnknownGenericEiPropertyException
	 * @return GenericEiProperty
	 */
	public function getGenericEiPropertyByFieldPath($eiFieldPath) {
		if (null !== ($genericEiProperty = $this->genericEiProperties
				->offsetGet(EiFieldPath::create($eiFieldPath)))) {
			return $genericEiProperty;
		}
	
		throw new UnknownGenericEiPropertyException('Unknown GenericEiProperty: ' . $eiFieldPath);
	}
}
