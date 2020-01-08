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
namespace rocket\ei\manage\frame;

use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\ei\manage\entry\UnknownEiObjectException;
use rocket\ei\manage\EiEntityObj;
use n2n\persistence\orm\store\EntityInfo;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\EiObject;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\persistence\orm\criteria\Criteria;
use rocket\ei\manage\gui\EiGuiFrame;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\ei\manage\LiveEiObject;
use rocket\ei\manage\gui\EiEntryGuiMulti;
use rocket\ei\EiException;
use rocket\ei\UnknownEiTypeException;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\security\SecurityException;
use rocket\ei\manage\gui\EiGui;

class EiFrameUtil {
	private $eiFrame;
	
	/**
	 * @param EiFrame $eiFrame
	 */
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
	}
	
	/**
	 * @param string $pid
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	function pidToId(string $pid) {
		return $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->pidToId($pid);
	}
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return boolean
	 */
	function containsId($id, int $ignoreConstraintTypes = 0) {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select(CrIt::c('1'));
		$this->applyIdComparison($criteria->where(), $id);
		
		return null !== $criteria->toQuery()->fetchSingle();
	}
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @throws UnknownEiObjectException
	 * @return \rocket\ei\manage\LiveEiObject
	 */
	function lookupEiObject($id, int $ignoreConstraintTypes = 0) {
		return new LiveEiObject($this->lookupEiEntityObj($id, $ignoreConstraintTypes));
	}
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @throws UnknownEiObjectException
	 * @return \rocket\ei\manage\EiEntityObj
	 */
	function lookupEiEntityObj($id, int $ignoreConstraintTypes = 0) {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select('e');
		$this->applyIdComparison($criteria->where(), $id);
		
		if (null !== ($entityObj = $criteria->toQuery()->fetchSingle())) {
			return EiEntityObj::createFrom($this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), $entityObj);
		}
		
		throw new UnknownEiObjectException('Entity not found: ' . EntityInfo::buildEntityString(
				$this->getContextEiType()->getEntityModel(), $id));
	}
	
	/**
	 * @param object $entityObj
	 * @return \rocket\ei\manage\EiEntityObj
	 */
	function createEiEntityObj(object $entityObj) {
		return EiEntityObj::createFrom($this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), $entityObj);
	}
	
	private function applyIdComparison(CriteriaComparator $criteriaComparator, $id) {
		$criteriaComparator->match(CrIt::p('e', $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()
				->getEntityModel()->getIdDef()->getEntityProperty()), CriteriaComparator::OPERATOR_EQUAL, $id);
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiEntry[]
	 */
	function createPossibleNewEiEntries() {
		$contextEiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(); 
		
		$newEiEntries = [];
		
		if (!$contextEiType->isAbstract()) {
			$newEiEntries[$contextEiType->getId()] = $this->eiFrame
					->createEiEntry($contextEiType->createNewEiObject());
		}
		
		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
			if ($eiType->isAbstract()) {
				continue;
			}
			
			$newEiEntries[$eiType->getId()] = $this->eiFrame
					->createEiEntry($eiType->createNewEiObject());
		}
		
		return $newEiEntries;
	}
	
	/**
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @return \rocket\ei\manage\gui\EiEntryGuiMulti
	 * @throws EiException
	 */
	function createNewEiEntryGuiMulti(bool $bulky, bool $readOnly) {
		$viewMode = ViewMode::determine($bulky, $readOnly, true);
		
		$newEiEntryGuis = [];
		
		foreach ($this->createPossibleNewEiEntries() as $eiTypeId => $newEiEntry) {
			$newEiGuiFrame = $newEiEntry->getEiMask()->getEiEngine()->createFramedEiGuiFrame($this->eiFrame, $viewMode);
			$newEiEntryGuis[$eiTypeId] = $newEiGuiFrame->createEiEntryGui($newEiEntry);
		}
		
		if (empty($newEiEntryGuis)) {
			throw new EiException('Can not create a new EiEntryGui of ' 
					. $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()
					. ' because this type is abstract and doesn\'t have any sub EiTypes.');
		}
		
		return new EiEntryGuiMulti($this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), 
				ViewMode::determine($bulky, $readOnly, true), $newEiEntryGuis);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @throws SecurityException
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	function createEiEntryGuiFromEiObject(EiObject $eiObject, bool $bulky, bool $readOnly) {
		$eiEntry = $this->eiFrame->createEiEntry($eiObject);
		$eiGuiFrame = $eiEntry->getEiMask()->getEiEngine()->createFramedEiGuiFrame($this->eiFrame, 
				ViewMode::determine($bulky, $readOnly, $eiObject->isNew()));
		return $eiGuiFrame->createEiEntryGui($eiEntry);
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	function createEiEntryGui(EiEntry $eiEntry, bool $bulky, bool $readOnly) {
		$eiGuiFrame = $eiEntry->getEiMask()->getEiEngine()->createFramedEiGuiFrame($this->eiFrame,
				ViewMode::determine($bulky, $readOnly, $eiEntry->isNew()));
		return $eiGuiFrame->createEiEntryGui($eiEntry);
	}
	
	/**
	 * @param int $ignoreConstraintTypes
	 * @return int
	 */
	function count(int $ignoreConstraintTypes = 0) {
		return $this->eiFrame->createCriteria('e', $ignoreConstraintTypes)
				->select('COUNT(1)')->toQuery()->fetchSingle();
	}
	
	/**
	 * @param int $from
	 * @param int $num
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	function lookupEiGuiFromRange(int $offset, int $num, bool $bulky, bool $readOnly) {
		$guiDefinition = $this->eiFrame->getManageState()->getDef()->getGuiDefinition(
				$this->eiFrame->getContextEiEngine()->getEiMask());
		$eiGui = $guiDefinition->createEiGui($this->eiFrame, ViewMode::determine($bulky, $readOnly, false));
			
		$criteria = $this->eiFrame->createCriteria(NestedSetUtils::NODE_ALIAS, false);
		$criteria->select(NestedSetUtils::NODE_ALIAS);
		$criteria->limit($offset, $num);
		
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		if (null !== ($nestedSetStrategy = $eiType->getNestedSetStrategy())) {
			$this->treeLookup($eiGui, $criteria, $eiType->getEntityModel()->getClass(), $nestedSetStrategy);
		} else {
			$this->simpleLookup($eiGui, $criteria);
		}
			
		return $eiGui;		
	}
		
	/**
	 * @param object $entityObj
	 * @return \rocket\ei\manage\LiveEiObject
	 */
	private function createEiObject(object $entityObj) {
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		
		return LiveEiObject::create($eiType, $entityObj);
	}
	
	/**
	 * @param EiGui $eiGui
	 * @param Criteria $criteria
	 */
	private function simpleLookup(EiGui $eiGui, Criteria $criteria) {
		$eiGuiFrame = $eiGui->getEiGuiFrame();
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$eiGui->addEiEntryGui($eiGuiFrame->createEiEntryGui(
					$this->eiFrame->createEiEntry($this->createEiObject($entityObj))));
		}
	}
		
	/**
	 * @param EiGuiFrame $eiuGuiFrame
	 * @param Criteria $criteria
	 * @param \ReflectionClass $class
	 * @param NestedSetStrategy $nestedSetStrategy
	 */
	private function treeLookup(EiGui $eiGui, Criteria $criteria, \ReflectionClass $class, 
			NestedSetStrategy $nestedSetStrategy) {
		$nestedSetUtils = new NestedSetUtils($this->eiFrame->getManageState()->getEntityManager(), 
				$class, $nestedSetStrategy);
		
		$eiGuiFrame = $eiGui->getEiGuiFrame();
		
		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$eiGui->addEiEntryGui($eiGuiFrame->createEiEntryGui($this->eiFrame->createEiEntry(
							$this->createEiObject($nestedSetItem->getEntityObj())), 
					$nestedSetItem->getLevel()));
		}
	}
	
	/**
	 * @param string $eiTypeId
	 * @return \rocket\ei\manage\EiObject
	 * @throws UnknownEiTypeException
	 */
	function createNewEiObject(string $eiTypeId) {
		$contextEiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		
		if ($contextEiType->getId() == $eiTypeId) {
			return $contextEiType->createNewEiObject(false);
		}
		
		return $contextEiType->getSubEiTypeById($eiTypeId, true)->createNewEiObject(false);
	}
}
