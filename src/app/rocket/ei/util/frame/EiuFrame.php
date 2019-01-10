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
namespace rocket\ei\util\frame;

use rocket\ei\manage\entry\EiEntry;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\store\EntityInfo;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\EiObject;
use rocket\ei\EiType;
use rocket\ei\manage\EiEntityObj;
use rocket\core\model\Rocket;
use n2n\persistence\orm\EntityManager;
use n2n\l10n\N2nLocale;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\preview\model\PreviewModel;
use n2n\util\ex\NotYetImplementedException;
use n2n\core\container\N2nContext;
use rocket\ei\EiCommandPath;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\map\PropertyPathPart;
use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\LiveEiObject;
use n2n\util\type\CastUtils;
use rocket\ei\manage\DraftEiObject;
use rocket\user\model\LoginContext;
use rocket\ei\manage\draft\DraftValueMap;
use n2n\reflection\ReflectionUtils;
use rocket\ei\manage\draft\Draft;
use rocket\ei\mask\EiMask;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\util\NestedSetUtils;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use rocket\ei\util\filter\EiuFilterForm;
use rocket\ei\util\filter\controller\FilterJhtmlHook;
use rocket\ei\manage\critmod\sort\SortSettingGroup;
use rocket\ei\util\sort\EiuSortForm;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\util\spec\EiuEngine;
use rocket\ei\util\spec\EiuMask;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\manage\entry\UnknownEiObjectException;
use rocket\ei\manage\entry\EiEntryManageException;
use rocket\ei\util\entry\form\EiuEntryForm;
use rocket\ei\util\entry\form\EiuEntryTypeForm;
use rocket\ei\util\gui\EiuEntryGui;
use rocket\ei\util\gui\EiuGui;
use rocket\ei\manage\frame\CriteriaConstraint;
use rocket\ei\manage\frame\Boundry;
use rocket\ei\manage\entry\EiEntryConstraint;
use rocket\ei\EiPropPath;
use rocket\ei\util\entry\EiuFieldMap;
use rocket\ei\util\entry\EiuObject;

class EiuFrame {
	private $eiFrame;
	private $eiuAnalyst;
	
	public function __construct(EiFrame $eiFrame, EiuAnalyst $eiuAnalyst) {
		$this->eiFrame = $eiFrame;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	public function getEiFrame() {
		return $this->eiFrame;
	}
	
	/**
	 * @throws IllegalStateException;
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

	/**
	 * @return EntityManager
	 */
	public function em() {
		return $this->eiFrame->getManageState()->getEntityManager();
	}

	
	private $eiuEngine;
	
	/**
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	public function getContextEiuEngine() {
		if (null !== $this->eiuEngine) {
			return $this->eiuEngine;		
		}
		
		return $this->eiuEngine = new EiuEngine($this->eiFrame->getContextEiEngine(), null, $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\spec\TypePath
	 */
	public function getContextEiTypePath() {
		return $this->eiFrame->getContextEiEngine()->getEiMask()->getEiTypePath();
	}
	
	/**
	 * @param mixed $eiObjectObj {@see EiuAnalyst::buildEiObjectFromEiArg()}
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	public function mask($eiObjectObj = null) {
		if ($eiObjectObj === null) {
			return $this->getContextEiuEngine()->getEiuMask();
		}
		
		$contextEiType = $this->getContextEiType();
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectObj, 'eiObjectArg', $contextEiType);
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
		if ($contextEiType->equals($eiType)) {
			return $this->getContextEiuEngine()->getEiuMask();
		}
		
		
		return new EiuMask($this->eiFrame->determineEiMask($eiType), null, $this->eiuAnalyst);
	}
	
	
	/**
	 * @param mixed $eiObjectObj {@see EiuAnalyst::buildEiObjectFromEiArg()}
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	public function engine($eiObjectObj = null) {
		if ($eiObjectObj === null) {
			return $this->getContextEiuEngine();
		}
		
		$contextEiType = $this->getContextEiType();
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectObj, 'eiObjectArg', $contextEiType);
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
		if ($contextEiType->equals($eiType)) {
			return $this->getContextEiuEngine();
		}
		
		
		return new EiuEngine($this->eiFrame->determineEiMask($eiType)->getEiEngine(), null, $this->eiuAnalyst);
	}
	
	
	public function getContextClass() {
		return $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getEntityModel()->getClass();
	}
	
	/**
	 * @param mixed $eiObjectArg
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\entry\EiuEntry
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
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	public function newEntry(bool $draft = false, $eiTypeArg = null) {
		$eiuObject = new EiuObject(
				$this->createNewEiObject($draft, EiuAnalyst::buildEiTypeFromEiArg($eiTypeArg, 'eiTypeArg', false)),
				$this->eiuAnalyst);
		return new EiuEntry(null, $eiuObject, null, $this->eiuAnalyst);
	}
	
	
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
	
	
	public function containsId($id, int $ignoreConstraintTypes = 0): bool {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select(CrIt::c('1'));
		$this->applyIdComparison($criteria->where(), $id);
		
		return null !== $criteria->toQuery()->fetchSingle();
	}
	
	/**
	 * 
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	public function lookupEntry($id, int $ignoreConstraintTypes = 0) {
		return $this->entry($this->lookupEiEntityObj($id, $ignoreConstraintTypes));
	}
	
	/**
	 * @param int $ignoreConstraintTypes
	 * @return \rocket\ei\util\entry\EiuEntry[]
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
	 * @param int $ignoreConstraintTypes
	 * @return int
	 */
	public function countEntries(int $ignoreConstraintTypes = 0) {
		return (int) $this->createCountCriteria('e', $ignoreConstraintTypes)->toQuery()->fetchSingle();
	}
	
	/**
	 * @param string $entityAlias
	 * @param int $ignoreConstraintTypes
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function createCountCriteria(string $entityAlias, int $ignoreConstraintTypes = 0) {
		return $this->eiFrame->createCriteria($entityAlias, $ignoreConstraintTypes)
				->select('COUNT(e)');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\util\frame\EiuFrame::lookupEiEntityObj($id, $ignoreConstraints)
	 */
	private function lookupEiEntityObj($id, int $ignoreConstraintTypes = 0): EiEntityObj {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select('e');
		$this->applyIdComparison($criteria->where(), $id);
		
		if (null !== ($entityObj = $criteria->toQuery()->fetchSingle())) {
			return EiEntityObj::createFrom($this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), $entityObj);
		}
		
		throw new UnknownEiObjectException('Entity not found: ' . EntityInfo::buildEntityString(
				$this->getContextEiType()->getEntityModel(), $id));
		
	}
	
	private function applyIdComparison(CriteriaComparator $criteriaComparator, $id) {
		$criteriaComparator->match(CrIt::p('e', $this->getEiFrame()->getContextEiEngine()->getEiMask()->getEiType()
				->getEntityModel()->getIdDef()->getEntityProperty()), CriteriaComparator::OPERATOR_EQUAL, $id);
	}
	
	public function getDraftManager() {
		return $this->eiFrame->getManageState()->getDraftManager();
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return \rocket\ei\manage\entry\EiEntry
	 * @throws \rocket\ei\security\InaccessibleEntryException
	 */
	private function createEiEntry(EiObject $eiObject, int $ignoreConstraintTypes = 0) {
		return $this->eiFrame->createEiEntry($eiObject, null, $ignoreConstraintTypes);
	}
	
	/**
	 * @param mixed $fromEiObjectArg
	 * @return EiuEntry
	 */
	public function copyEntryTo($fromEiObjectArg, $toEiObjectArg = null) {
		return $this->createEiEntryCopy($fromEiObjectArg, EiuAnalyst::buildEiObjectFromEiArg($toEiObjectArg, 'toEiObjectArg'));
	}
	
	public function copyEntry($fromEiObjectArg, bool $draft = null, $eiTypeArg = null) {
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
	
	public function copyEntryValuesTo($fromEiEntryArg, $toEiEntryArg, array $eiPropPaths = null) {
		$fromEiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($fromEiEntryArg, $this, 'fromEiEntryArg');
		$toEiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($toEiEntryArg, $this, 'toEiEntryArg');
		
		$this->determineEiMask($toEiEntryArg)->getEiEngine()
				->copyValues($this->eiFrame, $fromEiuEntry->getEiEntry(), $toEiuEntry->getEiEntry(), $eiPropPaths);
	}
	
	/**
	 * @param mixed $fromEiObjectObj
	 * @param EiObject $to
	 * @return \rocket\ei\manage\entry\EiEntry
	 */
	private function createEiEntryCopy($fromEiObjectObj, EiObject $to = null, array $eiPropPaths = null) {
		$fromEiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($fromEiObjectObj, $this, 'fromEiObjectObj');
		
		if ($to === null) {
			$to = $this->createNewEiObject($fromEiuEntry->isDraft(), $fromEiuEntry->getEiType());
		}
		
		return $this->eiFrame->createEiEntry($to, $fromEiuEntry->getEiEntry());
	}
	
	/**
	 * 
	 * @param bool $draft
	 * @param mixed $copyFromEiObjectObj
	 * @param PropertyPath $contextPropertyPath
	 * @param array $allowedEiTypeIds
	 * @throws EiEntryManageException
	 * @return EiuEntryForm
	 */
	public function newEntryForm(bool $draft = false, $copyFromEiObjectObj = null, 
			PropertyPath $contextPropertyPath = null, array $allowedEiTypeIds = null,
			array $eiEntries = array()) {
		$eiuEntryTypeForms = array();
		$labels = array();
		
		$contextEiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		$contextEiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		
		$guiDefinition = $this->eiFrame->getManageState()->getDef()->getGuiDefinition($contextEiMask);
		$eiGui = $contextEiMask->createEiGui($this->eiFrame, ViewMode::BULKY_ADD, true);
		
		ArgUtils::valArray($eiEntries, EiEntry::class);
		foreach ($eiEntries as $eiEntry) {
			$eiEntries[$eiEntry->getEiType()->getId()] = $eiEntry;
		}
		
		$eiTypes = array_merge(array($contextEiType->getId() => $contextEiType), $contextEiType->getAllSubEiTypes());
		if ($allowedEiTypeIds !== null) {
			foreach (array_keys($eiTypes) as $eiTypeId) {
				if (in_array($eiTypeId, $allowedEiTypeIds)) continue;
					
				unset($eiTypes[$eiTypeId]);
			}
		}
		
		if (empty($eiTypes)) {
			throw new \InvalidArgumentException('Param allowedEiTypeIds caused an empty EiuEntryForm.');
		}
		
		$chosenId = null;
		foreach ($eiTypes as $subEiTypeId => $subEiType) {
			if ($subEiType->getEntityModel()->getClass()->isAbstract()) {
				continue;
			}
				
			$subEiEntry = null;
			if (isset($eiEntries[$subEiType->getId()])) {
				$subEiEntry = $eiEntries[$subEiType->getId()];
				$chosenId = $subEiType->getId();
			} else {
				$eiObject = $this->createNewEiObject($draft, $subEiType);
				
				if ($copyFromEiObjectObj !== null) {
					$subEiEntry = $this->createEiEntryCopy($copyFromEiObjectObj, $eiObject);
				} else {
					$subEiEntry = $this->createEiEntry($eiObject);
				}
				
			}
						
			$eiuEntryTypeForms[$subEiTypeId] = $this->createEiuEntryTypeForm($subEiType, $subEiEntry, $contextPropertyPath);
			$labels[$subEiTypeId] = $this->eiFrame->determineEiMask($subEiType)->getLabelLstr()
					->t($this->eiFrame->getN2nContext()->getN2nLocale());
		}
		
		$eiuEntryForm = new EiuEntryForm($this);
		$eiuEntryForm->setEiuEntryTypeForms($eiuEntryTypeForms);
		$eiuEntryForm->setChoicesMap($labels);
		$eiuEntryForm->setChosenId($chosenId ?? key($eiuEntryTypeForms));
		$eiuEntryForm->setContextPropertyPath($contextPropertyPath);
		$eiuEntryForm->setChoosable(count($eiuEntryTypeForms) > 1);
		
		if (empty($eiuEntryTypeForms)) {
			throw new EiEntryManageException('Can not create EiuEntryForm of ' . $contextEiType
					. ' because its class is abstract an has no s of non-abstract subtypes.');
		}
		
		return $eiuEntryForm;
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param PropertyPath $contextPropertyPath
	 * @return \rocket\ei\util\entry\form\EiuEntryForm
	 */
	public function entryForm($eiEntryArg, PropertyPath $contextPropertyPath = null) {
		$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg);
// 		$contextEiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		$eiuEntryForm = new EiuEntryForm($this);
		$eiType = $eiEntry->getEiObject()->getEiEntityObj()->getEiType();

		$eiuEntryForm->setEiuEntryTypeForms(array($eiType->getId() => $this->createEiuEntryTypeForm($eiType, $eiEntry, $contextPropertyPath)));
		$eiuEntryForm->setChosenId($eiType->getId());
		// @todo remove hack when ContentItemEiProp gets updated.
		$eiuEntryForm->setChoicesMap(array($eiType->getId() => $this->eiFrame->determineEiMask($eiType)->getLabelLstr()
				->t($this->eiFrame->getN2nContext()->getN2nLocale())));
		return $eiuEntryForm;
	}
	
	private function createEiuEntryTypeForm(EiType $eiType, EiEntry $eiEntry, PropertyPath $contextPropertyPath = null) {
		$eiMask = $this->getEiFrame()->determineEiMask($eiType);
		$eiGui = $eiMask->createEiGui($this->eiFrame, $eiEntry->isNew() ? ViewMode::BULKY_ADD : ViewMode::BULKY_EDIT, true);
		
		$eiEntryGui = $eiGui->createEiEntryGui($eiEntry);
		
		if ($contextPropertyPath === null) {
			$contextPropertyPath = new PropertyPath(array());
		}
		
		$eiEntryGui->setContextPropertyPath($contextPropertyPath->ext(
				new PropertyPathPart('eiuEntryTypeForms', true, $eiType->getId()))->ext('dispatchable'));
		
		return new EiuEntryTypeForm(new EiuEntryGui($eiEntryGui, null, $this->eiuAnalyst));
	}
	
	public function remove(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			throw new NotYetImplementedException();
		}
			
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		$nss = $eiType->getNestedSetStrategy();
		if (null === $nss) {
			$this->em()->remove($eiObject->getEiEntityObj()->getEntityObj());
		} else {
			$nsu = new NestedSetUtils($this->em(), $eiType->getEntityModel()->getClass(), $nss);
			$nsu->remove($eiObject->getLiveObject());
		}
	}
	
	/**
	 * @return \rocket\core\model\launch\TransactionApproveAttempt
	 */
	public function flush() {
		return $this->eiFrame->getManageState()->getEiLifecycleMonitor()
				->approve($this->eiFrame->getN2nContext());
	}

	/**
	 * @param string $previewType
	 * @param mixed $eiObjectArg
	 * @return \rocket\ei\manage\preview\controller\PreviewController
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
	
	public function isExecutedBy($eiCommandPath) {
		return $this->eiFrame->getEiExecution()->getEiCommandPath()->startsWith(EiCommandPath::create($eiCommandPath));
	}
	
	public function isExecutedByType($eiCommandType) {
// 		ArgUtils::valType($eiCommandType, array('string', 'object'));
		return $this->eiFrame->getEiExecution()->getEiCommand() instanceof $eiCommandType;
	}
	
	/**
	 * @param string|EiCommand|EiCommandPath $eiCommandPath
	 * @return boolean
	 */
	public function isExecutableBy($eiCommandPath) {
		return $this->eiFrame->isExecutableBy(EiCommandPath::create($eiCommandPath));
	}
	
	/**
	 * 
	 * @return \rocket\ei\manage\generic\ScalarEiProperty[]
	 */
	public function getScalarEiProperties() {
		return $this->getContextEiMask()->getEiEngine()->getScalarEiDefinition()->getMap()->getValues();
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @return \rocket\ei\util\frame\EiuControlFactory
	 */
	public function controlFactory(EiCommand $eiCommand) {
		return new EiuControlFactory($this, $eiCommand);
	}
	
	/**
	 * @return \n2n\util\uri\Url
	 */
	public function getCurrentUrl() {
		return $this->eiFrame->getCurrentUrl($this->getN2nContext()->getHttpContext());
	}
	
	/**
	 * @return \n2n\util\uri\Url
	 */
	public function getUrlToCommand(EiCommand $eiCommand) {
		return $this->getHttpContext()->getControllerContextPath($this->getEiFrame()->getControllerContext())
				->ext((string) EiCommandPath::from($eiCommand))->toUrl();
	}
	
	/**
	 * @return \n2n\util\uri\Url
	 */
	public function getContextUrl() {
		return $this->getHttpContext()->getControllerContextPath($this->getEiFrame()->getControllerContext())->toUrl();
	}
	
	/**
	 * @return EiuGui
	 */
	public function newGui(int $viewMode) {
		$eiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		$guiDefinition = $this->eiFrame->getManageState()->getDef()->getGuiDefinition($eiMask);
		
		
		$eiGui = $eiMask->createEiGui($this->eiFrame, $viewMode, true);
		
		return new EiuGui($eiGui, $this, $this->eiuAnalyst);
	}
	
	/**
	 * @param int $viewMode
	 * @param \Closure $uiFactory
	 * @param array $guiPropPaths
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	public function newCustomGui(int $viewMode, \Closure $uiFactory, array $guiPropPaths) {
		$eiGui = $this->eiFrame->getContextEiEngine()->getEiMask()->createEiGui($this->eiFrame, $viewMode, false);
		
		$eiuGui = new EiuGui($eiGui, $this, $this->eiuAnalyst);
		$eiuGui->initWithUiCallback($uiFactory, $guiPropPaths);
		return $eiuGui;
	}
	
	/**
	 * @param CriteriaConstraint $criteriaConstraint
	 * @param int $type {@see Boundry::getTypes()}
	 * @see Boundry::addCriteriaConstraint()
	 */
	public function addCriteriaConstraint(CriteriaConstraint $criteriaConstraint, int $type = Boundry::TYPE_MANAGE) {
		$this->eiFrame->getBoundry()->addCriteriaConstraint($type, $criteriaConstraint);
	}
	
	/**
	 * @param EiEntryConstraint $eiEntryConstraint
	 * @param int $type {@see Boundry::getTypes()}
	 * @see Boundry::addEiEntryConstraint()
	 */
	public function addEiEntryConstraint(EiEntryConstraint $eiEntryConstraint, int $type = Boundry::TYPE_MANAGE) {
		$this->eiFrame->getBoundry()->addEiEntryConstraint($type, $eiEntryConstraint);
	}
	
	
	//////////////////////////
	
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	public function getContextEiMask() {
		return $this->eiFrame->getContextEiEngine()->getEiMask();
	}
	
	/**
	 * @return \rocket\ei\EiType
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
	public function pidToId(string $pid) {
		return $this->getContextEiType()->pidToId($pid);
	}

	/**
	 * @param mixed $eiObjectObj
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericLabel($eiObjectObj = null, N2nLocale $n2nLocale = null): string {
		return $this->determineEiMask($eiObjectObj)->getLabelLstr()->t($n2nLocale ?? $this->getN2nLocale());
	}

	/**
	 * @param mixed $eiObjectObj
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericPluralLabel($eiObjectObj = null, N2nLocale $n2nLocale = null): string {
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
	 * @param EiObject $eiObject
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiObject $eiObject, bool $determineEiMask = true,
			N2nLocale $n2nLocale = null): string {
		$eiMask = null;
		if ($determineEiMask) {
			$eiMask = $this->determineEiMask($eiObject);
		} else {
			$eiMask = $this->getContextEiMask();
		}

		return $this->eiFrame->getManageState()->getDef()->getGuiDefinition($eiMask)
				->createIdentityString($eiObject, $this->eiFrame->getN2nContext(), 
						$n2nLocale ?? $this->getN2nLocale());
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
	 * @return \rocket\ei\EiEngine
	 */
	private function determineEiEngine($eiObjectObj) {
		return $this->determineEiMask($eiObjectObj)->getEiEngine();
	}

	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return EiObject
	 */
	public function lookupEiObjectById($id, int $ignoreConstraintTypes = 0): EiObject {
		return new LiveEiObject($this->lookupEiEntityObj($id, $ignoreConstraintTypes));
	}


	/**
	 * @return bool
	 */
	public function isDraftingEnabled(): bool {
		return $this->getContextEiMask()->isDraftingEnabled();
	}


	/**
	 * @param int $id
	 * @throws UnknownEiObjectException
	 * @return Draft
	 */
	public function lookupDraftById(int $id): Draft {
		$draft = $this->getDraftManager()->find($this->getClass(), $id,
				$this->getContextEiMask()->getEiEngine()->getDraftDefinition());

		if ($draft !== null) return $draft;

		throw new UnknownEiObjectException('Unknown draft with id: ' . $id);
	}


	/**
	 * @param int $id
	 * @return EiObject
	 */
	public function lookupEiObjectByDraftId(int $id): EiObject {
		return new DraftEiObject($this->lookupDraftById($id));
	}


	/**
	 * @param mixed $entityObjId
	 * @param int $limit
	 * @param int $num
	 * @return array
	 */
	public function lookupDraftsByEntityObjId($entityObjId, int $limit = null, int $num = null): array {
		return $this->getDraftManager()->findByEntityObjId($this->getClass(), $entityObjId, $limit, $num,
				$this->getContextEiMask()->getEiEngine()->getDraftDefinition());
	}


	/**
	 * @return object
	 */
	public function createEntityObj() {
		return ReflectionUtils::createObject($this->getClass());
	}


	/**
	 * @param mixed $eiEntityObj
	 * @return EiObject
	 */
	public function createEiObjectFromEiEntityObj($eiEntityObj): EiObject {
		if ($eiEntityObj instanceof EiEntityObj) {
			return new LiveEiObject($eiEntityObj);
		}

		if ($eiEntityObj !== null) {
			return LiveEiObject::create($this->getContextEiType(), $eiEntityObj);
		}

		return new LiveEiObject(EiEntityObj::createNew($this->getContextEiMask()));
	}

	/**
	 * @param Draft $draft
	 * @return EiObject
	 */
	public function createEiObjectFromDraft(Draft $draft): EiObject {
		return new DraftEiObject($draft);
	}

	/**
	 * @param bool $draft
	 * @param EiType $eiType
	 * @return EiObject
	 */
	public function createNewEiObject(bool $draft = false, EiType $eiType = null): EiObject {
		if ($eiType === null) {
			$eiType = $this->getContextEiType();
		}

		if (!$draft) {
			return new LiveEiObject(EiEntityObj::createNew($eiType));
		}

		$loginContext = $this->getN2nContext()->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);

		return new DraftEiObject($this->createNewDraftFromEiEntityObj(EiEntityObj::createNew($eiType)));
	}

	/**
	 * @param EiEntityObj $eiEntityObj
	 * @return \rocket\ei\manage\draft\Draft
	 */
	public function createNewDraftFromEiEntityObj(EiEntityObj $eiEntityObj) {
		$loginContext = $this->getN2nContext()->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);

		return new Draft(null, $eiEntityObj, new \DateTime(),
				$loginContext->getCurrentUser()->getId(), new DraftValueMap());
	}

	/**
	 * @param mixed $eiObjectObj
	 * @param bool $flush
	 */
	public function persist($eiObjectObj, bool $flush = true) {
		if ($eiObjectObj instanceof Draft) {
			$this->persistDraft($eiObjectObj, $flush);
			return;
		}

		if ($eiObjectObj instanceof EiEntityObj) {
			$this->persistEiEntityObj($eiObjectObj, $flush);
			return;
		}

		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectObj, 'eiObjectObj', $this->getContextEiType());

		if ($eiObject->isDraft()) {
			$this->persistDraft($eiObject->getDraft(), $flush);
			return;
		}

		$this->persistEiEntityObj($eiObject->getEiEntityObj(), $flush);
	}

	private function persistDraft(Draft $draft, bool $flush) {
		$draftManager = $this->getDraftManager();

		if (!$draft->isNew()) {
			$draftManager->persist($draft);
		} else {
			$draftManager->persist($draft, $this->getContextEiMask()->determineEiMask(
					$draft->getEiEntityObj()->getEiType())->getEiEngine()->getDraftDefinition());
		}

		if ($flush) {
			$draftManager->flush();
		}
	}

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

			$nsu = new NestedSetUtils($em, $this->getClass(), $nss);
			$nsu->insertRoot($eiEntityObj->getEntityObj());
		}

		if (!$eiEntityObj->isPersistent()) {
			$eiEntityObj->refreshId();
			$eiEntityObj->setPersistent(true);
		}
	}
	
	/**
	 * @var \rocket\ei\manage\critmod\filter\FilterDefinition
	 */
	private $filterDefinition;
	/**
	 * @var \rocket\ei\manage\critmod\sort\SortDefinition
	 */
	private $sortDefinition;
	/**
	 * @var \rocket\ei\manage\critmod\quick\QuickSearchDefinition
	 */
	private $quickSearchDefinition;
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\FilterDefinition
	 */
	public function getFilterDefinition() {
		if ($this->filterDefinition !== null) {
			return $this->filterDefinition;
		}
		
		return $this->filterDefinition = $this->eiFrame->getContextEiEngine()->createFramedFilterDefinition($this->eiFrame);
	}
	
	/**
	 * @return boolean
	 */
	public function hasFilterProps() {
		return !$this->getFilterDefinition()->isEmpty();
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\sort\SortDefinition
	 */
	public function getSortDefinition() {
		if ($this->sortDefinition !== null) {
			return $this->sortDefinition;
		}
		
		return $this->sortDefinition = $this->eiFrame->getContextEiEngine()->createFramedSortDefinition($this->eiFrame);
	}
	
	/**
	 * @return boolean
	 */
	public function hasSortProps() {
		return !$this->getSortDefinition()->isEmpty();
	}
	
	
	/**
	 * @return \rocket\ei\manage\critmod\quick\QuickSearchDefinition
	 */
	public function getQuickSearchDefinition() {
		if ($this->quickSearchDefinition !== null) {
			return $this->quickSearchDefinition;
		}
		
		return $this->quickSearchDefinition = $this->eiFrame->getContextEiEngine()->createFramedQuickSearchDefinition($this->eiFrame);
	}
	
	/**
	 * @return boolean
	 */
	public function hasQuickSearchProps() {
		return !$this->getQuickSearchDefinition()->isEmpty();
	}
	
	/**
	 * @param FilterJhtmlHook $filterJhtmlHook
	 * @param FilterSettingGroup|null $rootGroup
	 * @return \rocket\ei\util\filter\EiuFilterForm
	 */
	public function newFilterForm(FilterJhtmlHook $filterJhtmlHook, FilterSettingGroup $rootGroup = null) {
		return new EiuFilterForm($this->getFilterDefinition(), $filterJhtmlHook, $rootGroup, $this->eiuAnalyst);
	}
	
	/**
	 * @param SortSettingGroup|null $sortSetting
	 * @return \rocket\ei\util\sort\EiuSortForm
	 */
	public function newSortForm(SortSettingGroup $sortSetting = null) {
		return new EiuSortForm($this->getSortDefinition(), $sortSetting, $this->eiuAnalyst);
	}
}

// class EiCascadeOperation implements CascadeOperation {
// 	private $cascader;
// 	private $entityModelManager;
// 	private $spec;
// 	private $entityObjs = array();
// 	private $eiTypes = array();
// 	private $liveEntries = array();

// 	public function __construct(EntityModelManager $entityModelManager, Spec $spec, int $cascadeType) { 
// 		$this->entityModelManager = $entityModelManager;
// 		$this->spec = $spec;
// 		$this->cascader = new OperationCascader($cascadeType, $this);
// 	}

// 	public function cascade($entityObj) {
// 		if (!$this->cascader->markAsCascaded($entityObj)) return;

// 		$entityModel = $this->entityModelManager->getEntityModelByEntityObj($entityObj);
		
// 		$this->liveEntries[] = EiEntityObj::createFrom($this->spec
// 				->getEiTypeByClass($entityModel->getClass()), $entityObj);
		
// 		$this->cascader->cascadeProperties($entityModel, $entityObj);
// 	}
	
// 	public function getLiveEntries(): array {
// 		return $this->liveEntries;
// 	}
// }