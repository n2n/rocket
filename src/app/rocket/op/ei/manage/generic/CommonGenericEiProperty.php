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
namespace rocket\op\ei\manage\generic;

use n2n\l10n\Lstr;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\item\CriteriaItem;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\component\prop\EiPropNature;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\entry\UnknownEiFieldExcpetion;

class CommonGenericEiProperty implements GenericEiProperty {
	private $eiProp;
	private $criteriaProperty;
	private $entityValueBuilder;
	private $eiFieldValueBuilder;
	
	public function __construct(private EiPropPath $eiPropPath, private Lstr $labelLstr, CriteriaProperty $criteriaProperty,
			?\Closure $entityValueBuilder = null, ?\Closure $eiFieldValueBuilder = null) {
		$this->criteriaProperty = $criteriaProperty;
		$this->entityValueBuilder = $entityValueBuilder;
		$this->eiFieldValueBuilder = $eiFieldValueBuilder;
	}

	public function getLabelLstr(): Lstr {
		return $this->labelLstr;
	}
	
	public function getEiPropPath(): EiPropPath {
		return $this->eiPropPath;
	}
	
	public function createCriteriaItem(CriteriaProperty $alias): CriteriaItem {
		return CrIt::p($alias, $this->criteriaProperty);
	}

	/**
	 * @throws UnknownEiFieldExcpetion
	 */
	public function buildEntityValue(EiEntry $eiEntry) {
		return $this->eiFieldValueToEntityValue($eiEntry->getValue($this->eiPropPath));
	}
	
	public function eiFieldValueToEntityValue($eiFieldValue) {
		if ($this->entityValueBuilder === null) {
			return $eiFieldValue;
		}
		
		return $this->entityValueBuilder->__invoke($eiFieldValue);
	}
	
	public function entityValueToEiFieldValue($entityValue) {
		if ($this->eiFieldValueBuilder === null) {
			return $entityValue;
		}
		
		return $this->eiFieldValueBuilder->__invoke($entityValue);
	}
}
