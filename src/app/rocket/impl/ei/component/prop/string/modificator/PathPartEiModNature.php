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

use rocket\impl\ei\component\mod\adapter\EiModNatureAdapter;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\entry\EiEntry;
use n2n\util\col\ArrayUtils;
use n2n\util\io\IoUtils;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\property\BasicEntityProperty;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\string\conf\PathPartConfig;
use rocket\impl\ei\component\prop\string\PathPartEiPropNature;
use rocket\ei\util\spec\EiuMask;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use rocket\ei\manage\generic\ScalarEiProperty;
use rocket\ei\manage\generic\GenericEiProperty;
use n2n\persistence\orm\property\EntityProperty;

class PathPartEiModNature extends EiModNatureAdapter {

	private ?ScalarEiProperty $baseScalarEiProperty = null;
	private ?GenericEiProperty $uniquePerGenericEiProperty = null;

	public function __construct(private EiPropPath $eiPropPath, private EiuMask $eiuMask,
			private EntityProperty $entityProperty, private bool $nullAllowed, ) {
	}
	
	public function setupEiEntry(Eiu $eiu) {
		$pathPartPurify = new PathPartPurifier($eiu->frame()->getEiFrame(), $eiu->entry()->getEiEntry(), 
				$this->eiPropPath, $this->eiuMask, $this->entityProperty, $this->nullAllowed,
				$this->baseScalarEiProperty, $this->uniquePerGenericEiProperty);
		$eiu->entry()->onWrite(function () use ($pathPartPurify) {
			$pathPartPurify->purify();
		});
	}

	public function setBaseScalarEiProperty(?ScalarEiProperty $baseScalarEiProperty) {
		$this->baseScalarEiProperty = $baseScalarEiProperty;
	}

	public function setUniquePerGenericEiProperty(?GenericEiProperty $uniqueGenericEiProperty) {
		$this->uniquePerGenericEiProperty = $uniqueGenericEiProperty;
	}
}

class PathPartPurifier {
	private $eiFrame;
	private $eiEntry;
	private $pathPartConfig;
	private $eiPropPath;
	private $eiuMask;
	
	public function __construct(EiFrame $eiFrame, EiEntry $eiEntry, EiPropPath $eiPropPath, EiuMask $eiuMask,
			private EntityProperty $entityProperty, private bool $nullAllowed, private ?ScalarEiProperty $baseScalarEiProperty,
			private ?GenericEiProperty $uniquePerGenericEiProperty) {
		$this->eiFrame = $eiFrame;
		$this->eiEntry = $eiEntry;
		$this->eiPropPath = $eiPropPath;
		$this->eiuMask = $eiuMask;
	}
	
	private function getIdEntityProperty(): BasicEntityProperty {
		return $this->eiuMask->type()->getEntityModel()->getIdDef()->getEntityProperty();
	}
	
	public function purify() {
		$value = $this->eiEntry->getValue($this->eiPropPath);
	
		if ($value !== null) {
			$this->eiEntry->setValue($this->eiPropPath, $this->uniquePathPart(IoUtils::stripSpecialChars($value)));
			return;
		}
	
		if ($this->eiEntry->isNew() || !$this->nullAllowed) {
			$this->eiEntry->setValue($this->eiPropPath, $this->uniquePathPart($this->generatePathPart()));
		} else {
			$this->eiEntry->setValue($this->eiPropPath, null);
		}
	}
	
	private function generatePathPart(): string {
		$baseScalarEiProperty = $this->baseScalarEiProperty;
		if ($baseScalarEiProperty === null) {
			$baseScalarEiProperty = ArrayUtils::first($this->eiuMask->engine()->getScalarEiProperties());
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
		$entityClass = $this->eiEntry->getEiMask()->getEiType()->getEntityModel()->getClass();
		$criteria = $this->eiFrame->getEiLaunch()->getEntityManager()->createCriteria();
		$criteria->select('COUNT(1)')
				->from($entityClass, 'e')
				->where()->match(CrIt::p('e', $this->entityProperty), '=', $pathPart)
				->andMatch(CrIt::p('e', $this->getIdEntityProperty()), '!=', $this->eiEntry->getId());
		
		if (null !== ($uniquePerGenericEiProperty = $this->uniquePerGenericEiProperty)) {
			$criteria->where()->match($uniquePerGenericEiProperty->createCriteriaItem(CrIt::p('e')),
					'=', $uniquePerGenericEiProperty->buildEntityValue($this->eiEntry));
		}
	
		return 0 < $criteria->toQuery()->fetchSingle();
	}
}
