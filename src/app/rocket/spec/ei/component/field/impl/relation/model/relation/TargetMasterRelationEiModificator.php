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

use rocket\spec\ei\component\modificator\impl\EiModificatorAdapter;
use rocket\spec\ei\manage\EiState;
use n2n\reflection\property\AccessProxy;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\mapping\WrittenMappingListener;
use rocket\spec\ei\manage\mapping\EiMappingListener;
use rocket\spec\ei\manage\mapping\MappingListenerAdapter;

class TargetMasterRelationEiModificator extends EiModificatorAdapter {
	private $eiFieldRelation;
	private $removeFromMaster;

	public function __construct(EiFieldRelation $eiFieldRelation, bool $removeFromMaster) {
		$this->eiFieldRelation = $eiFieldRelation;
		$this->removeFromMaster = $removeFromMaster;
	}
	
	public function setRemoveFromMaster(bool $removeFromMaster) {
		$this->removeFromMaster = $removeFromMaster;
	}

	public function setupEiMapping(EiState $eiState, EiMapping $eiMapping) {
		if ($eiMapping->getEiSelection()->isDraft()) return;
		
		$that = $this;
		$eiMapping->registerListener(new TargetMasterEiMappingListener($this->eiFieldRelation, $this->removeFromMaster));
	}
}

class TargetMasterEiMappingListener extends MappingListenerAdapter {
	private $eiFieldRelation;
	private $removeFromMaster;
	private $accessProxy;
	
	private $oldValue;
	
	public function __construct(EiFieldRelation $eiFieldRelation, bool $removeFromMaster) {
		$this->eiFieldRelation = $eiFieldRelation;
		$this->removeFromMaster = $removeFromMaster;
		$this->accessProxy = $this->eiFieldRelation->getRelationEiField()->getObjectPropertyAccessProxy();
	}
	
	public function onWrite(EiMapping $eiMapping) {
		$this->oldValue = $this->accessProxy->getValue($eiMapping->getEiSelection()->getLiveObject());
	}
	
	public function written(EiMapping $eiMapping) {
		$entityObj = $eiMapping->getEiSelection()->getLiveObject();
		
		if ($this->eiFieldRelation->isTargetMany()) {
			$this->writeToMany($entityObj);
		} else {
			$this->writeToOne($entityObj);
		}
	}
	
	private function writeToOne($entityObj) {
		$oldTargetEntityObj = $this->oldValue;
		$targetEntityObj = $this->accessProxy->getValue($entityObj);
		
		if ($this->removeFromMaster && $oldTargetEntityObj !== null && $oldTargetEntityObj !== $targetEntityObj) {
			$this->removeFromMaster($oldTargetEntityObj);
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
		
		if (!$this->removeFromMaster) return;
		
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
		$targetAccessProxy = $this->eiFieldRelation->getTargetMasterEiField()->getObjectPropertyAccessProxy();
	
		if (!$this->eiFieldRelation->isSourceMany()) {
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
		$targetAccessProxy = $this->eiFieldRelation->getTargetMasterEiField()->getObjectPropertyAccessProxy();
	
		if (!$this->eiFieldRelation->isSourceMany()) {
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
