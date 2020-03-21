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
use n2n\l10n\N2nLocale;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\si\content\SiEntry;
use rocket\si\meta\SiDeclaration;
use n2n\util\ex\IllegalStateException;
use rocket\si\content\SiEntryIdentifier;
use n2n\util\type\ArgUtils;

class EiFrameUtil {
	private $eiFrame;
	
	/**
	 * @param EiFrame $eiFrame
	 */
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
	}
	
	/**
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	function getEiFrame() {
		return $this->eiFrame;
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
	function createPossibleNewEiEntries(array $eiTypeIds = null) {
		$contextEiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(); 
		
		$newEiEntries = [];
		
		if (!$contextEiType->isAbstract() && ($eiTypeIds === null || in_array($contextEiType->getId(), $eiTypeIds))) {
			$newEiEntries[$contextEiType->getId()] = $this->eiFrame
					->createEiEntry($contextEiType->createNewEiObject());
		}
		
		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
			if ($eiType->isAbstract() && ($eiTypeIds === null || in_array($eiType->getId(), $eiTypeIds))) {
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
	 * @return EiEntryGuiMultiResult
	 * @throws EiException
	 */
	function createNewEiEntryGuiMulti(bool $bulky, bool $readOnly, ?array $guiPropPaths, ?array $eiTypeIds, bool $eiGuiRequired) {
		$viewMode = ViewMode::determine($bulky, $readOnly, true);
		
		$newEiEntryGuis = [];
		$eiGuiFrames = [];
		$eiGuis = $eiGuiRequired ? [] : null;
		
		foreach ($this->createPossibleNewEiEntries($eiTypeIds) as $eiTypeId => $newEiEntry) {
			$newEiGuiFrame = null;
			if (!$eiGuiRequired) {
				$eiGuiFrames[$eiTypeId] = $newEiGuiFrame = $this->createEiGuiFrame($newEiEntry->getEiMask(), $viewMode, $guiPropPaths);
			} else {
				$eiGuis[$eiTypeId] = $eiGui = $this->createEiGui($newEiEntry->getEiMask(), $viewMode, $guiPropPaths);
				$eiGuiFrames[$eiTypeId] = $newEiGuiFrame = $eiGui->getEiGuiFrame();
			}
			
			$newEiEntryGuis[$eiTypeId] = $newEiGuiFrame->createEiEntryGuiVariation($this->eiFrame, $newEiEntry);
		}
		
		if (empty($newEiEntryGuis)) {
			throw new EiException('Can not create a new EiEntryGui of ' 
					. $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()
					. ' because this type is abstract and doesn\'t have any sub EiTypes.');
		}
		
		$eiEntryGuiMulti = new EiEntryGuiMulti($this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), 
				$viewMode, $newEiEntryGuis);
		
		return new EiEntryGuiMultiResult($eiEntryGuiMulti, $this->eiFrame, $eiGuiFrames, $eiGuis);
	}
	
	/**
	 * @param EiMask $eiMask
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @param array $guiPropPaths
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	private function createEiGuiFrame(EiMask $eiMask, int $viewMode, array $guiPropPaths = null) {
		$guiDefinition = $this->eiFrame->getManageState()->getDef()->getGuiDefinition($eiMask);
		
		
		if ($guiPropPaths === null) {
			return $guiDefinition->createEiGui($this->eiFrame->getN2nContext(), $viewMode)->getEiGuiFrame();
		} else {
			return $guiDefinition->createEiGuiFrame($this->eiFrame->getN2nContext(), $viewMode, $guiPropPaths);
		}
	}
	
	/**
	 * @param EiMask $eiMask
	 * @param int $viewMode
	 * @param array $guiPropPaths
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	private function createEiGui(EiMask $eiMask, int $viewMode, array $guiPropPaths = null) {
		$guiDefinition = $this->eiFrame->getManageState()->getDef()->getGuiDefinition($eiMask);
		
		return $guiDefinition->createEiGui($this->eiFrame->getN2nContext(), $viewMode, $guiPropPaths);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @throws SecurityException
	 * @return EiEntryGuiResult
	 */
	function createEiEntryGuiFromEiObject(EiObject $eiObject, bool $bulky, bool $readOnly, ?array $guiPropPaths, 
			bool $eiGuiRequired) {
		$eiEntry = $this->eiFrame->createEiEntry($eiObject);
		$viewMode = ViewMode::determine($bulky, $readOnly, $eiObject->isNew());
		$eiGui = null;
		$eiGuiFrame = null;
		if (!$eiGuiRequired) {
			$eiGuiFrame = $this->createEiGuiFrame($eiEntry->getEiMask(), $viewMode, $guiPropPaths);
		} else {
			$eiGui = $this->createEiGui($eiEntry->getEiMask(), $viewMode, $guiPropPaths);
			$eiGuiFrame = $eiGui->getEiGuiFrame();
		}
		
		return new EiEntryGuiResult($eiGuiFrame->createEiEntryGui($this->eiFrame, $eiEntry), $this->eiFrame, 
				$eiGuiFrame, $eiGui);
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @return EiEntryGuiResult
	 */
	function createEiEntryGui(EiEntry $eiEntry, bool $bulky, bool $readOnly, ?array $guiPropPaths,
			bool $eiGuiRequired) {
		$viewMode = ViewMode::determine($bulky, $readOnly, $eiEntry->isNew());
		$eiGui = null;
		$eiGuiFrame = null;
		if (!$eiGuiRequired) {
			$eiGuiFrame = $this->createEiGuiFrame($eiEntry->getEiMask(), $viewMode, $guiPropPaths);
		} else {
			$eiGui = $this->createEiGui($eiEntry->getEiMask(), $viewMode, $guiPropPaths);
			$eiGuiFrame = $eiGui->getEiGuiFrame();
		}
		return new EiEntryGuiResult($eiGuiFrame->createEiEntryGui($this->eiFrame, $eiEntry), $this->eiFrame,
				$eiGuiFrame, $eiGui);
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
	function lookupEiGuiFromRange(int $offset, int $num, bool $bulky, bool $readOnly, array $guiPropPaths = null) {
		$guiDefinition = $this->eiFrame->getManageState()->getDef()->getGuiDefinition(
				$this->eiFrame->getContextEiEngine()->getEiMask());
		$eiGui = $guiDefinition->createEiGui($this->eiFrame->getN2nContext(), ViewMode::determine($bulky, $readOnly, false), $guiPropPaths);
			
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
			$eiGui->addEiEntryGui($eiGuiFrame->createEiEntryGui($this->eiFrame,
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
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	function createIdentityString(EiObject $eiObject, bool $determineEiMask = true, N2nLocale $n2nLocale = null) {
		$eiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		if ($determineEiMask) {
			$eiMask = $eiMask->determineEiMask($eiObject->getEiEntityObj()->getEiType());
		}
		
		$n2nContext = $this->eiFrame->getN2nContext();
		return $this->eiFrame->getManageState()->getDef()->getIdNameDefinition($eiMask)
				->createIdentityString($eiObject, $n2nContext, $n2nLocale ?? $this->eiFrame->getN2nContext()->getN2nLocale());
	}
}


class EiEntryGuiResult {
	private $eiEntryGui;
	private $eiFrame;
	private $eiGuiFrame;
	private $eiGui;
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 * @param EiGuiFrame $eiGuiFrame
	 */
	function __construct(EiEntryGui $eiEntryGui, EiFrame $eiFrame, EiGuiFrame $eiGuiFrame, ?EiGui $eiGui) {
		$this->eiEntryGui = $eiEntryGui;
		$this->eiFrame = $eiFrame;
		$this->eiGuiFrame = $eiGuiFrame;
		$this->eiGui = $eiGui;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	function getEiEntryGui() {
		return $this->eiEntryGui;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	function getEiGuiFrame() {
		return $this->eiGuiFrame;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGui|null
	 */
	function getEiGui() {
		return $this->eiGui;
	}
	
	/**
	 * @param bool $controlsIncluded
	 * @return SiEntry
	 */
	function createSiEntry(bool $controlsIncluded) {
		return $this->eiGuiFrame->createSiEntry($this->eiFrame, $this->eiEntryGui);
	}
	
	/**
	 * @param EiEntryGui[]
	 * @return SiDeclaration
	 */
	function createSiDeclaration() {
		IllegalStateException::assertTrue($this->eiGui !== null);
		
		return $this->eiGui->createSiDeclaration($this->eiFrame);
	}
}


class EiEntryGuiMultiResult {
	private $eiEntryGuiMulti;
	private $eiFrame;
	private $eiGuiFrames;
	private $eiGuis;
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 * @param EiGuiFrame[] $eiGuiFrames
	 * @param EiGui[] $eiGuis
	 */
	function __construct(EiEntryGuiMulti $eiEntryGuiMulti, EiFrame $eiFrame, array $eiGuiFrames, ?array $eiGuis) {
		ArgUtils::valArray($eiGuis, EiGui::class, true);
		$this->eiEntryGuiMulti = $eiEntryGuiMulti;
		$this->eiFrame = $eiFrame;
		$this->eiGuiFrames = $eiGuiFrames;
		$this->eiGuis = $eiGuis;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiEntryGuiMulti
	 */
	function getEiEntryGuiMulti() {
		return $this->eiEntryGuiMulti;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	function getEiGuiFrames() {
		return $this->eiGuiFrames;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGui[]
	 */
	function getEiGuis() {
		return $this->eiGuis;
	}
	
	/**
	 * @param bool $controlsIncluded
	 * @return SiEntry
	 */
	function createSiEntry(bool $controlsIncluded) {
		
		$siEntry = new SiEntry(new SiEntryIdentifier($this->eiEntryGuiMulti->getContextEiType()->getSupremeEiType()->getId(), null),
				ViewMode::isReadOnly($this->eiEntryGuiMulti->getViewMode()), ViewMode::isBulky($this->eiEntryGuiMulti->getViewMode()));
		
		$eiEntryGuis = $this->eiEntryGuiMulti->getEiEntryGuis();
		foreach ($eiEntryGuis as $key => $eiEntryGui) {
			$siEntry->putBuildup($eiEntryGui->getEiEntry()->getEiType()->getId(),
					$this->eiGuiFrames[$key]->createSiEntryBuildup($this->eiFrame, $eiEntryGui, $controlsIncluded));
		}
		
		if (count($eiEntryGuis) == 1) {
			$siEntry->setSelectedTypeId(current($eiEntryGuis)->getEiEntry()->getEiType()->getId());
		}
		
		return $siEntry;
	}
	
	/**
	 * @param EiEntryGui[]
	 * @return SiDeclaration
	 */
	function createSiDeclaration() {
		IllegalStateException::assertTrue($this->eiGuis !== null);
		
		$n2nLocale = $this->eiFrame->getN2nContext()->getN2nLocale();
		$declaration = new SiDeclaration();
		
		foreach ($this->eiGuis as $eiGui) {
			$declaration->addTypeDeclaration($eiGui->createSiTypeDeclaration($n2nLocale));
		}
		
		return $declaration;
	}
}