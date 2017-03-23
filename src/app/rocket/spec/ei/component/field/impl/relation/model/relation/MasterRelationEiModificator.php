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
namespace rocket\spec\ei\component\field\impl\relation\model\relation;

use rocket\spec\ei\component\modificator\impl\adapter\EiModificatorAdapter;
use rocket\spec\ei\manage\EiFrame;
use n2n\reflection\property\AccessProxy;
use rocket\spec\ei\manage\mapping\WrittenMappingListener;
use rocket\spec\ei\manage\util\model\Eiu;

class MasterRelationEiModificator extends EiModificatorAdapter {
	private $targetEiFrame;
	private $entityObj;
	private $propertyAccessProxy;
	private $targetMany;

	public function __construct(EiFrame $targetEiFrame, $entityObj, AccessProxy $propertyAccessProxy, $targetMany) {
		$this->targetEiFrame = $targetEiFrame;
		$this->entityObj = $entityObj;
		$this->propertyAccessProxy = $propertyAccessProxy;
		$this->targetMany = (boolean) $targetMany;
	}

	public function setupEiMapping(Eiu $eiu) {
		if ($this->targetEiFrame !== $eiu->frame()->getEiFrame()) return;

		if ($eiu->entry()->isDraft()) return;
		
		$eiMapping = $eiu->entry()->getEiMapping();
		$that = $this;
		if (!$this->targetMany) {
			$eiMapping->registerListener(new WrittenMappingListener(
					function () use ($that, $eiMapping) {
						$that->propertyAccessProxy->setValue($that->entityObj, $eiMapping->getEiEntry()->getLiveObject());
					}));
			return;
		}

		
		$eiMapping->registerListener(new WrittenMappingListener(
				function () use ($that, $eiMapping) {
					$targetEntities = $that->propertyAccessProxy->getValue($that->entityObj);
					if ($targetEntities === null) {
						$targetEntities = new \ArrayObject();
					}
					$targetEntities[] = $eiMapping->getEiEntry()->getLiveObject();
					$that->propertyAccessProxy->setValue($that->entityObj, $targetEntities);
				}));
	}
}
