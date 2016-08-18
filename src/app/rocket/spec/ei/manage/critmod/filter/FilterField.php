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
namespace rocket\spec\ei\manage\critmod\filter;

use n2n\util\config\Attributes;
use n2n\dispatch\mag\MagCollection;
use n2n\l10n\N2nLocale;
use n2n\dispatch\mag\MagDispatchable;

interface FilterField {
	/**
	 * @return string
	 */
	public function getLabel(N2nLocale $n2nLocale): string;
	
	/**
	 * No Exception should be thrown if Attributes are invalid. Use of {@link \n2n\util\config\LenientAttributeReader}
	 * recommended.
	 * @return \n2n\dispatch\mag\MagCollection 
	 */
	public function createMagDispatchable(Attributes $attributes): MagDispatchable;

	/**
	 * @return \n2n\util\config\Attributes
	 */
	public function buildAttributes(MagDispatchable $magDispatchable): Attributes;
	
	/**
	 * No Exception should be thrown if Attributes are invalid. Use of {@link \n2n\util\config\LenientAttributeReader}
	 * recommended.
	 * @param Attributes $attributes
	 * @return ComparatorConstraint
	 * @throws AttributesException
	 */
	public function createComparatorConstraint(Attributes $attributes): ComparatorConstraint;
}
