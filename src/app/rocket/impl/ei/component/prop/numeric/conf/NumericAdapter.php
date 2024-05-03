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
namespace rocket\impl\ei\component\prop\numeric\conf;

use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\spec\dbo\meta\structure\Column;
use n2n\spec\dbo\meta\structure\IntegerColumn;
use n2n\util\type\attrs\LenientAttributeReader;

use n2n\util\type\attrs\DataSet;
use rocket\op\ei\util\Eiu;
use n2n\web\dispatch\mag\MagCollection;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\impl\ei\component\prop\adapter\config\EditAdapter;

trait NumericAdapter {
	
	protected ?float $minValue = null;
	protected ?float $maxValue = null;
	
	/**
	 * @return int
	 */
	function getMinValue() {
	    return $this->minValue;
	}

	/**
	 * @param float|null $minValue
	 * @return $this
	 */
	function setMinValue(?float $minValue) {
	    $this->minValue = $minValue;
	    return $this;
	}
	
	/**
	 * @return float
	 */
	function getMaxValue() {
	    return $this->maxValue;
	}

	/**
	 * @param float|null $maxValue
	 * @return $this
	 */
	function setMaxValue(?float $maxValue) {
	    $this->maxValue = $maxValue;
	    return $this;
	}
}
