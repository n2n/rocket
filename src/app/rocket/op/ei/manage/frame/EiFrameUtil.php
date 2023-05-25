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
use ReflectionClass;
use rocket\op\ei\manage\gui\EiGuiDeclarationFactory;
use rocket\op\ei\manage\gui\EiGuiEntry;

class EiFrameUtil {
	private EiGuiDeclarationFactory $eiGuiDeclarationFactory;

	/**
	 * @param EiFrame $eiFrame
	 */
	function __construct(private EiFrame $eiFrame) {;
		$this->eiGuiDeclarationFactory = new EiGuiDeclarationFactory($eiFrame->getContextEiEngine()->getEiMask(),
				$eiFrame->getN2nContext());
	}
	
	/**
	 * @return EiFrame
	 */
	function getEiFrame(): EiFrame {
		return $this->eiFrame;
	}
	
	/**
	 * @param string $pid
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	function pidToId(string $pid): mixed {
		return $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->pidToId($pid);
	}
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return boolean
	 */
	function containsId($id, int $ignoreConstraintTypes = 0): bool {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select(CrIt::c('1'));
		$this->applyIdComparison($criteria->where(), $id);
		
		return null !== $criteria->toQuery()->fetchSingle();
	}
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return LiveEiObject
	 * @throws UnknownEiObjectException
	 */
	function lookupEiObject(mixed $id, int $ignoreConstraintTypes = 0): LiveEiObject {
		return new LiveEiObject($this->lookupEiEntityObj($id, $ignoreConstraintTypes));
	}
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return EiEntityObj
	 * @throws UnknownEiObjectException
	 */
	function lookupEiEntityObj($id, int $ignoreConstraintTypes = 0): EiEntityObj {
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
	 * @return EiEntityObj
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
	

	function createNewEiGuiDeclaration(bool $bulky, bool $readOnly, ?array $defPropPaths, ?array $allowedEiTypeIds): EiGuiDeclaration {
		$viewMode = ViewMode::determine($bulky, $readOnly, true);
		$allowedEiTypes = $this->determineEiTypes($allowedEiTypeIds);
		
		return $this->eiGuiDeclarationFactory->createMultiEiGuiDeclaration($viewMode, true,
				$allowedEiTypes, $defPropPaths);
	}

	function createNewEiGuiValueBoundary(bool $bulky, bool $readOnly, bool $entryGuiControlsIncluded,
			?array $defPropPaths, ?array $allowedEiTypeIds): EiGuiValueBoundary {
		$eiGuiDeclaration = $this->createNewEiGuiDeclaration($bulky, $readOnly, $defPropPaths, $allowedEiTypeIds);
		return $eiGuiDeclaration->createNewEiGuiValueBoundary($this->eiFrame, $entryGuiControlsIncluded);
	}

//	/**
//	 * @param EiMask $eiMask
//	 * @param int $viewMode
//	 * @param DefPropPath[] $defPropPaths
//	 * @return EiGuiMaskDeclaration
//	 */
//	private function createEiGuiMaskDeclaration(EiMask $eiMask, int $viewMode, array $defPropPaths = null): EiGuiMaskDeclaration {
//		$guiDefinition = $eiMask->getEiEngine()->getGuiDefinition();
//
////		if ($defPropPaths === null) {
////			return $guiDefinition->createEiGuiDeclaration($this->eiFrame->getN2nContext(), $viewMode)->getEiGuiMaskDeclaration();
////		} else {
//			return $guiDefinition->createEiGuiMaskDeclaration($this->eiFrame->getN2nContext(), $viewMode, $defPropPaths);
////		}
//	}

	/**
	 * @param EiMask $eiMask
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @param DefPropPath[] $defPropPaths
	 * @return EiGuiDeclaration
	 */
	function createEiGuiDeclaration(EiMask $eiMask, bool $bulky, bool $readOnly, array $defPropPaths = null): EiGuiDeclaration {
		$viewMode = ViewMode::determine($bulky, $readOnly, true);

		return $this->eiGuiDeclarationFactory->createEiGuiDeclaration($viewMode, false, $defPropPaths);
	}

	function createEiGuiValueBoundaryFromEiObject(EiObject $eiObject, bool $bulky, bool $readOnly, bool $entryGuiControlsIncluded,
			?string $eiTypeId, ?array $defPropPaths, ?int $treeLevel): EiGuiValueBoundary {
		return $this->createEiGuiValueBoundaryFromEiEntry($this->eiFrame->createEiEntry($eiObject), $bulky, $readOnly,
				$entryGuiControlsIncluded, $eiTypeId, $defPropPaths, $treeLevel);
	}

	private function determineEiMask(EiEntry $eiEntry, ?string $eiTypeId): EiMask {
		if ($eiTypeId === null) {
			return $eiEntry->getEiMask();
		}

		return $this->eiFrame->getContextEiEngine()->getEiMask()->determineEiMask(
					$this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->determineEiTypeById($eiTypeId));
	}

	function createEiGuiValueBoundaryFromEiEntry(EiEntry $eiEntry, bool $bulky, bool $readOnly, bool $entryGuiControlsIncluded,
			?string $eiTypeId, ?array $defPropPaths, ?int $treeLevel): EiGuiValueBoundary {
		$viewMode = ViewMode::determine($bulky, $readOnly, $eiEntry->isNew());

		$eiMask = $this->determineEiMask($eiEntry, $eiTypeId);

		$eiGuiDeclarationFactory = new EiGuiDeclarationFactory($eiMask, $this->eiFrame->getN2nContext());

		$eiGuiDeclaration = $eiGuiDeclarationFactory->createEiGuiDeclaration($viewMode, false, $defPropPaths);
		$eiGuiValueBoundary = $eiGuiDeclaration->createEiGuiValueBoundary($this->eiFrame, [$eiEntry], $entryGuiControlsIncluded, $treeLevel);
		$eiGuiValueBoundary->selectEiGuiEntryByEiMaskId((string) $eiMask->getEiTypePath());
		return $eiGuiValueBoundary;
	}

//	/**
//	 * @param EiEntry $eiEntry
//	 * @param bool $bulky
//	 * @param bool $readOnly
//	 * @param bool $entryGuiControlsIncluded
//	 * @param array|null $defPropPaths
//	 * @return EiGuiValueBoundaryResult
//	 */
//	function createEiGuiValueBoundary(EiEntry $eiEntry, bool $bulky, bool $readOnly, bool $entryGuiControlsIncluded,
//			?array $defPropPaths, bool $contextEiMaskUsed = false): EiGuiValueBoundaryResult {
//
//		$contextEiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
//		$eiMask = $contextEiMaskUsed ? $contextEiMask : $eiEntry->getEiMask();
//
//		$eiGuiDeclaration = $this->createEiGuiDeclaration($eiMask, $bulky, $readOnly, $defPropPaths);
//
//		return new EiGuiValueBoundaryResult(
//				$eiGuiDeclaration->createEiGuiValueBoundary($this->eiFrame, [$eiEntry], $entryGuiControlsIncluded),
//				$eiGuiDeclaration);
//	}


	function copyEiGuiValueBoundary(EiGuiValueBoundary $eiGuiValueBoundary, int $viewMode = null, array $defPropPaths = null,
			bool $entryGuiControlsIncluded = null): EiGuiValueBoundary {
		$newViewMode = $viewMode ?? $eiGuiValueBoundary->getEiGuiDeclaration();
		$newEiGuiDeclaration = new EiGuiDeclaration($this->getEiFrame()->getContextEiEngine()->getEiMask(), $newViewMode);
		$newEiGuiValueBoundary = new EiGuiValueBoundary($newEiGuiDeclaration, $eiGuiValueBoundary->getTreeLevel());

		foreach ($eiGuiValueBoundary->getEiGuiEntries() as $eiGuiEntry) {
			$newEiGuiEntry = $this->copyEiGuiEntry($eiGuiEntry, $viewMode, $defPropPaths, $entryGuiControlsIncluded);
			$newEiGuiDeclaration->putEiGuiMaskDeclaration($newEiGuiEntry->getEiGuiMaskDeclaration());
			$newEiGuiValueBoundary->putEiGuiEntry($newEiGuiEntry);
		}

		if ($eiGuiValueBoundary->isEiGuiEntrySelected()) {
			$newEiGuiValueBoundary->selectEiGuiEntryByEiMaskId($eiGuiValueBoundary->getSelectedEiMaskId());
		}

		return $newEiGuiValueBoundary;
	}

	function createEiGuiEntry(EiEntry $eiEntry, bool $bulky, bool $readOnly, bool $entryGuiControlsIncluded,
			?string $eiTypeId, ?array $defPropPaths): EiGuiEntry {
		$viewMode = ViewMode::determine($bulky, $readOnly, $eiEntry->isNew());

		$eiMask = $this->determineEiMask($eiEntry, $eiTypeId);
		return $eiMask->getEiEngine()->obtainEiGuiMaskDeclaration($viewMode, $defPropPaths)
				->createEiGuiEntry($this->eiFrame, $eiEntry, $entryGuiControlsIncluded);

	}

	function copyEiGuiEntry(EiGuiEntry $eiGuiEntry, int $viewMode = null, array $defPropPaths = null,
			bool $entryGuiControlsIncluded = null): EiGuiEntry {
		ArgUtils::valArray($defPropPaths, DefPropPath::class, nullAllowed: true);

		$eiGuiMaskDeclaration = $eiGuiEntry->getEiGuiMaskDeclaration();

		if ($viewMode === null || $viewMode === $eiGuiMaskDeclaration->getViewMode()) {
			$newEiGuiMaskDeclaration = $eiGuiMaskDeclaration;
		} else {
			$newEiGuiMaskDeclaration = $eiGuiMaskDeclaration->getEiMask()->getEiEngine()
					->obtainEiGuiMaskDeclaration($viewMode, $defPropPaths);
		}

		return $newEiGuiMaskDeclaration->createEiGuiEntry($this->eiFrame,
				$eiGuiEntry->getEiEntry(),
				$entryGuiControlsIncluded ?? $eiGuiEntry->getGuiControlMap() === null);
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
	 * @param bool $entryGuiControlsIncluded
	 * @param array|null $defPropPaths
	 * @return EiGuiValueBoundary
	 */
	function lookupEiGuiFromId(mixed $id, bool $bulky, bool $readOnly, bool $entryGuiControlsIncluded, ?array $defPropPaths): EiGuiValueBoundary {
		$eiObject = $this->lookupEiObject($id);
		
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		$treeLevel = $this->lookupTreeLevel($eiObject);
		
		return $this->createEiGuiValueBoundaryFromEiObject($eiObject, $bulky, $readOnly, $entryGuiControlsIncluded, null, $defPropPaths, $treeLevel);
	}
	

	function lookupEiGuiFromRange(int $offset, int $num, bool $bulky, bool $readOnly, bool $entryGuiControlsIncluded,
			array $defPropPaths = null, string $quickSearchStr = null): RangeResult {

		$eiGuiDeclaration = $this->eiGuiDeclarationFactory->createEiGuiDeclaration(
				ViewMode::determine($bulky, $readOnly, false), true, $defPropPaths);

		$criteria = $this->createCriteria(NestedSetUtils::NODE_ALIAS, false, $quickSearchStr);
		$criteria->select(NestedSetUtils::NODE_ALIAS);
		$criteria->limit($offset, $num);
		
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		if (null !== ($nestedSetStrategy = $eiType->getNestedSetStrategy())) {
			$eiGuiValueBoundaries = $this->treeLookup($eiGuiDeclaration, $criteria, $eiType->getClass(), $nestedSetStrategy, $entryGuiControlsIncluded);
		} else {
			$eiGuiValueBoundaries = $this->simpleLookup($eiGuiDeclaration, $criteria, $entryGuiControlsIncluded);
		}

		return new RangeResult($eiGuiDeclaration, $eiGuiValueBoundaries);
	}
		
	/**
	 * @param object $entityObj
	 * @return LiveEiObject
	 */
	private function createEiObject(object $entityObj) {
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		
		return LiveEiObject::create($eiType, $entityObj);
	}

	/**
	 * @param EiGuiDeclaration $eiGuiDeclaration
	 * @param Criteria $criteria
	 * @param bool $entryGuiControlsIncluded
	 * @return array<EiGuiValueBoundary>
	 */
	private function simpleLookup(EiGuiDeclaration $eiGuiDeclaration, Criteria $criteria, bool $entryGuiControlsIncluded): array {
		$eiGuiValueBoundaries = [];
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$eiGuiValueBoundaries[] = $eiGuiDeclaration->createEiGuiValueBoundary($this->eiFrame,
					[$this->eiFrame->createEiEntry($this->createEiObject($entityObj))], $entryGuiControlsIncluded);
		}
		return $eiGuiValueBoundaries;
	}


	/**
	 * @param EiGuiDeclaration $eiGuiDeclaration
	 * @param Criteria $criteria
	 * @param ReflectionClass $class
	 * @param NestedSetStrategy $nestedSetStrategy
	 * @param bool $entryGuiControlsIncluded
	 * @return array<EiGuiValueBoundary>
	 */
	private function treeLookup(EiGuiDeclaration $eiGuiDeclaration, Criteria $criteria, ReflectionClass $class,
			NestedSetStrategy $nestedSetStrategy, bool $entryGuiControlsIncluded): array {
		$nestedSetUtils = new NestedSetUtils($this->eiFrame->getEiLaunch()->getEntityManager(), 
				$class, $nestedSetStrategy);

		$eiGuiValueBoundaries = [];
		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$eiGuiValueBoundaries[] = $eiGuiDeclaration->createEiGuiValueBoundary($this->eiFrame,
					[$this->eiFrame->createEiEntry($this->createEiObject($nestedSetItem->getEntityObj()))],
					$entryGuiControlsIncluded, $nestedSetItem->getLevel());
		}
		return $eiGuiValueBoundaries;
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
	function createIdentityString(EiObject $eiObject, bool $determineEiMask = true, N2nLocale $n2nLocale = null): string {
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
	private $eiGuiDeclaration;


	function __construct(EiGuiValueBoundary $eiGuiValueBoundary, EiGuiDeclaration $eiGuiDeclaration) {
		$this->eiGuiValueBoundary = $eiGuiValueBoundary;
		$this->eiGuiDeclaration = $eiGuiDeclaration;
	}
	
	/**
	 * @return EiGuiValueBoundary
	 */
	function getEiGuiValueBoundary(): EiGuiValueBoundary {
		return $this->eiGuiValueBoundary;
	}
	
//	/**
//	 * @return EiGuiMaskDeclaration
//	 */
//	function getEiGuiMaskDeclaration() {
//		return $this->eiGuiMaskDeclaration;
//	}
	
	/**
	 * @return EiGuiDeclaration|null
	 */
	function getEiGuiDeclaration() {
		return $this->eiGuiDeclaration;
	}
	
//	/**
//	 * @param bool $controlsIncluded
//	 * @return SiValueBoundary
//	 */
//	function createSiEntry(bool $controlsIncluded) {
//		return $this->eiGuiMaskDeclaration->createSiEntry($this->eiFrame, $this->eiGuiValueBoundary);
//	}
	
//	/**
//	 * @param EiGuiValueBoundary[]
//	 * @return SiDeclaration
//	 */
//	function createSiDeclaration(): SiDeclaration {
//		IllegalStateException::assertTrue($this->eiGuiDeclaration !== null);
//
//		return $this->eiGuiDeclaration->createSiDeclaration($this->eiFrame);
//	}
}

class RangeResult {
	/**
	 * @param EiGuiDeclaration $eiGuiDeclaration
	 * @param array<EiGuiValueBoundary> $eiGuiValueBoundaries
	 */
	function __construct(public EiGuiDeclaration $eiGuiDeclaration, public readonly array $eiGuiValueBoundaries) {
		ArgUtils::valArray($this->eiGuiValueBoundaries, EiGuiValueBoundary::class);
	}
}