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
namespace rocket\spec\ei\component\command\impl\tree\model;

use n2n\persistence\orm\NestedSetUtils;
use n2n\dispatch\Dispatchable;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\map\val\impl\ValEnum;
use n2n\reflection\annotation\AnnoInit;
use n2n\dispatch\DispatchAnnotations;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\EiSelection;

class TreeMoveModel implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->m('move', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $eiSpec;
	private $eiState;
	private $eiSelection;
	private $nestedSetUtils;
	
	private $nestedSetItems;
	public $parentId;
	private $parentIdOptions;
	
	public function __construct(EiState $eiState) {
		$this->eiSpec = $eiState->getContextEiMask()->getEiEngine()->getEiSpec();
		$this->eiState = $eiState;
	}
	
	public function initialize($id) {
		$em = $this->eiState->getEntityManager();
		$class = $this->eiSpec->getEntityModel()->getClass();
		
		$object = $em->find($class, $id);
		if (!isset($object)) {
			return false;
		}
		
		$this->nestedSetUtils = $nestedSetUtils = new NestedSetUtils($em, $class);
		$this->eiSelection = new EiSelection($id, $object);
		$this->eiState->setEiSelection($this->eiSelection);
		
		$this->nestedSetItems = array();
		$this->parentIdOptions = array(null => 'Root');
		$currentLevelObjectIds = array();
		$disabledLevel = null;
		foreach ($nestedSetUtils->fetch() as $nestedSetItem) {
			$objectId = $this->eiSpec->extractId($nestedSetItem->getObject());
			$level = $nestedSetItem->getLevel();
			
			if (isset($disabledLevel)) {
				if ($level > $disabledLevel) {
					continue;
				}
				$disabledLevel = null;
			}
			
			if ($id == $objectId) {
				$disabledLevel = $level;
				
				if (isset($currentLevelObjectIds[$level - 1])) {
					$this->parentId = $currentLevelObjectIds[$level - 1];
				}
				
				continue;
			}
			
			$currentLevelObjectIds[$level] = $objectId;
			$this->nestedSetItems[$objectId] = $nestedSetItem;
			$this->parentIdOptions[$objectId] = str_repeat('..', $level + 1) . 
					$this->eiSpec->createIdentityString($nestedSetItem->getObject(), $this->eiState->getN2nLocale());
		}
				
		return true;		
	}
			
	public function getEiSpec() {
		return $this->eiSpec;
	}
	
	public function getEiState() {
		return $this->eiState;
	}
	
	public function getParentIdOptions() {
		return $this->parentIdOptions;
	}
	
	public function getTitle() {
		return $this->eiSpec->createIdentityString($this->eiSelection->getLiveEntityObj(), 
				$this->eiState->getN2nLocale());
	}
	
	private function _validation(BindingConstraints $bc) {
		$bc->val('parentId', new ValEnum(array_keys($this->parentIdOptions)));
	}
	
	public function move() {
		$parentObject = null;
		if (isset($this->nestedSetItems[$this->parentId])) {
			$parentObject = $this->nestedSetItems[$this->parentId]->getObject();
		}
		$this->nestedSetUtils->move($this->eiSelection->getLiveEntityObj(), $parentObject);
	}
}
