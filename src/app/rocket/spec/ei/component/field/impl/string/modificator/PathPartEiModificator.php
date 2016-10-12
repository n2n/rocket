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
namespace rocket\spec\ei\component\field\impl\string\modificator;

use rocket\spec\ei\component\modificator\impl\EiModificatorAdapter;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\component\field\impl\string\PathPartEiField;
use n2n\util\col\ArrayUtils;
use rocket\spec\ei\EiUtils;
use n2n\io\IoUtils;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\property\BasicEntityProperty;

class PathPartEiModificator extends EiModificatorAdapter {
	private $pathPartEiField;
	
	public function __construct(PathPartEiField $pathPartEiField) {
		$this->pathPartEiField = $pathPartEiField;
	}
	
	public function setupEiMapping(EiState $eiState, EiMapping $eiMapping) {
		$pathPartPurify = new PathPartPurifier($eiState, $eiMapping, $this->pathPartEiField);
		$eiMapping->executeOnWrite(function () use ($pathPartPurify) {
			$pathPartPurify->purify();
		});
	}	
}

class PathPartPurifier {
	private $eiState;
	private $eiMapping;
	private $pathPartEiField;
	
	public function __construct(EiState $eiState, EiMapping $eiMapping, PathPartEiField $pathPartEiField) {
		$this->eiState = $eiState;
		$this->eiMapping = $eiMapping;
		$this->pathPartEiField = $pathPartEiField;
	}
	
	private function getIdEntityProperty(): BasicEntityProperty {
		return $this->pathPartEiField->getEiEngine()->getEiThing()->getEntityModel()->getIdDef()->getEntityProperty();
	}
	
	public function purify() {
		$value = $this->eiMapping->getValue($this->pathPartEiField);
	
		if ($value !== null) {
			$this->eiMapping->setValue($this->pathPartEiField, $this->uniquePathPart(IoUtils::stripSpecialChars($value)));
			return;
		}
	
		if ($this->eiMapping->isNew() || !$this->pathPartEiField->isNullAllowed()) {
			$this->eiMapping->setValue($this->pathPartEiField, $this->uniquePathPart($this->generatePathPart()));
		} else {
			$this->eiMapping->setValue($this->pathPartEiField, null);
		}
	}
	
	private function generatePathPart(): string {
		$baseScalarEiProperty = $this->pathPartEiField->getBaseScalarEiProperty();
		if ($baseScalarEiProperty === null) {
			$baseScalarEiProperty = ArrayUtils::first($this->pathPartEiField
					->getEiEngine()->getScalarEiDefinition()->getScalarEiProperties()->getValues());
		}
	
		if ($baseScalarEiProperty !== null) {
			return mb_strtolower(IoUtils::stripSpecialChars($baseScalarEiProperty->buildScalarValue($this->eiMapping)));
		}
	
		if (null !== ($id = $this->eiMapping->getId())) {
			return mb_strtolower(IoUtils::stripSpecialChars($this->getIdEntityProperty()->valueToRep($id)));
		}
	
		return IoUtils::stripSpecialChars(uniqid());
	}
	
	private function uniquePathPart(string $pathPart): string {
		$uniquePathPart = $pathPart;
		for ($i = 2; $this->containsPathPart($uniquePathPart); $i++) {
			$uniquePathPart = $pathPart . '-' . $i;
		}
		return $uniquePathPart;
	}
	
	private function containsPathPart(string $pathPart): bool {
		$criteria = $this->eiState->createCriteria('e', CriteriaConstraint::ALL_TYPES);
		$criteria->select('COUNT(1)')
				->where()->match(CrIt::p('e', $this->pathPartEiField->getEntityProperty()), '=', $pathPart)
				->andMatch(CrIt::p('e', $this->getIdEntityProperty()), '!=', $this->eiMapping->getId());
	
		
		if (null !== ($uniquePerGenericEiProperty = $this->pathPartEiField->getUniquePerGenericEiProperty())) {
			$criteria->where()->match($uniquePerGenericEiProperty->buildCriteriaItem(CrIt::p('e')),
					'=', $uniquePerGenericEiProperty->buildEntityValue($this->eiMapping));
		}
	
		return 0 < $criteria->toQuery()->fetchSingle();
	}
}
