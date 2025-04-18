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
namespace rocket\op\ei\util\frame;

use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\EiObject;
use rocket\op\ei\EiType;
use rocket\op\ei\manage\EiEntityObj;
use n2n\persistence\orm\EntityManager;
use n2n\l10n\N2nLocale;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\preview\model\PreviewModel;
use n2n\core\container\N2nContext;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\component\command\EiCmdNature;
use rocket\op\ei\manage\LiveEiObject;
use rocket\op\ei\manage\DraftEiObject;
use rocket\op\ei\manage\draft\Draft;
use rocket\op\ei\mask\EiMask;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\util\NestedSetUtils;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\util\spec\EiuEngine;
use rocket\op\ei\util\spec\EiuMask;
use rocket\op\ei\util\EiuPerimeterException;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\manage\entry\UnknownEiObjectException;
use rocket\op\ei\manage\frame\CriteriaConstraint;
use rocket\op\ei\manage\frame\Boundary;
use rocket\op\ei\manage\entry\EiEntryConstraint;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\util\entry\EiuFieldMap;
use rocket\op\ei\util\entry\EiuObject;
use rocket\op\ei\manage\frame\EiObjectSelector;
use rocket\op\ei\manage\frame\EiRelation;
use rocket\op\ei\component\prop\EiPropNature;
use rocket\op\ei\manage\frame\EiForkLink;
use rocket\op\ei\manage\frame\CriteriaFactory;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\ui\si\api\response\SiCallResponse;
use rocket\op\ei\util\Eiu;
use n2n\reflection\magic\MagicMethodInvoker;
use rocket\op\ei\manage\frame\SortAbility;
use rocket\op\util\OpfControlResponse;
use n2n\util\type\TypeConstraints;
use rocket\op\ei\manage\api\ApiController;
use rocket\op\ei\util\spec\EiuCmd;
use rocket\op\ei\component\command\EiCmd;
use rocket\op\ei\util\spec\EiuProp;
use rocket\op\ei\component\prop\EiProp;
use n2n\util\uri\Url;
use rocket\ui\si\control\SiNavPoint;
use rocket\op\ei\manage\frame\EiObjectFactory;
use rocket\op\spec\TypePath;
use rocket\op\ei\UnknownEiTypeExtensionException;
use rocket\op\ei\UnknownEiTypeException;
use rocket\ui\gui\GuiValueBoundary;
use rocket\op\ei\manage\gui\factory\EiGuiValueBoundaryFactory;
use rocket\ui\si\content\SiObjectQualifier;
use n2n\persistence\orm\criteria\Criteria;

class EiuFrame {
	private $eiFrame;
	private $eiuAnalyst;
	
	public function __construct(EiFrame $eiFrame, EiuAnalyst $eiuAnalyst) {
		$this->eiFrame = $eiFrame;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return EiFrame
	 */
	public function getEiFrame(): EiFrame {
		return $this->eiFrame;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \n2n\web\http\HttpContext
	 */
	public function getHttpContext() {
		return $this->eiFrame->getN2nContext()->getHttpContext();
	}
	
	/**
	 * @return N2nContext
	 */
	public function getN2nContext() {
		return $this->eiFrame->getN2nContext();
	}
	
	/**
	 * @return N2nLocale
	 */
	public function getN2nLocale() {
		return $this->eiFrame->getN2nContext()->getN2nLocale();
	}

//	function getApiControlUrl(EiCmdPath|EiCmd|EiuCmd|string $eiCmdPath = null): Url {
//		return $this->getApiUrl($eiCmdPath, ApiController::API_CONTROL_SECTION);
//	}
//
//	function getApiFieldUrl(EiCmdPath|EiCmd|EiuCmd|string $eiCmdPath = null): Url {
//		return $this->getApiUrl($eiCmdPath, ApiController::API_FIELD_SECTION);
//	}
//
//	function getApiGetUrl(EiCmdPath|EiCmd|EiuCmd|string $eiCmdPath = null): Url {
//		return $this->getApiUrl($eiCmdPath, ApiController::API_GET_SECTION);
//	}
//
//	function getApiValUrl(EiCmdPath|EiCmd|EiuCmd|string $eiCmdPath = null): Url {
//		return $this->getApiUrl($eiCmdPath, ApiController::API_VAL_SECTION);
//	}
//
//	function getApiSortUrl(EiCmdPath|EiCmd|EiuCmd|string $eiCmdPath = null): Url {
//		return $this->getApiUrl($eiCmdPath, ApiController::API_SORT_SECTION);
//	}

	function getApiUrl(EiCmdPath|EiCmd|EiuCmd|string|null $eiCmdPath = null): Url {
		if ($eiCmdPath === null) {
			$eiCmdPath = $this->eiuAnalyst->getEiCmdPath(false)
					?? EiCmdPath::from($this->eiFrame->getEiExecution()->getEiCmd());
		} else {
			$eiCmdPath = EiCmdPath::create($eiCmdPath);
		}
		
		return $this->eiFrame->getApiUrl($eiCmdPath);
	}

	public function getCmdUrl(EiCmdPath|EiCmd|EiuCmd|string|null $eiCmdPath = null): Url {
		if ($eiCmdPath === null) {
			$eiCmdPath = $this->eiuAnalyst->getEiCmdPath(false)
					?? EiCmdPath::from($this->eiFrame->getEiExecution()->getEiCmd());
		} else {
			$eiCmdPath = EiCmdPath::create($eiCmdPath);
		}
		
		return $this->eiFrame->getCmdUrl($eiCmdPath);
	}
	
	public function getOverviewNavPoint(bool $required = true): ?SiNavPoint {
		return $this->eiFrame->getOverviewNavPoint($required);
	}
	
	public function getDetailNavPoint($eiObjectArg, bool $required = true): ?SiNavPoint {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, '$eiObjectArg', 
				$this->eiFrame->getContextEiEngine()->getEiMask()->getEiType());
		return $this->eiFrame->getDetailNavPoint($eiObject, $required);
	}
	
	public function getEditNavPoint($eiObjectArg, bool $required = true): ?SiNavPoint {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, '$eiObjectArg',
				$this->eiFrame->getContextEiEngine()->getEiMask()->getEiType());
		return $this->eiFrame->getEditNavPoint($eiObject, $required);
	}
	
	public function getAddNavPoint(bool $required = true): ?SiNavPoint {
		return $this->eiFrame->getAddNavPoint($required);
	}
	
	/**
	 * @return EntityManager
	 */
	public function em() {
		return $this->eiFrame->getEiLaunch()->getEntityManager();
	}

	
	private $eiuEngine;

	public function contextEngine(): EiuEngine {
		if (null !== $this->eiuEngine) {
			return $this->eiuEngine;		
		}
		
		return $this->eiuEngine = new EiuEngine($this->eiFrame->getContextEiEngine(), null, $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\op\spec\TypePath
	 */
	public function getContextEiTypePath() {
		return $this->eiFrame->getContextEiEngine()->getEiMask()->getEiTypePath();
	}
	
	/**
	 * @param mixed $eiObjectObj {@see EiuAnalyst::buildEiObjectFromEiArg()}
	 * @return \rocket\op\ei\util\spec\EiuMask
	 */
	public function mask($eiObjectObj = null) {
		if ($eiObjectObj === null) {
			return $this->contextEngine()->mask();
		}
		
		$contextEiType = $this->getContextEiType();
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectObj, 'eiObjectArg', $contextEiType);
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
		if ($contextEiType->equals($eiType)) {
			return $this->contextEngine()->mask();
		}
		
		return new EiuMask($this->getContextEiMask()->determineEiMask($eiType), null, $this->eiuAnalyst);
	}
	
	
	/**
	 * @param mixed $eiObjectObj {@see EiuAnalyst::buildEiObjectFromEiArg()}
	 * @return \rocket\op\ei\util\spec\EiuEngine
	 */
	public function engine($eiObjectObj = null) {
		if ($eiObjectObj === null) {
			return $this->contextEngine();
		}
		
		$contextEiType = $this->getContextEiType();
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectObj, 'eiObjectArg', $contextEiType);
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
		if ($contextEiType->equals($eiType)) {
			return $this->contextEngine();
		}
		
		return new EiuEngine($this->eiFrame->getContextEiEngine()->getEiMask()->determineEiMask($eiType)->getEiEngine(),
				null, $this->eiuAnalyst);
	}
	
	
	public function getContextClass() {
		return $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getEntityModel()->getClass();
	}
	
	/**
	 * @param mixed $eiObjectArg
	 * @throws EiuPerimeterException
	 * @return \rocket\op\ei\util\entry\EiuEntry
	 */
	public function entry(object $eiObjectArg) {
		if ($eiObjectArg instanceof EiuEntry) {
			return $eiObjectArg;
		}
		
		$eiEntry = null;
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectObj', 
				$this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), true, $eiEntry);
		return new EiuEntry($eiEntry, new EiuObject($eiObject, $this->eiuAnalyst), null, $this->eiuAnalyst);
	}
	
	/**
	 * @param bool $draft
	 * @param mixed $eiTypeArg
	 * @return \rocket\op\ei\util\entry\EiuEntry
	 */
	public function newEntry(bool $draft = false, $eiTypeArg = null) {
		$eiuObject = new EiuObject(
				$this->createNewEiObject($draft, EiuAnalyst::buildEiTypeFromEiArg($eiTypeArg, 'eiTypeArg', false)),
				$this->eiuAnalyst);
		return new EiuEntry(null, $eiuObject, null, $this->eiuAnalyst);
	}


	/**
	 * @param $contextEiTypePath
	 * @return EiuEntry[]
	 */
	function newPossibleEntries($contextEiTypePath = null): array {
		$contextEiTypePath = TypePath::build($contextEiTypePath);
		$eiObjectFactory = new EiObjectFactory($this->eiFrame);

		try {
			return array_map(fn(EiEntry $eiEntry) => new EiuEntry($eiEntry, null, null, $this->eiuAnalyst),
					$eiObjectFactory->createPossibleNewEiEntries($contextEiTypePath));
		} catch (UnknownEiTypeException|UnknownEiTypeExtensionException $e) {
			throw new EiuPerimeterException($e->getMessage(), previous: $e);
		}
	}

	private function createNewEiObject(bool $draft, ?EiType $eiType) {
		if ($eiType === null) {
			$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		}
		
		return $eiType->createNewEiObject($draft);
	}
	/**
	 * @param EiEntry|object $eiEntryArg
	 * @param EiPropPath|null $forkEiPropPath
	 * @param object $object
	 * @param EiEntry|object $copyFromEiEntryArg
	 * @return \rocket\op\ei\util\entry\EiuFieldMap
	 */
	public function newFieldMap($eiEntryArg, $forkEiPropPath, object $object, $copyFromEiEntryArg = null) {
		$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg);
		$copyFrom = null;
		
		if ($copyFromEiEntryArg !== null) {
			$copyFrom = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg);
		}
		
		$eiFieldMap = $eiEntry->getEiMask()->getEiEngine()->createFramedEiFieldMap($this->eiFrame, $eiEntry,
				EiPropPath::create($forkEiPropPath), $object, $copyFrom);
		
		return new EiuFieldMap($eiFieldMap, $this->eiuAnalyst);
	}
	
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return bool
	 */
	public function containsId($id, int $ignoreConstraintTypes = 0) {
		return (new EiObjectSelector($this->eiFrame))->containsId($id, $ignoreConstraintTypes);
	}
	
	/**
	 * 
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return \rocket\op\ei\util\entry\EiuEntry|null
	 */
	public function lookupEntry($id, int $ignoreConstraintTypes = 0, bool $required = false) {
		try {
			return $this->entry($this->lookupEiEntityObj($id, $ignoreConstraintTypes));
		} catch (UnknownEiObjectException $e) {
			if (!$required) {
				return null;
			}
			
			throw $e;
		}
	}
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @param bool $required
	 * @throws UnknownEiObjectException
	 * @return \rocket\op\ei\util\entry\EiuObject|NULL
	 */
	function lookupObject($id, int $ignoreConstraintTypes = 0, bool $required = false) {
		try {
			return new EiuObject($this->lookupEiObjectById($id, $ignoreConstraintTypes), $this->eiuAnalyst);
		} catch (UnknownEiObjectException $e) {
			if (!$required) {
				return null;
			}
			
			throw $e;
		}
	}
	
	/**
	 * @param int $ignoreConstraintTypes
	 * @return \rocket\op\ei\util\entry\EiuEntry[]
	 */
	public function lookupEntries(int $ignoreConstraintTypes = 0) {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		
		$entries = [];
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$entries[] = $this->entry(EiEntityObj::createFrom(
					$this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), $entityObj));
		}
		
		return $entries;
	}
	
	/**
	 * @return \rocket\op\ei\util\entry\EiuObject[] 
	 */
	function lookupObjects(int $ignoreConstraintTypes = 0, ?int $limit = null, ?int $num = null) {
		$eiEntityObjs = $this->lookupEiEntityObjs($ignoreConstraintTypes, $limit, $num);
		
		$eiuObjects = [];
		foreach ($eiEntityObjs as $eiEntityObj) {
			$eiuObjects[] = new EiuObject(new LiveEiObject($eiEntityObj), $this->eiuAnalyst);
		}
		return $eiuObjects;
	}
	
	
	/**
	 * @return EiEntityObj[]
	 */
	private function lookupEiEntityObjs(int $ignoreConstraintTypes = 0, ?int $limit = null, ?int $num = null) {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes)->select('e')->limit($limit, $num);
		$contextEiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		
		$eiEntityObjs = [];
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$eiEntityObjs[] = EiEntityObj::createFrom($contextEiType, $entityObj);
		}
		
		return $eiEntityObjs;
	}
	
	/**
	 * @param int $ignoreConstraintTypes
	 * @return int
	 */
	public function count(int $ignoreConstraintTypes = 0) {
		return (int) $this->createCountCriteria('e', $ignoreConstraintTypes)->toQuery()->fetchSingle();
	}
	
	/**
	 * @param string $entityAlias
	 * @param int $ignoreConstraintTypes
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function createCountCriteria(string $entityAlias, int $ignoreConstraintTypes = 0) {
		return $this->eiFrame->createCriteria($entityAlias, $ignoreConstraintTypes)
				->select('COUNT(1)');
	}

	function createCriteria(string $entityAlias, int $ignoreConstraintTypes = 0): Criteria {
		return $this->eiFrame->createCriteria($entityAlias, $ignoreConstraintTypes);
	}
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return EiEntityObj
	 */
	private function lookupEiEntityObj($id, int $ignoreConstraintTypes = 0) {
		return (new EiObjectSelector($this->eiFrame))->lookupEiEntityObj($id, $ignoreConstraintTypes);
	}
	
//	public function getDraftManager() {
//		return $this->eiFrame->getEiLaunch()->getDraftManager();
//	}
	
	/**
	 * @param EiObject $eiObject
	 * @return \rocket\op\ei\manage\entry\EiEntry
	 * @throws \rocket\op\ei\manage\security\InaccessibleEiEntryException
	 */
	private function createEiEntry(EiObject $eiObject, int $ignoreConstraintTypes = 0) {
		return $this->eiFrame->createEiEntry($eiObject, null, $ignoreConstraintTypes);
	}
	
//	/**
//	 * @param mixed $fromEiObjectArg
//	 * @return EiuEntry
//	 */
//	public function copyEntryTo($fromEiObjectArg, $toEiObjectArg = null) {
//		return $this->createEiEntryCopy($fromEiObjectArg, EiuAnalyst::buildEiObjectFromEiArg($toEiObjectArg, 'toEiObjectArg'));
//	}
	
	public function copyEntry($fromEiObjectArg, ?bool $draft = null, $eiTypeArg = null) {
		$fromEiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($fromEiObjectArg, $this, 'fromEiObjectArg');
		$draft = $draft ?? $fromEiuEntry->isDraft();
		
		if ($eiTypeArg !== null) {
			$eiType = EiuAnalyst::buildEiTypeFromEiArg($eiTypeArg, 'eiTypeArg', false);
		} else {
			$eiType = $fromEiuEntry->getEiType();
		}
		
		$eiObject = $this->createNewEiObject($draft, $eiType);
		return $this->entry($this->createEiEntryCopy($fromEiuEntry, $eiObject));
	}
	
	public function copyEntryValuesTo($fromEiEntryArg, $toEiEntryArg, ?array $eiPropPaths = null) {
		$fromEiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($fromEiEntryArg, $this, 'fromEiEntryArg');
		$toEiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($toEiEntryArg, $this, 'toEiEntryArg');
		
		$this->determineEiMask($toEiEntryArg)->getEiEngine()
				->copyValues($this->eiFrame, $fromEiuEntry->getEiEntry(), $toEiuEntry->getEiEntry(), $eiPropPaths);
	}
	
	/**
	 * @param mixed $fromEiObjectObj
	 * @param EiObject $to
	 * @return \rocket\op\ei\manage\entry\EiEntry
	 */
	private function createEiEntryCopy($fromEiObjectObj, ?EiObject $to = null, ?array $eiPropPaths = null) {
		$fromEiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($fromEiObjectObj, $this, 'fromEiObjectObj');
		
		if ($to === null) {
			$to = $this->createNewEiObject($fromEiuEntry->isDraft(), $fromEiuEntry->getEiType());
		}
		
		return $this->eiFrame->createEiEntry($to, $fromEiuEntry->getEiEntry());
	}
	
// 	/**
// 	 * 
// 	 * @param bool $draft
// 	 * @param mixed $copyFromEiObjectObj
// 	 * @param PropertyPath $contextPropertyPath
// 	 * @param array $allowedEiTypeIds
// 	 * @throws EiEntryManageException
// 	 * @return EiuEntryForm
// 	 */
// 	public function newEntryForm(bool $draft = false, $copyFromEiObjectObj = null, 
// 			PropertyPath $contextPropertyPath = null, ?array $allowedEiTypeIds = null,
// 			array $eiEntries = array()) {
// 		$eiuEntryTypeForms = array();
// 		$labels = array();
		
// 		$contextEiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
// 		$contextEiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		
// 		$guiDefinition = $this->eiFrame->getEiLaunch()->getDef()->getEiGuiDefinition($contextEiMask);
// 		$eiGuiMaskDeclaration = $contextEiMask->createEiGuiMaskDeclaration($this->eiFrame, ViewMode::BULKY_ADD, true);
		
// 		ArgUtils::valArray($eiEntries, EiEntry::class);
// 		foreach ($eiEntries as $eiEntry) {
// 			$eiEntries[$eiEntry->getEiType()->getId()] = $eiEntry;
// 		}
		
// 		$eiTypes = array_merge(array($contextEiType->getId() => $contextEiType), $contextEiType->getAllSubEiTypes());
// 		if ($allowedEiTypeIds !== null) {
// 			foreach (array_keys($eiTypes) as $eiTypeId) {
// 				if (in_array($eiTypeId, $allowedEiTypeIds)) continue;
					
// 				unset($eiTypes[$eiTypeId]);
// 			}
// 		}
		
// 		if (empty($eiTypes)) {
// 			throw new \InvalidArgumentException('Param allowedEiTypeIds caused an empty EiuEntryForm.');
// 		}
		
// 		$chosenId = null;
// 		foreach ($eiTypes as $subEiTypeId => $subEiType) {
// 			if ($subEiType->getClass()->isAbstract()) {
// 				continue;
// 			}
				
// 			$subEiEntry = null;
// 			if (isset($eiEntries[$subEiType->getId()])) {
// 				$subEiEntry = $eiEntries[$subEiType->getId()];
// 				$chosenId = $subEiType->getId();
// 			} else {
// 				$eiObject = $this->createNewEiObject($draft, $subEiType);
				
// 				if ($copyFromEiObjectObj !== null) {
// 					$subEiEntry = $this->createEiEntryCopy($copyFromEiObjectObj, $eiObject);
// 				} else {
// 					$subEiEntry = $this->createEiEntry($eiObject);
// 				}
				
// 			}
						
// 			$eiuEntryTypeForms[$subEiTypeId] = $this->createEiuEntryTypeForm($subEiType, $subEiEntry, $contextPropertyPath);
// 			$labels[$subEiTypeId] = $this->eiFrame->determineEiMask($subEiType)->getLabelLstr()
// 					->t($this->eiFrame->getN2nContext()->getN2nLocale());
// 		}
		
// 		$eiuEntryForm = new EiuEntryForm($this);
// 		$eiuEntryForm->setEiuEntryTypeForms($eiuEntryTypeForms);
// 		$eiuEntryForm->setChoicesMap($labels);
// 		$eiuEntryForm->setChosenId($chosenId ?? key($eiuEntryTypeForms));
// 		$eiuEntryForm->setContextPropertyPath($contextPropertyPath);
// 		$eiuEntryForm->setChoosable(count($eiuEntryTypeForms) > 1);
		
// 		if (empty($eiuEntryTypeForms)) {
// 			throw new EiEntryManageException('Can not create EiuEntryForm of ' . $contextEiType
// 					. ' because its class is abstract an has no s of non-abstract subtypes.');
// 		}
		
// 		return $eiuEntryForm;
// 	}
	
// 	/**
// 	 * @param EiEntry $eiEntry
// 	 * @param PropertyPath $contextPropertyPath
// 	 * @return \rocket\op\ei\util\entry\form\EiuEntryForm
// 	 */
// 	public function entryForm($eiEntryArg, ?PropertyPath $contextPropertyPath = null) {
// 		$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg);
// // 		$contextEiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
// 		$eiuEntryForm = new EiuEntryForm($this);
// 		$eiType = $eiEntry->getEiObject()->getEiEntityObj()->getEiType();

// 		$eiuEntryForm->setEiuEntryTypeForms(array($eiType->getId() => $this->createEiuEntryTypeForm($eiType, $eiEntry, $contextPropertyPath)));
// 		$eiuEntryForm->setChosenId($eiType->getId());
// 		// @todo remove hack when ContentItemEiProp gets updated.
// 		$eiuEntryForm->setChoicesMap(array($eiType->getId() => $this->eiFrame->determineEiMask($eiType)->getLabelLstr()
// 				->t($this->eiFrame->getN2nContext()->getN2nLocale())));
// 		return $eiuEntryForm;
// 	}
	
// 	private function createEiuEntryTypeForm(EiType $eiType, EiEntry $eiEntry, ?PropertyPath $contextPropertyPath = null) {
// 		$eiMask = $this->getEiFrame()->determineEiMask($eiType);
// 		$eiGuiMaskDeclaration = $eiMask->createEiGuiMaskDeclaration($this->eiFrame, $eiEntry->isNew() ? ViewMode::BULKY_ADD : ViewMode::BULKY_EDIT, true);
		
// 		$eiGuiValueBoundary = $eiGuiMaskDeclaration->createEiGuiValueBoundary($eiEntry);
		
// 		if ($contextPropertyPath === null) {
// 			$contextPropertyPath = new PropertyPath(array());
// 		}
		
// 		$eiGuiValueBoundary->setContextPropertyPath($contextPropertyPath->ext(
// 				new PropertyPathPart('eiuEntryTypeForms', true, $eiType->getId()))->ext('dispatchable'));
		
// 		return new EiuEntryTypeForm(new EiuGuiEntry($eiGuiValueBoundary, null, $this->eiuAnalyst));
// 	}
	


	/**
	 * @param string $previewType
	 * @param mixed $eiObjectArg
	 * @return \rocket\op\ei\manage\preview\controller\PreviewController
	 */
	public function lookupPreviewController(string $previewType, $eiObjectArg) {
		$eiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($eiObjectArg, $this, 'eiObjectArg');
		
		$previewModel = new PreviewModel($previewType, $this->eiFrame, $eiuEntry->object()->getEiObject(), 
				$eiuEntry->getEiEntry(false));
		
		return $this->getContextEiMask()->lookupPreviewController($this->eiuAnalyst->getN2nContext(true), 
				$previewModel);
	}

	public function getDefaultPreviewType($eiObjectArg) {
		$previewTypeOptions = $this->getPreviewTypeOptions($eiObjectArg);
		
		if (empty($previewTypeOptions)) return null;
			
		return key($previewTypeOptions);
	}
	
	/**
	 * @return boolean
	 */
	public function isPreviewSupported($eiObjectArg) {
		$eiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($eiObjectArg, $this, 'eiObjectArg', true);
		
		return $eiuEntry->mask()->getEiMask()->isPreviewSupported();
	}
	
	public function getPreviewTypeOptions($eiObjectArg) {
		$eiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($eiObjectArg, $this, 'eiObjectArg', true);
		
		$eiMask = $eiuEntry->mask()->getEiMask();
		
		if (!$eiMask->isPreviewSupported()) {
			return array();
		}
		
		return $eiMask->getPreviewTypeOptions($this->eiuAnalyst->getN2nContext(true), $this->eiFrame, 
				$eiuEntry->object()->getEiObject(), $eiuEntry->getEiEntry(false));
	}
	
	public function isExecutedBy(EiCmdPath|EiCmd|EiuCmd|string $eiCmdPath) {
		return $this->eiFrame->getEiExecution()->getEiCmd()->getEiCmdPath()
				->equals(EiCmdPath::create($eiCmdPath));
	}
	
//	public function isExecutedByType($eiCmdType) {
//// 		ArgUtils::valType($eiCmdType, array('string', 'object'));
//		return $this->eiFrame->getEiExecution()->getEiCmd()->getNature() instanceof $eiCmdType;
//	}
	
	/**
	 * @param string|EiCmdNature|EiCmdPath $eiCmdPath
	 * @return boolean
	 */
	public function isExecutableBy($eiCmdPath) {
		return $this->eiFrame->isExecutableBy(EiCmdPath::create($eiCmdPath));
	}
	
	/**
	 * 
	 * @return \rocket\op\ei\manage\generic\ScalarEiProperty[]
	 */
	public function getScalarEiProperties() {
		return $this->getContextEiMask()->getEiEngine()->getScalarEiDefinition()->getMap()->getValues();
	}
	
	/**
	 * @return Url
	 */
	public function getCurrentUrl() {
		return $this->eiFrame->getCurrentUrl($this->getN2nContext()->getHttpContext());
	}
	
//	/**
//	 * @return Url
//	 */
//	public function getUrlToCommand(EiCmdNature $eiCmd) {
//		return $this->getHttpContext()->getControllerContextPath($this->getEiFrame()->getControllerContext())
//				->ext((string) EiCmdPath::from($eiCmd))->toUrl();
//	}
//
//	/**
//	 * @return Url
//	 */
//	public function getContextUrl() {
//		return $this->getHttpContext()->getControllerContextPath($this->getEiFrame()->getControllerContext())->toUrl();
//	}
	
//	/**
//	 * @return EiuGuiEntry
//	 */
//	function newForgeMultiGuiValueBoundary(bool $bulky = true, bool $readOnly = false, ?array $allowedEiTypesArg = null, ?array $defPropPathsArg = null,
//			bool $guiStructureDeclarationsRequired = true) {
//		$viewMode = ViewMode::determine($bulky, $readOnly, true);
//		$allowedEiTypes = EiuAnalyst::buildEiTypesFromEiArg($allowedEiTypesArg);
//		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);
//
//		$eiGui =  new EiGui($this->eiFrame->getContextEiEngine()
//				->obtainForgeMultiEiGuiDeclaration($viewMode, $allowedEiTypes, $defPropPaths, $guiStructureDeclarationsRequired));
//		$eiGui->appendNewEiGuiValueBoundary($this->eiFrame);
//
//		$eiuGui = new EiuGui($eiGui, null, $this->eiuAnalyst);
//		return $eiuGui->entryGui();
//	}
	
// 	/**
// 	 * @param int $viewMode
// 	 * @param \Closure $uiFactory
// 	 * @param array $defPropPaths
// 	 * @return \rocket\op\ei\util\gui\EiuGuiMaskDeclaration
// 	 */
// 	public function newCustomGui(int $viewMode, \Closure $uiFactory, array $defPropPaths) {
// 		$eiGuiMaskDeclaration = $this->eiFrame->getContextEiEngine()->getEiMask()->createEiGuiMaskDeclaration($this->eiFrame, $viewMode, false);
		
// 		$eiuGuiMaskDeclaration = new EiuGuiMaskDeclaration($eiGuiMaskDeclaration, $this, $this->eiuAnalyst);
// 		$eiuGuiMaskDeclaration->initWithUiCallback($uiFactory, $defPropPaths);
// 		return $eiuGuiMaskDeclaration;
// 	}
	
	
	
	/**
	 * @param CriteriaConstraint $criteriaConstraint
	 * @param int $type {@see Boundary::getTypes()}
	 * @see Boundary::addCriteriaConstraint()
	 */
	public function addCriteriaConstraint(CriteriaConstraint $criteriaConstraint, int $type = Boundary::TYPE_MANAGE) {
		$this->eiFrame->getBoundary()->addCriteriaConstraint($type, $criteriaConstraint);
	}
	
	/**
	 * @param EiEntryConstraint $eiEntryConstraint
	 * @param int $type {@see Boundary::getTypes()}
	 * @see Boundary::addEiEntryConstraint()
	 */
	public function addEiEntryConstraint(EiEntryConstraint $eiEntryConstraint, int $type = Boundary::TYPE_MANAGE) {
		$this->eiFrame->getBoundary()->addEiEntryConstraint($type, $eiEntryConstraint);
	}
	
	//////////////////////////
	
	
	/**
	 * @return \rocket\op\ei\mask\EiMask
	 */
	public function getContextEiMask() {
		return $this->eiFrame->getContextEiEngine()->getEiMask();
	}
	
	/**
	 * @return \rocket\op\ei\EiType
	 */
	public function getContextEiType() {
		return $this->getContextEiMask()->getEiType();
	}

	/**
	 * @return \n2n\persistence\orm\util\NestedSetStrategy
	 */
	public function getNestedSetStrategy() {
		return $this->getContextEiType()->getNestedSetStrategy();
	}

	/**
	 * @param mixed $id
	 * @return string
	 */
	public function idToPid($id): string {
		return $this->getContextEiType()->idToPid($id);
	}

	/**
	 * @param string $pid
	 * @return mixed
	 */
	public function pidToId(string $pid): mixed {
		return $this->getContextEiType()->pidToId($pid);
	}

	public function siQualifierToId(SiEntryQualifier|SiObjectQualifier $siQualifier): mixed {
		$pid = null;
		if ($siQualifier instanceof SiObjectQualifier) {
			$pid = $siQualifier->getId();
		} else {
			$pid = $siQualifier->getIdentifier()->getId();
		}

		if (null !== $pid) {
			return $this->pidToId($pid);
		}
		
		return null;
	}
	
	/**
	 * @param mixed $eiObjectObj
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericLabel($eiObjectObj = null, ?N2nLocale $n2nLocale = null): string {
		return $this->determineEiMask($eiObjectObj)->getLabelLstr()->t($n2nLocale ?? $this->getN2nLocale());
	}

	/**
	 * @param mixed $eiObjectObj
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericPluralLabel($eiObjectObj = null, ?N2nLocale $n2nLocale = null): string {
		return $this->determineEiMask($eiObjectObj)->getPluralLabelLstr()->t($n2nLocale ?? $this->getN2nLocale());
	}

	/**
	 * @param mixed $eiObjectObj
	 * @return string
	 */
	public function getGenericIconType($eiObjectObj = null) {
		return $this->determineEiMask($eiObjectObj)->getIconType();
	}

	

	/**
	 * @param mixed $eiObjectObj
	 * @return EiType
	 */
	private function determineEiType($eiObjectObj): EiType {
		if ($eiObjectObj === null) {
			return $this->getContextEiType();
		}

		ArgUtils::valType($eiObjectObj, array(EiObject::class, EiEntry::class, EiEntityObj::class, EiuEntry::class, 'object'), true);

		if ($eiObjectObj instanceof EiEntry) {
			return $eiObjectObj->getEiObject()->getEiEntityObj()->getEiType();
		}

		if ($eiObjectObj instanceof EiObject) {
			return $eiObjectObj->getEiEntityObj()->getEiType();
		}

		if ($eiObjectObj instanceof EiEntityObj) {
			return $eiObjectObj->getEiType();
		}

		if ($eiObjectObj instanceof Draft) {
			return $eiObjectObj->getEiEntityObj()->getEiType();
		}

		if ($eiObjectObj instanceof EiuEntry) {
			return $eiObjectObj->getEiEntityObj()->getEiType();
		}

		return $this->getContextEiType()->determineAdequateEiType(new \ReflectionClass($eiObjectObj));
	}

	/**
	 * @param mixed $eiObjectObj
	 * @return EiMask
	 */
	private function determineEiMask($eiObjectObj): EiMask {
		if ($eiObjectObj === null) {
			return $this->getContextEiMask();
		}

		return $this->determineEiType($eiObjectObj)->getEiMask();
	}

	/**
	 * @param mixed $eiObjectObj
	 * @return \rocket\op\ei\EiEngine
	 */
	private function determineEiEngine($eiObjectObj) {
		return $this->determineEiMask($eiObjectObj)->getEiEngine();
	}

	
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return EiObject
	 */
	private function lookupEiObjectById($id, int $ignoreConstraintTypes = 0): EiObject {
		return new LiveEiObject($this->lookupEiEntityObj($id, $ignoreConstraintTypes));
	}

//	/**
//	 * @return bool
//	 */
//	public function isDraftingEnabled(): bool {
//		return $this->getContextEiMask()->isDraftingEnabled();
//	}

//	/**
//	 * @param int $id
//	 * @throws UnknownEiObjectException
//	 * @return Draft
//	 */
//	public function lookupDraftById(int $id): Draft {
//		$draft = $this->getDraftManager()->find($this->getClass(), $id,
//				$this->getContextEiMask()->getEiEngine()->getDraftDefinition());
//
//		if ($draft !== null) return $draft;
//
//		throw new UnknownEiObjectException('Unknown draft with id: ' . $id);
//	}


//	/**
//	 * @param int $id
//	 * @return EiObject
//	 */
//	public function lookupEiObjectByDraftId(int $id): EiObject {
//		return new DraftEiObject($this->lookupDraftById($id));
//	}


//	/**
//	 * @param mixed $entityObjId
//	 * @param int $limit
//	 * @param int $num
//	 * @return array
//	 */
//	public function lookupDraftsByEntityObjId($entityObjId, ?int $limit = null, ?int $num = null): array {
//		return $this->getDraftManager()->findByEntityObjId($this->getClass(), $entityObjId, $limit, $num,
//				$this->getContextEiMask()->getEiEngine()->getDraftDefinition());
//	}


//	/**
//	 * @return object
//	 */
//	public function createEntityObj() {
//		return ReflectionUtils::createObject($this->getClass());
//	}


//	/**
//	 * @param mixed $eiEntityObj
//	 * @return EiObject
//	 */
//	public function createEiObjectFromEiEntityObj($eiEntityObj): EiObject {
//		if ($eiEntityObj instanceof EiEntityObj) {
//			return new LiveEiObject($eiEntityObj);
//		}
//
//		if ($eiEntityObj !== null) {
//			return LiveEiObject::create($this->getContextEiType(), $eiEntityObj);
//		}
//
//		return new LiveEiObject(EiEntityObj::createNew($this->getContextEiMask()->getEiType()));
//	}

	/**
	 * @param Draft $draft
	 * @return EiObject
	 */
	public function createEiObjectFromDraft(Draft $draft): EiObject {
		return new DraftEiObject($draft);
	}

	

	/**
	 * @param mixed $eiObjectObj
	 * @param bool $flush
	 */
	public function persist($eiObjectObj, bool $flush = true) {
//		if ($eiObjectObj instanceof Draft) {
//			$this->persistDraft($eiObjectObj, $flush);
//			return;
//		}

		if ($eiObjectObj instanceof EiEntityObj) {
			$this->persistEiEntityObj($eiObjectObj, $flush);
			return;
		}

		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectObj, 'eiObjectObj', $this->getContextEiType());

//		if ($eiObject->isDraft()) {
//			$this->persistDraft($eiObject->getDraft(), $flush);
//			return;
//		}

		$this->persistEiEntityObj($eiObject->getEiEntityObj(), $flush);
	}

//	private function persistDraft(Draft $draft, bool $flush) {
//		$draftManager = $this->getDraftManager();
//
//		if (!$draft->isNew()) {
//			$draftManager->persist($draft);
//		} else {
//			$draftManager->persist($draft, $this->getContextEiMask()->determineEiMask(
//					$draft->getEiEntityObj()->getEiType())->getEiEngine()->getDraftDefinition());
//		}
//
//		if ($flush) {
//			$draftManager->flush();
//		}
//	}

	private function persistEiEntityObj(EiEntityObj $eiEntityObj, bool $flush) {
		$em = $this->em();
		$nss = $this->getNestedSetStrategy();
		if ($nss === null || $eiEntityObj->isPersistent()) {
			$em->persist($eiEntityObj->getEntityObj());
			if (!$flush) return;
			$em->flush();
		} else {
			if (!$flush) {
				throw new IllegalStateException(
						'Flushing is mandatory because EiEntityObj is new and has a NestedSetStrategy.');
			}

			$nsu = new NestedSetUtils($em, $eiEntityObj->getEiType()->getClass(), $nss);
			$nsu->insertRoot($eiEntityObj->getEntityObj());
		}

		if (!$eiEntityObj->isPersistent()) {
			$eiEntityObj->refreshId();
			$eiEntityObj->setPersistent(true);
		}
	}
	

//	/**
//	 * @param FilterJhtmlHook $filterJhtmlHook
//	 * @param FilterSettingGroup|null $rootGroup
//	 * @return \rocket\op\ei\util\filter\EiuFilterForm
//	 */
//	public function newFilterForm(FilterJhtmlHook $filterJhtmlHook, ?FilterSettingGroup $rootGroup = null) {
//		return new EiuFilterForm($this->getFilterDefinition(), $filterJhtmlHook, $rootGroup, $this->eiuAnalyst);
//	}
//
//	/**
//	 * @param SortSettingGroup|null $sortSetting
//	 * @return \rocket\op\ei\util\sort\EiuSortForm
//	 */
//	public function newSortForm(?SortSettingGroup $sortSetting = null) {
//		return new EiuSortForm($this->getSortDefinition(), $sortSetting, $this->eiuAnalyst);
//	}
	
	/**
	 * @param EiPropPath|EiPropNature|string $eiPropPath
	 * @param mixed $targetEiFrameArg
	 * @param mixed $targetEiObjectArg
	 */
	public function setRelation($eiPropPath, $targetEiFrameArg, $targetEiObjectArg = null) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		$targetEiFrame = EiuAnalyst::buildEiFrameFromEiArg($targetEiFrameArg, 'targetEiFrameArg');
		$targetEiObject = EiuAnalyst::buildEiObjectFromEiArg($targetEiObjectArg, 'targetEiObjectArg', null, false);
		
		$this->eiFrame->setEiRelation($eiPropPath, new EiRelation($targetEiFrame, $targetEiObject));
	}
	
	/**
	 * @param EiPropPath|EiPropNature|string $eiPropPath
	 * @return bool
	 */
	public function hasRelation($eiPropPath) {
		return $this->eiFrame->hasEiRelation(EiPropPath::create($eiPropPath));
	}


	/**
	 * @param EiPropPath|EiuProp|EiProp|array|string $eiPropPath
	 * @param null $eiObjectArg
	 * @param mixed ...$eiArgs
	 * @return Eiu
	 */
	function forkSelect(EiPropPath|EiuProp|EiProp|array|string $eiPropPath, $eiObjectArg = null, ...$eiArgs): Eiu {
		return $this->fork($eiPropPath, EiForkLink::MODE_SELECT, $eiObjectArg, ...$eiArgs);
	}

	/**
	 * @param EiPropPath|EiuProp|EiProp|array|string $eiPropPath
	 * @param null $eiObjectArg
	 * @param mixed ...$eiArgs
	 * @return Eiu
	 */
	function forkDiscover(EiPropPath|EiuProp|EiProp|array|string $eiPropPath, $eiObjectArg = null, ...$eiArgs) {
		return $this->fork($eiPropPath, EiForkLink::MODE_DISCOVER, $eiObjectArg, ...$eiArgs);
	}

	/**
	 * @param EiPropPath|EiuProp|EiProp|array|string $eiPropPath
	 * @param string $mode
	 * @param null $eiObjectArg
	 * @param mixed ...$eiArgs
	 * @return Eiu
	 */
	function fork(EiPropPath|EiuProp|EiProp|array|string $eiPropPath, string $mode, $eiObjectArg = null, ...$eiArgs) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', 
				$this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), false);
		$eiForkLink = new EiForkLink($this->eiFrame, $mode, $eiObject);
		
		$newEiFrame = $this->determineEiEngine($eiObject)->forkEiFrame($eiPropPath, $eiForkLink);
		return new Eiu($this->getN2nContext(), $newEiFrame, ...$eiArgs);
	}
	
	/**
	 * @param EiCmdPath|string $eiCmdPath
	 * @return EiuFrame
	 */
	function exec($eiCmdPath) {
		$eiCmdPath = EiCmdPath::create($eiCmdPath);
		$eiCmd = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiCmdCollection()
				->getByPath($eiCmdPath);
		
		$this->eiFrame->exec($eiCmd);
		return $this;
	}
	
	/**
	 * @param CriteriaFactory $criteriaFactory
	 * @return \rocket\op\ei\util\frame\EiuFrame
	 */
	function setCriteriaFactory(?CriteriaFactory $criteriaFactory) {
		$this->eiFrame->getBoundary()->setCriteriaFactory($criteriaFactory);
		return $this;
	}
	
	function createSiFrame() {
		return $this->eiFrame->createSiFrame();
	}
	
	/**
	 * @param \Closure $insertAfterCallback
	 * @param \Closure $insertBeforeCallback
	 * @param \Closure $insertAsChildCallback
	 * @throws EiuPerimeterException
	 * @return \rocket\op\ei\util\frame\EiuFrame
	 */
	function setSortAbility(\Closure $insertAfterCallback, \Closure $insertBeforeCallback, ?\Closure $insertAsChildCallback = null) {
		if ($insertAsChildCallback === null && null !== $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getNestedSetStrategy()) {
			throw new EiuPerimeterException('No insertAsChild callback provided in a tree context.');
		}
		
		$this->eiFrame->getAbility()->setSortAbility(
				new EiuCallbackSortAbility($this->eiuAnalyst, $insertAfterCallback, $insertBeforeCallback, $insertAsChildCallback));
		
		return $this;
	}
	
	function getQuickSearchDefinition() {
		return $this->eiFrame->getQuickSearchDefinition();
	}

	function createGuiValueBoundary($eiEntriesArg, int $viewMode, ?int $treeLevel = null): GuiValueBoundary {
		$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntriesArg, 'eiEntriesArg');

		$eiGuiEntryFactory = new EiGuiValueBoundaryFactory($this->eiuAnalyst->getEiFrame(true));
		return $eiGuiEntryFactory->create($treeLevel, [$eiEntry], $viewMode);
	}
}

class EiuCallbackSortAbility implements SortAbility {
	/**
	 * @var EiuAnalyst
	 */
	private $eiuAnalyst;
	private $insertAfterCallback;
	private $insertBeforeCallback;
	private $insertAsChildCallback;
	
	function __construct(EiuAnalyst $eiuAnalyst, \Closure $insertAfterCallback, \Closure $insertBeforeCallback, ?\Closure $insertAsChildCallback = null) {
		$this->eiuAnalyst = $eiuAnalyst;
		
		$this->insertAfterCallback = $insertAfterCallback;
		$this->insertBeforeCallback = $insertBeforeCallback;
		$this->insertAsChildCallback = $insertAsChildCallback;
	}
	
	function insertAfter(array $eiObjects, EiObject $afterEiObject): void {
		$this->callClosure($this->insertAfterCallback, $eiObjects, $afterEiObject);
	}

	function insertBefore(array $eiObjects, EiObject $beforeEiObject): void {
		$this->callClosure($this->insertBeforeCallback, $eiObjects, $beforeEiObject);
	}
	
	function insertAsChild(array $eiObjects, EiObject $parentEiObject): void {
		if ($this->insertAsChildCallback === null) {
			throw new IllegalStateException('Tree sort ability not available.');
		}
		
		$this->callClosure($this->insertAsChildCallback, $eiObjects, $parentEiObject);
	}
	
	private function callClosure(\Closure $closure, array $eiObjects, EiObject $targetEiObject) {
		$mmi = new MagicMethodInvoker($this->eiuAnalyst->getN2nContext(true));
		$mmi->setMethod(new \ReflectionFunction($closure));
//		$mmi->setReturnTypeConstraint(TypeConstraints::namedType(OpfControlResponse::class, true));
		
		$eiuObjects = array_map(function ($eiObject) { return new EiuObject($eiObject, $this->eiuAnalyst); }, $eiObjects);
		$targetEiuObject = new EiuObject($targetEiObject, $this->eiuAnalyst);
		
		$eiuControlResponse = $mmi->invoke(null, null, [$eiuObjects, $targetEiuObject]);
		
//		if ($eiuControlResponse === null) {
//			$eiuControlResponse = new OpfControlResponse($this->eiuAnalyst);
//		}
//
//		return $eiuControlResponse->toSiCallResponse($this->eiuAnalyst->getManageState()->getEiLifecycleMonitor());
	}
}
