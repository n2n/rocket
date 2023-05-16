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
namespace rocket\op\ei\manage\frame;

use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\op\ei\manage\entry\UnknownEiObjectException;
use rocket\op\ei\manage\EiEntityObj;
use n2n\persistence\orm\store\EntityInfo;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\op\ei\manage\gui\ViewMode;
use rocket\op\ei\manage\EiObject;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\persistence\orm\criteria\Criteria;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\op\ei\manage\LiveEiObject;
use rocket\op\ei\EiException;
use rocket\op\ei\UnknownEiTypeException;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\security\SecurityException;
use rocket\op\ei\manage\gui\EiGuiDeclaration;
use n2n\l10n\N2nLocale;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\gui\EiGuiValueBoundary;
use rocket\si\content\SiValueBoundary;
use rocket\si\meta\SiDeclaration;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\gui\EiGui;
use n2n\core\N2N;
use rocket\op\ei\manage\critmod\filter\ComparatorConstraintGroup;
use rocket\op\ei\manage\critmod\filter\impl\CriteriaConstraints;
use rocket\op\ei\manage\DefPropPath;

class EiFrameUtil {
	private $eiFrame;
	
	/**
	 * @param EiFrame $eiFrame
	 */
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
	}
	
	/**
	 * @return \rocket\op\ei\manage\frame\EiFrame
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
	 * @return \rocket\op\ei\manage\LiveEiObject
	 */
	function lookupEiObject($id, int $ignoreConstraintTypes = 0) {
		return new LiveEiObject($this->lookupEiEntityObj($id, $ignoreConstraintTypes));
	}
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @throws UnknownEiObjectException
	 * @return \rocket\op\ei\manage\EiEntityObj
	 */
	function lookupEiEntityObj($id, int $ignoreConstraintTypes = 0) {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select('e');
		$this->applyIdComparison($criteria->where(), $id);
		
		if (null !== ($entityObj = $criteria->toQuery()->fetchSingle())) {
			return EiEntityObj::createFrom($this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), $entityObj);
		}
		
		throw new UnknownEiObjectException('Entity not found: ' . EntityInfo::buildEntityString(
				$this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getEntityModel(), $id));
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return int|null
	 */
	function lookupTreeLevel(EiObject $eiObject) {
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		$nestedSetStrategy = $eiType->getNestedSetStrategy();
		
		if ($nestedSetStrategy === null) {
			return null;
		}
		
		$nestedSetUtils = new NestedSetUtils($this->eiFrame->getEiLaunch()->getEntityManager(),
				$eiType->getClass(), $nestedSetStrategy);
		return $nestedSetUtils->fetchLevel($eiObject->getEiEntityObj()->getEntityObj());
	}
	
	/**
	 * @param object $entityObj
	 * @return \rocket\op\ei\manage\EiEntityObj
	 */
	function createEiEntityObj(object $entityObj) {
		return EiEntityObj::createFrom($this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), $entityObj);
	}
	
	private function applyIdComparison(CriteriaComparator $criteriaComparator, $id) {
		$criteriaComparator->match(CrIt::p('e', $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()
				->getEntityModel()->getIdDef()->getEntityProperty()), CriteriaComparator::OPERATOR_EQUAL, $id);
	}
	
	/**
	 * @return \rocket\op\ei\manage\entry\EiEntry[]
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
	 * @param string[]|null $eiTypeIds
	 * @return \rocket\op\ei\EiType[]|null
	 */
	function determineEiTypes(?array $eiTypeIds) {
		if ($eiTypeIds === null) {
			return null;
		}
		
		$eiTypes = [];
		foreach ($eiTypeIds as $eiTypeId) {
			ArgUtils::valType($eiTypeId, 'string');
			$eiTypes[] = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->determineEiTypeById($eiTypeId);
		}
		return $eiTypes;
	}
	
	/**
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @return EiGui
	 * @throws EiException
	 */
	function createNewEiGui(bool $bulky, bool $readOnly, ?array $defPropPaths, ?array $allowedEiTypeIds, bool $eiGuiDeclarationRequired) {
		$viewMode = ViewMode::determine($bulky, $readOnly, true);
		$allowedEiTypes = $this->determineEiTypes($allowedEiTypeIds);
		
		$eiGui = new EiGui($this->eiFrame->getContextEiEngine()
				->obtainForgeMultiEiGuiDeclaration($viewMode, $allowedEiTypes, $defPropPaths));
		
		$eiGui->appendNewEiGuiValueBoundary($this->eiFrame, 0);
		
		return $eiGui;
		
// 		if (empty($newEiGuiValueBoundaries)) {
// 			throw new EiException('Can not create a new EiGuiValueBoundary of ' 
// 					. $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()
// 					. ' because this type is abstract and doesn\'t have any sub EiTypes.');
// 		}
	}

	/**
	 * @param EiMask $eiMask
	 * @param int $viewMode
	 * @param DefPropPath[] $defPropPaths
	 * @return EiGuiMaskDeclaration
	 */
	private function createEiGuiMaskDeclaration(EiMask $eiMask, int $viewMode, array $defPropPaths = null) {
		$guiDefinition = $eiMask->getEiEngine()->getGuiDefinition();
		
		if ($defPropPaths === null) {
			return $guiDefinition->createEiGuiDeclaration($this->eiFrame->getN2nContext(), $viewMode)->getEiGuiMaskDeclaration();
		} else {
			return $guiDefinition->createEiGuiMaskDeclaration($this->eiFrame->getN2nContext(), $viewMode, $defPropPaths);
		}
	}
	
	/**
	 * @param EiMask $eiMask
	 * @param int $viewMode
	 * @param DefPropPath[] $defPropPaths
	 * @return EiGuiDeclaration
	 */
	private function createEiGuiDeclaration(EiMask $eiMask, int $viewMode, array $defPropPaths = null): EiGuiDeclaration {
		return $eiMask->getEiEngine()->obtainEiGuiDeclaration($eiMask, $viewMode, $defPropPaths);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @throws SecurityException
	 * @throws EiException
	 * @return EiGui
	 */
	function createEiGuiFromEiObject(EiObject $eiObject, bool $bulky, bool $readOnly, ?string $eiTypeId, ?array $defPropPaths, ?int $treeLevel) {
		return $this->createEiGuiFromEiEntry($this->eiFrame->createEiEntry($eiObject), $bulky, $readOnly, $eiTypeId, $defPropPaths, $treeLevel);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @throws SecurityException
	 * @throws EiException
	 * @return EiGui
	 */
	function createEiGuiFromEiEntry(EiEntry $eiEntry, bool $bulky, bool $readOnly, ?string $eiTypeId, ?array $defPropPaths, ?int $treeLevel) {
		$viewMode = ViewMode::determine($bulky, $readOnly, $eiEntry->isNew());
		
		$eiMask = null;
		if ($eiTypeId === null) {
			$eiMask = $eiEntry->getEiMask();
		} else {
			$eiMask = $this->eiFrame->getContextEiEngine()->getEiMask()->determineEiMask(
					$this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->determineEiTypeById($eiTypeId));
		}
		
		$eiGui = new EiGui($eiMask->getEiEngine()->obtainEiGuiDeclaration($viewMode, $defPropPaths));
		
		$eiGui->appendEiGuiValueBoundary($this->eiFrame, [$eiEntry], $treeLevel);
		
		return $eiGui;
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @return EiGuiValueBoundaryResult
	 */
	function createEiGuiValueBoundary(EiEntry $eiEntry, bool $bulky, bool $readOnly, ?array $defPropPaths,
			bool $eiGuiDeclarationRequired) {
		$viewMode = ViewMode::determine($bulky, $readOnly, $eiEntry->isNew());
		$eiGuiDeclaration = null;
		$eiGuiMaskDeclaration = null;
		if (!$eiGuiDeclarationRequired) {
			$eiGuiMaskDeclaration = $this->createEiGuiMaskDeclaration($eiEntry->getEiMask(), $viewMode, $defPropPaths);
		} else {
			$eiGuiDeclaration = $this->createEiGuiDeclaration($eiEntry->getEiMask(), $viewMode, $defPropPaths);
			$eiGuiMaskDeclaration = $eiGuiDeclaration->getEiGuiMaskDeclaration();
		}
		return new EiGuiValueBoundaryResult($eiGuiMaskDeclaration->createEiGuiValueBoundary($this->eiFrame, $eiEntry), $this->eiFrame,
				$eiGuiMaskDeclaration, $eiGuiDeclaration);
	}
	
	/**
	 * @param int $ignoreConstraintTypes
	 * @param string $quickSearchStr
	 * @return int
	 */
	function count(string $quickSearchStr = null) {
		return $this->createCriteria('e', 0, $quickSearchStr)
				->select('COUNT(1)')->toQuery()->fetchSingle();
	}

	function createCriteria(string $entityAlias, int $ignoreConstraintTypes = 0, string $quickSearchStr = null): Criteria {
		$criteria = $this->eiFrame->createCriteria($entityAlias, $ignoreConstraintTypes);
		
		if ($quickSearchStr !== null) {
            $criteriaConstraint = $this->eiFrame->getQuickSearchDefinition()->buildCriteriaConstraint($quickSearchStr)
                    ?? CriteriaConstraints::noResult();

            $criteriaConstraint->applyToCriteria($criteria, CrIt::p($entityAlias));
		}
		
		return $criteria;
	}

	/**
	 * @param mixed $id
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @param array|null $defPropPaths
	 * @return EiGui
	 */
	function lookupEiGuiFromId($id, bool $bulky, bool $readOnly, ?array $defPropPaths): EiGui {
		$eiObject = $this->lookupEiObject($id);
		
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		$treeLevel = $this->lookupTreeLevel($eiObject);
		
		return $this->createEiGuiFromEiObject($eiObject, $bulky, $readOnly, null, $defPropPaths, $treeLevel);
	}
	
	/**
	 * @param int $from
	 * @param int $num
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @return \rocket\op\ei\manage\gui\EiGui
	 */
	function lookupEiGuiFromRange(int $offset, int $num, bool $bulky, bool $readOnly, array $defPropPaths = null, string $quickSearchStr = null) {
		$eiGuiDeclaration = $this->eiFrame->getContextEiEngine()->obtainEiGuiDeclaration(
				ViewMode::determine($bulky, $readOnly, false), $defPropPaths, true);
		$eiGui = new EiGui($eiGuiDeclaration);
			
		$criteria = $this->createCriteria(NestedSetUtils::NODE_ALIAS, false, $quickSearchStr);
		$criteria->select(NestedSetUtils::NODE_ALIAS);
		$criteria->limit($offset, $num);
		
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		if (null !== ($nestedSetStrategy = $eiType->getNestedSetStrategy())) {
			$this->treeLookup($eiGui, $criteria, $eiType->getClass(), $nestedSetStrategy);
		} else {
			$this->simpleLookup($eiGui, $criteria);
		}
		return $eiGui;		
	}
		
	/**
	 * @param object $entityObj
	 * @return \rocket\op\ei\manage\LiveEiObject
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
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$eiGui->appendEiGuiValueBoundary($this->eiFrame, 
					[$this->eiFrame->createEiEntry($this->createEiObject($entityObj))]);
		}
	}
		
	/**
	 * @param EiGuiMaskDeclaration $eiuGuiFrame
	 * @param Criteria $criteria
	 * @param \ReflectionClass $class
	 * @param NestedSetStrategy $nestedSetStrategy
	 */
	private function treeLookup(EiGui $eiGui, Criteria $criteria, \ReflectionClass $class, 
			NestedSetStrategy $nestedSetStrategy) {
		$nestedSetUtils = new NestedSetUtils($this->eiFrame->getEiLaunch()->getEntityManager(), 
				$class, $nestedSetStrategy);
		
		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$eiGui->appendEiGuiValueBoundary($this->eiFrame, 
					[$this->eiFrame->createEiEntry($this->createEiObject($nestedSetItem->getEntityObj()))], 
					$nestedSetItem->getLevel());
		}
	}
	
	/**
	 * @param string $eiTypeId
	 * @return \rocket\op\ei\manage\EiObject
	 * @throws UnknownEiTypeException
	 */
	function createNewEiObject(string $eiTypeId) {
		return $this->getEiTypeById($eiTypeId)->createNewEiObject(false);
	}
	
	/**
	 * @param string $eiTypeId
	 * @return \rocket\op\ei\EiType
	 * @throws UnknownEiTypeException
	 */
	function getEiTypeById(string $eiTypeId) {
		$contextEiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		
		if ($contextEiType->getId() == $eiTypeId) {
			return $contextEiType;
		}
		
		return $contextEiType->getSubEiTypeById($eiTypeId, true);
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
		return $eiMask->getEiEngine()->getIdNameDefinition()->createIdentityString($eiObject, $n2nContext,
				$n2nLocale ?? $this->eiFrame->getN2nContext()->getN2nLocale());
	}
}


class EiGuiValueBoundaryResult {
	private $eiGuiValueBoundary;
	private $eiFrame;
	private $eiGuiMaskDeclaration;
	private $eiGuiDeclaration;
	
	/**
	 * @param EiGuiValueBoundary $eiGuiValueBoundary
	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 */
	function __construct(EiGuiValueBoundary $eiGuiValueBoundary, EiFrame $eiFrame, EiGuiMaskDeclaration $eiGuiMaskDeclaration, ?EiGuiDeclaration $eiGuiDeclaration) {
		$this->eiGuiValueBoundary = $eiGuiValueBoundary;
		$this->eiFrame = $eiFrame;
		$this->eiGuiMaskDeclaration = $eiGuiMaskDeclaration;
		$this->eiGuiDeclaration = $eiGuiDeclaration;
	}
	
	/**
	 * @return \rocket\op\ei\manage\gui\EiGuiValueBoundary
	 */
	function getEiGuiValueBoundary() {
		return $this->eiGuiValueBoundary;
	}
	
	/**
	 * @return EiGuiMaskDeclaration
	 */
	function getEiGuiMaskDeclaration() {
		return $this->eiGuiMaskDeclaration;
	}
	
	/**
	 * @return EiGuiDeclaration|null
	 */
	function getEiGuiDeclaration() {
		return $this->eiGuiDeclaration;
	}
	
	/**
	 * @param bool $controlsIncluded
	 * @return SiValueBoundary
	 */
	function createSiEntry(bool $controlsIncluded) {
		return $this->eiGuiMaskDeclaration->createSiEntry($this->eiFrame, $this->eiGuiValueBoundary);
	}
	
	/**
	 * @param EiGuiValueBoundary[]
	 * @return SiDeclaration
	 */
	function createSiDeclaration() {
		IllegalStateException::assertTrue($this->eiGuiDeclaration !== null);
		
		return $this->eiGuiDeclaration->createSiDeclaration($this->eiFrame);
	}
}