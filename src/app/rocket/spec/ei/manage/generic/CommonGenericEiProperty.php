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

use n2n\l10n\Lstr;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\item\CriteriaItem;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\component\field\EiField;
use rocket\spec\ei\EiFieldPath;

class CommonGenericEiProperty implements GenericEiProperty {
	private $eiField;
	private $criteriaProperty;
	private $entityValueBuilder;
	private $mappableValueBuilder;
	
	public function __construct(EiField $eiField, CriteriaProperty $criteriaProperty, 
			\Closure $entityValueBuilder = null, \Closure $mappableValueBuilder = null) {
		$this->eiField = $eiField;
		$this->criteriaProperty = $criteriaProperty;
		$this->entityValueBuilder = $entityValueBuilder;
		$this->mappableValueBuilder = $mappableValueBuilder;
	}

	public function getLabelLstr(): Lstr {
		return $this->eiField->getLabelLstr();
	}
	
	public function getEiFieldPath(): EiFieldPath {
		return EiFieldPath::from($this->eiField);
	}
	
	public function buildCriteriaItem(CriteriaProperty $alias): CriteriaItem {
		return CrIt::p($alias, $this->criteriaProperty);
	}
	
	public function buildEntityValue(EiMapping $eiMapping) {
		return $this->mappableValueToEntityValue($eiMapping->getValue($this->eiField));
	}
	
	public function mappableValueToEntityValue($mappableValue) {
		if ($this->entityValueBuilder === null) {
			return $mappableValue;
		}
		
		return $this->entityValueBuilder->__invoke($mappableValue);
	}
	
	public function entityValueToMappableValue($entityValue) {
		if ($this->mappableValueBuilder === null) {
			return $entityValue;
		}
		
		return $this->mappableValueBuilder->__invoke($entityValue);
	}
}
