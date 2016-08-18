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
namespace rocket\spec\ei\component;

use n2n\core\container\N2nContext;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\component\field\EiFieldCollection;
use rocket\spec\ei\component\command\EiCommandCollection;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use rocket\spec\ei\manage\generic\GenericEiProperty;
use rocket\spec\ei\manage\generic\ScalarEiProperty;

interface EiSetupProcess {
	
	public function containsClass(\ReflectionClass $class):  bool;
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\spec\ei\
	 * @throws UnknownException
	 */
	public function getEiSpecByClass(\ReflectionClass $class): EiSpec;
	
	public function getN2nContext(): N2nContext;
	
	public function createException($reason = null, \Exception $previous = null): InvalidEiComponentConfigurationException;
	
	public function getGenericEiPropertyByFieldPath($eiFieldPath): GenericEiProperty;
	
	public function getScalarEiPropertyByFieldPath($eiFieldPath): ScalarEiProperty;
	
	/**
	 * @return \rocket\spec\ei\component\field\EiFieldCollection
	 */
	public function getEiFieldCollection(): EiFieldCollection;
	
	/**
	 * @return \rocket\spec\ei\component\command\EiCommandCollection
	 */
	public function getEiCommandCollection(): EiCommandCollection;
	
	/**
	 * @return \rocket\spec\ei\component\modificator\EiModificatorCollection
	 */
	public function getEiModificatorCollection(): EiModificatorCollection;
}
