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
namespace rocket\impl\ei\component\prop\relation\model\relation;

use rocket\impl\ei\component\modificator\adapter\EiModificatorAdapter;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\entry\EiEntryListenerAdapter;
use rocket\ei\util\Eiu;

class TargetMasterRelationEiModificator extends EiModificatorAdapter {
	private $eiPropRelation;

	public function __construct(EiPropRelation $eiPropRelation) {
		$this->eiPropRelation = $eiPropRelation;
	}

	public function setupEiEntry(Eiu $eiu) {
		$eiEntry = $eiu->entry()->getEiEntry();
		if ($eiEntry->getEiObject()->isDraft()) return;
		
		$that = $this;
		$eiEntry->registerListener(new TargetMasterEiEntryListener($this->eiPropRelation));
	}
}

class TargetMasterEiEntryListener extends EiEntryListenerAdapter {
	private $eiPropRelation;
	private $accessProxy;
	private $orphanRemoval;
	
	private $oldValue;
	
	public function __construct(EiPropRelation $eiPropRelation) {
		$this->eiPropRelation = $eiPropRelation;
		$this->accessProxy = $this->eiPropRelation->getRelationEiProp()->getObjectPropertyAccessProxy();
		$this->orphanRemoval = $this->eiPropRelation->getRelationEntityProperty()->getRelation()->isOrphanRemoval();
	}
	
	public function onWrite(EiEntry $eiEntry) {
		$this->oldValue = $this->accessProxy->getValue($eiEntry->getEiObject()->getLiveObject());
	}
	
	public function written(EiEntry $eiEntry) {
		$entityObj = $eiEntry->getEiObject()->getLiveObject();
		
		if ($this->eiPropRelation->isTargetMany()) {
			$this->writeToMany($entityObj);
		} else {
			$this->writeToOne($entityObj);
		}
	}
	
	private function writeToOne($entityObj) {
		$oldTargetEntityObj = $this->oldValue;
		$targetEntityObj = $this->accessProxy->getValue($entityObj);
		
		if (!$this->orphanRemoval && $oldTargetEntityObj !== null && $oldTargetEntityObj !== $targetEntityObj) {
			$this->removeFromMaster($entityObj, $oldTargetEntityObj);
		}
		
		$this->writeToMaster($entityObj, $targetEntityObj);
	}
	
	private function writeToMany($entityObj) {
		$targetEntityObjs = $this->accessProxy->getValue($entityObj);
		if ($targetEntityObjs === null) {
			$targetEntityObjs = array();
		}
		
		foreach ($targetEntityObjs as $targetEntityObj) {
			$this->writeToMaster($entityObj, $targetEntityObj);
		}
		
		if ($this->orphanRemoval) return;
		
		$obsoleteTargetEntityObjs = array();
		if ($this->oldValue !== null) {
			$obsoleteTargetEntityObjs = $this->oldValue->getArrayCopy();
		}
		
		foreach ($targetEntityObjs as $targetEntityObj) {
			foreach ($obsoleteTargetEntityObjs as $key => $oldTargetEntityObj) {
				if ($targetEntityObj === $oldTargetEntityObj) {
					unset($obsoleteTargetEntityObjs[$key]);
				}
			}
		}
	
		foreach ($obsoleteTargetEntityObjs as $obsoleteTargetEntityObj) {
			$this->removeFromMaster($entityObj, $obsoleteTargetEntityObj);
		}
	}
	
	private function writeToMaster($entityObj, $targetEntityObj) {
		$targetAccessProxy = $this->eiPropRelation->getTargetMasterAccessProxy();
	
		if (!$this->eiPropRelation->isSourceMany()) {
			$targetAccessProxy->setValue($targetEntityObj, $entityObj);
			return;
		}
	
		$sourceEntityObjs = $targetAccessProxy->getValue($targetEntityObj);
		if ($sourceEntityObjs === null) {
			$sourceEntityObjs = new \ArrayObject();
		}
	
		foreach ($sourceEntityObjs as $sourceEntityObj) {
			if ($sourceEntityObj === $entityObj) return;
		}
	
		$sourceEntityObjs[] = $entityObj;
		$targetAccessProxy->setValue($targetEntityObj, $sourceEntityObjs);
	}
	
	private function removeFromMaster($entityObj, $targetEntityObj) {
		$targetAccessProxy = $this->eiPropRelation->getTargetMasterAccessProxy();
	
		if (!$this->eiPropRelation->isSourceMany()) {
			if ($entityObj === $targetAccessProxy->getValue($targetEntityObj)) {
				$targetAccessProxy->setValue($targetEntityObj, null);
			}
				
			return;
		}
	
		$sourceEntityObjs = $targetAccessProxy->getValue($targetEntityObj);
		if ($sourceEntityObjs === null) {
			$sourceEntityObjs = new \ArrayObject();
		}
	
		foreach ($sourceEntityObjs as $key => $sourceEntityObj) {
			if ($sourceEntityObj === $entityObj) {
				$sourceEntityObjs->offsetUnset($key);
				$targetAccessProxy->setValue($targetEntityObj, $sourceEntityObjs);
			}
		}
	}
	
}
