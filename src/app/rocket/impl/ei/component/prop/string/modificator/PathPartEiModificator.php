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
namespace rocket\impl\ei\component\prop\string\modificator;

use rocket\impl\ei\component\modificator\adapter\EiModificatorAdapter;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\entry\EiEntry;
use rocket\impl\ei\component\prop\string\PathPartEiProp;
use n2n\util\col\ArrayUtils;
use n2n\io\IoUtils;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\property\BasicEntityProperty;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;

class PathPartEiModificator extends EiModificatorAdapter {
	private $pathPartEiProp;
	
	public function __construct(PathPartEiProp $pathPartEiProp) {
		$this->pathPartEiProp = $pathPartEiProp;
	}
	
	public function setupEiEntry(Eiu $eiu) {
		$pathPartPurify = new PathPartPurifier($eiu->frame()->getEiFrame(), $eiu->entry()->getEiEntry(), 
				$this->pathPartEiProp);
		$eiu->entry()->onWrite(function () use ($pathPartPurify) {
			$pathPartPurify->purify();
		});
	}	
}

class PathPartPurifier {
	private $eiFrame;
	private $eiEntry;
	private $pathPartEiProp;
	
	public function __construct(EiFrame $eiFrame, EiEntry $eiEntry, PathPartEiProp $pathPartEiProp) {
		$this->eiFrame = $eiFrame;
		$this->eiEntry = $eiEntry;
		$this->pathPartEiProp = $pathPartEiProp;
	}
	
	private function getIdEntityProperty(): BasicEntityProperty {
		return $this->pathPartEiProp->getEiMask()->getEiType()->getEntityModel()->getIdDef()->getEntityProperty();
	}
	
	public function purify() {
		$value = $this->eiEntry->getValue(EiPropPath::create($this->pathPartEiProp));
	
		if ($value !== null) {
			$this->eiEntry->setValue(EiPropPath::create($this->pathPartEiProp), $this->uniquePathPart(IoUtils::stripSpecialChars($value)));
			return;
		}
	
		if ($this->eiEntry->isNew() || !$this->pathPartEiProp->isNullAllowed()) {
			$this->eiEntry->setValue(EiPropPath::create($this->pathPartEiProp), $this->uniquePathPart($this->generatePathPart()));
		} else {
			$this->eiEntry->setValue(EiPropPath::create($this->pathPartEiProp), null);
		}
	}
	
	private function generatePathPart(): string {
		$baseScalarEiProperty = $this->pathPartEiProp->getBaseScalarEiProperty();
		if ($baseScalarEiProperty === null) {
			$baseScalarEiProperty = ArrayUtils::first($this->pathPartEiProp
					->getEiMask()->getEiEngine()->getScalarEiDefinition()->getMap()->getValues());
		}
	
		if ($baseScalarEiProperty !== null) {
			return mb_strtolower(IoUtils::stripSpecialChars($baseScalarEiProperty->eiFieldValueToScalarValue(
					$this->eiEntry->getValue($baseScalarEiProperty->getEiPropPath()))));
		}
	
		if (null !== ($id = $this->eiEntry->getId())) {
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
		$entityClass = $this->pathPartEiProp->getEiMask()->getEiType()->getEntityModel()->getClass();
		$criteria = $this->eiFrame->getManageState()->getEntityManager()->createCriteria();
		$criteria->select('COUNT(1)')
				->from($entityClass, 'e')
				->where()->match(CrIt::p('e', $this->pathPartEiProp->getEntityProperty(true)), '=', $pathPart)
				->andMatch(CrIt::p('e', $this->getIdEntityProperty()), '!=', $this->eiEntry->getId());
		
		if (null !== ($uniquePerGenericEiProperty = $this->pathPartEiProp->getUniquePerGenericEiProperty())) {
			$criteria->where()->match($uniquePerGenericEiProperty->createCriteriaItem(CrIt::p('e')),
					'=', $uniquePerGenericEiProperty->buildEntityValue($this->eiEntry));
		}
	
		return 0 < $criteria->toQuery()->fetchSingle();
	}
}
