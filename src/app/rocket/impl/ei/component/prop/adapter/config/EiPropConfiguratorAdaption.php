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
namespace rocket\impl\ei\component\prop\adapter\config;

use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use n2n\persistence\meta\structure\Column;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\IncompatiblePropertyException;

interface EiPropConfiguratorAdaption {
	
	/**
	 * @param PropertyAssignation $propertyAssignation
	 * @return int
	 */
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int;
	
	/**
	 * @param PropertyAssignation $propertyAssignation
	 * @throws IncompatiblePropertyException
	 */
	function assignProperty(PropertyAssignation $propertyAssignation);
	
	/**
	 * @param Eiu $eiu
	 * @param DataSet $dataSet
	 * @param Column $column
	 */
	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null);
	
	/**
	 * @param Eiu $eiu
	 * @param DataSet $dataSet
	 * @param MagCollection $magCollection
	 */
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection);

	/**
	 * @param Eiu $eiu
	 * @param MagCollection $magCollection
	 * @param DataSet $dataSet
	 */
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet);
	
	/**
	 * @param Eiu $eiu
	 * @param DataSet $dataSet
	 */
	function setup(Eiu $eiu, DataSet $dataSet);
}