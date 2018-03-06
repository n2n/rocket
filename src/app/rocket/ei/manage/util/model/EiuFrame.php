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
namespace rocket\ei\manage\util\model;

use rocket\ei\manage\mapping\EiEntry;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\store\EntityInfo;
use rocket\ei\manage\EiFrame;
use rocket\ei\manage\EiObject;
use rocket\ei\EiType;
use rocket\ei\manage\EiEntityObj;
use rocket\core\model\Rocket;
use n2n\persistence\orm\EntityManager;
use rocket\ei\mask\EiMask;
use n2n\l10n\N2nLocale;
use rocket\ei\manage\draft\DraftManager;
use n2n\reflection\ArgUtils;
use rocket\ei\manage\preview\model\PreviewModel;
use n2n\util\ex\NotYetImplementedException;
use n2n\l10n\Lstr;
use n2n\core\container\N2nContext;
use rocket\ei\EiCommandPath;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\map\PropertyPathPart;
use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\ViewMode;

class EiuFrame extends EiUtilsAdapter {
	private $eiFrame;
	
	public function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
	}
	
	/**
	 * @return \rocket\ei\manage\EiFrame
	 */
	public function getEiFrame(): EiFrame {
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
	 * @return EntityManager
	 */
	public function em(): EntityManager {
		return $this->eiFrame->getManageState()->getEntityManager();
	}

	/**
	 * @return EiMask
	 */
	public function getEiMask(): EiMask {
		return $this->eiFrame->getContextEiEngine()->getEiMask();
	}

// 	/**
// 	 * @return \rocket\ei\EiThingPath
// 	 */
// 	public function getEiThingPath() {
// 		return $this->eiFrame->getContextEiEngine()->getEiMask()->getEiThingPath();
// 	}
	
	public function getN2nContext(): N2nContext {
		return $this->eiFrame->getN2nContext();
	}
	
	public function getN2nLocale(): N2nLocale {
		return $this->eiFrame->getN2nLocale();
	}
	
	private $eiuEngine;
	
	/**
	 * @return \rocket\ei\manage\util\model\EiuEngine
	 */
	public function getEiuEngine() {
		if (null === $this->eiuEngine) {
			$this->eiuEngine = new EiuEngine($this->eiFrame->getContextEiEngine(), $this->eiFrame->getN2nContext());
		}
		
		return $this->eiuEngine;
	}
	
	
	
	/**
	 * @param mixed $eiObjectObj
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuEntry
	 */
	public function entry($eiObjectObj) {
		return new EiuEntry($eiObjectObj, $this);
	}
	
	/**
	 * @param bool $draft
	 * @param mixed $eiTypeArg
	 * @return \rocket\ei\manage\util\model\EiuEntry
	 */
	public function newEntry(bool $draft = false, $eiTypeArg = null) {
		return new EiuEntry($this->createNewEiObject($draft, 
				EiuFactory::buildEiTypeFromEiArg($eiTypeArg, 'eiTypeArg', false)), $this);
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
	 * @return \rocket\ei\manage\util\model\EiuEntry
	 */
	public function lookupEntry($id, int $ignoreConstraintTypes = 0) {
		return $this->entry($this->lookupEiEntityObj($id, $ignoreConstraintTypes));
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
	 * @see \rocket\ei\manage\util\model\EiUtils::lookupEiEntityObj($id, $ignoreConstraints)
	 */
	public function lookupEiEntityObj($id, int $ignoreConstraintTypes = 0): EiEntityObj {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select('e');
		$this->applyIdComparison($criteria->where(), $id);
		
		if (null !== ($entityObj = $criteria->toQuery()->fetchSingle())) {
			return EiEntityObj::createFrom($this->eiFrame->getContextEiEngine()->getEiMask()->getEiType(), $entityObj);
		}
		
		throw new UnknownEntryException('Entity not found: ' . EntityInfo::buildEntityString(
				$this->getEntityModel(), $id));
		
	}
	
	private function applyIdComparison(CriteriaComparator $criteriaComparator, $id) {
		$criteriaComparator->match(CrIt::p('e', $this->getEiFrame()->getContextEiEngine()->getEiMask()->getEiType()
				->getEntityModel()->getIdDef()->getEntityProperty()), CriteriaComparator::OPERATOR_EQUAL, $id);
	}
	
	public function getDraftManager(): DraftManager {
		return $this->eiFrame->getManageState()->getDraftManager();
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return \rocket\ei\manage\mapping\EiEntry
	 * @throws \rocket\ei\security\InaccessibleEntryException
	 */
	public function createEiEntry(EiObject $eiObject) {
		return $this->determineEiMask($eiObject)->getEiEngine()->createEiEntry($this->eiFrame, $eiObject);
	}
	
	/**
	 * @param mixed $fromEiObjectArg
	 * @return EiuEntry
	 */
	public function copyEntryTo($fromEiObjectArg, $toEiObjectArg = null) {
		return $this->createEiEntryCopy($fromEiObjectArg, EiuFactory::buildEiObjectFromEiArg($toEiObjectArg, 'toEiObjectArg'));
	}
	
	public function copyEntry($fromEiObjectArg, bool $draft = null, $eiTypeArg = null) {
		$fromEiuEntry = EiuFactory::buildEiuEntryFromEiArg($fromEiObjectArg, $this, 'fromEiObjectArg');
		$draft = $draft ?? $fromEiuEntry->isDraft();
		
		if ($eiTypeArg !== null) {
			$eiType = EiuFactory::buildEiTypeFromEiArg($eiTypeArg, 'eiTypeArg', false);
		} else {
			$eiType = $fromEiuEntry->getEiType();
		}
		
		return new EiuEntry($this->createEiEntryCopy($fromEiuEntry, $this->createNewEiObject($draft, $eiType)), $this);
	}
	
	public function copyEntryValuesTo($fromEiEntryArg, $toEiEntryArg, array $eiPropPaths = null) {
		$fromEiuEntry = EiuFactory::buildEiuEntryFromEiArg($fromEiEntryArg, $this, 'fromEiEntryArg');
		$toEiuEntry = EiuFactory::buildEiuEntryFromEiArg($toEiEntryArg, $this, 'toEiEntryArg');
		
		$this->determineEiMask($toEiEntryArg)->getEiEngine()
				->copyValues($this->eiFrame, $fromEiuEntry->getEiEntry(), $toEiuEntry->getEiEntry(), $eiPropPaths);
	}
	
	/**
	 * @param mixed $fromEiObjectObj
	 * @param EiObject $to
	 * @return \rocket\ei\manage\mapping\EiEntry
	 */
	private function createEiEntryCopy($fromEiObjectObj, EiObject $to = null, array $eiPropPaths = null) {
		$fromEiuEntry = EiuFactory::buildEiuEntryFromEiArg($fromEiObjectObj, $this, 'fromEiObjectObj');
		
		if ($to === null) {
			$to = $this->createNewEiObject($fromEiuEntry->isDraft(), $fromEiuEntry->getEiType());
		}
		
		return $this->determineEiMask($to)->getEiEngine()
				->createEiEntryCopy($this->eiFrame, $to, $fromEiuEntry->getEiEntry());
	}
	
// 	public function createListView(array $eiuEntryGuis) {
// 		ArgUtils::valArray($eiuEntryGuis, EiuEntryGui::class);
		
// 		return $this->getEiMask()->createListView($this, $eiuEntryGuis);
// 	}

// 	public function createTreeView(EiuEntryGuiTree $eiuEntryGuiTree) {
// 		return $this->getEiMask()->createTreeView($this, $eiuEntryGuiTree);
// 	}
	
// 	public function createBulkyView(EiuEntryGui $eiuEntryGui) {
// 		return $eiuEntryGui->getEiMask()->createBulkyView($eiuEntryGui);
// 	}
		
// 	public function createBulkyDetailView($eiObjectObj, bool $determineEiMask = true) {
// 		$eiMask = null;
// 		if ($determineEiMask) {
// 			$eiMask = $this->getEiMask();
// 		} else {
// 			$eiMask = $this->determineEiMask($eiObjectObj);
// 		}
		
// 		$eiuEntryGui = $this->entry($eiObjectObj)->newGui(false, false);

// 		return $eiMask->createBulkyView($eiuEntryGui);
// 	}
	
	/**
	 * 
	 * @param bool $draft
	 * @param mixed $copyFromEiObjectObj
	 * @param PropertyPath $contextPropertyPath
	 * @param array $allowedEiTypeIds
	 * @throws EntryManageException
	 * @return EntryForm
	 */
	public function newEntryForm(bool $draft = false, $copyFromEiObjectObj = null, 
			PropertyPath $contextPropertyPath = null, array $allowedEiTypeIds = null,
			array $eiEntries = array()): EntryForm {
		$entryTypeForms = array();
		$labels = array();
		
		$contextEiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		$contextEiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		
		$eiGui = new EiGui($this->eiFrame, ViewMode::BULKY_ADD);
		$eiGui->init($contextEiMask->createEiGuiViewFactory($eiGui));
		
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
			throw new \InvalidArgumentException('Param allowedEiTypeIds caused an empty EntryForm.');
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
						
			$entryTypeForms[$subEiTypeId] = $this->createEntryTypeForm($subEiType, $subEiEntry, $contextPropertyPath);
			$labels[$subEiTypeId] = $contextEiMask->determineEiMask($subEiType)->getLabelLstr()
					->t($this->eiFrame->getN2nLocale());
		}
		
		$entryForm = new EntryForm($this);
		$entryForm->setEntryTypeForms($entryTypeForms);
		$entryForm->setChoicesMap($labels);
		$entryForm->setChosenId($chosenId ?? key($entryTypeForms));
		$entryForm->setContextPropertyPath($contextPropertyPath);
		$entryForm->setChoosable(count($entryTypeForms) > 1);
		
		if (empty($entryTypeForms)) {
			throw new EntryManageException('Can not create EntryForm of ' . $contextEiType
					. ' because its class is abstract an has no s of non-abstract subtypes.');
		}
		
		return $entryForm;
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param PropertyPath $contextPropertyPath
	 * @return \rocket\ei\manage\util\model\EntryForm
	 */
	public function entryForm(EiEntry $eiEntry, PropertyPath $contextPropertyPath = null) {
		$contextEiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		$entryForm = new EntryForm($this);
		$eiType = $eiEntry->getEiType();

		$entryForm->setEntryTypeForms(array($eiType->getId() => $this->createEntryTypeForm($eiType, $eiEntry, $contextPropertyPath)));
		$entryForm->setChosenId($eiType->getId());
		// @todo remove hack when ContentItemEiProp gets updated.
		$entryForm->setChoicesMap(array($eiType->getId() => $contextEiMask->determineEiMask($eiType)->getLabelLstr()
				->t($this->eiFrame->getN2nLocale())));
		return $entryForm;
	}
	
	private function createEntryTypeForm(EiType $eiType, EiEntry $eiEntry, PropertyPath $contextPropertyPath = null) {
		$eiMask = $this->getEiFrame()->getContextEiEngine()->getEiMask()->determineEiMask($eiType);
		$eiGui = new EiGui($this->eiFrame, $eiEntry->isNew() ? ViewMode::BULKY_ADD : ViewMode::BULKY_EDIT);
		$eiGui->init($eiMask->createEiGuiViewFactory($eiGui));
		$eiEntryGui = $eiGui->createEiEntryGui($eiEntry);
		
		if ($contextPropertyPath === null) {
			$contextPropertyPath = new PropertyPath(array());
		}
		
		$eiEntryGui->setContextPropertyPath($contextPropertyPath->ext(
				new PropertyPathPart('entryTypeForms', true, $eiType->getId()))->ext('dispatchable'));
		
		return new EntryTypeForm(new EiuEntryGui($eiEntryGui));
	}
	
	public function remove(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			throw new NotYetImplementedException();
		}
		
		
		$this->eiFrame->getManageState()->getVetoableRemoveActionQueue()->removeEiObject($eiObject);
	}

	public function lookupPreviewController(string $previewType, $eiObjectArg) {
		$eiObject = EiuFactory::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg');
		
		$entityObj = null;
		if (!$eiObject->isDraft()) {
			$entityObj = $eiObject->getLiveObject();
		} else {
			$eiEntry = $this->createEiEntry($eiObject);
			$previewEiEntry = $this->createEiEntryCopy($eiEntry, 
					$this->createNewEiObject(false, $eiObject->getEiEntityObj()->getEiType()));
			$previewEiEntry->write();
			$entityObj = $previewEiEntry->getEiObject()->getLiveObject();
		}
		
		$previewModel = new PreviewModel($previewType, $eiObject, $entityObj);
		
		return $this->getEiMask()->lookupPreviewController($this->eiFrame, $previewModel);
	}

	public function getPreviewType(EiObject $eiObject) {
		$previewTypeOptions = $this->getPreviewTypeOptions($eiObject);
		
		if (empty($previewTypeOptions)) return null;
			
		return key($previewTypeOptions);
	}
	
	/**
	 * @return boolean
	 */
	public function isPreviewSupported() {
		return $this->getEiMask()->isPreviewSupported();
	}
	
	public function getPreviewTypeOptions(EiObject $eiObject) {
		$eiMask = $this->getEiMask();
		if (!$eiMask->isPreviewSupported()) {
			return array();
		}
		
		$previewController = $eiMask->lookupPreviewController($this->eiFrame);
		$previewTypeOptions = $previewController->getPreviewTypeOptions(new Eiu($this, $eiObject));
		ArgUtils::valArrayReturn($previewTypeOptions, $previewController, 'getPreviewTypeOptions', 
				array('string', Lstr::class));
		
		return $previewTypeOptions;
	}
	
	public function isExecutedBy($eiCommandPath) {
		return $this->eiFrame->getEiExecution()->getEiCommandPath()->startsWith(EiCommandPath::create($eiCommandPath));
	}
	
	public function isExecutedByType($eiCommandType) {
// 		ArgUtils::valType($eiCommandType, array('string', 'object'));
		return $this->eiFrame->getEiExecution()->getEiCommand() instanceof $eiCommandType;
	}
	
	/**
	 * 
	 * @return \rocket\ei\manage\generic\ScalarEiProperty[]
	 */
	public function getScalarEiProperties() {
		return $this->getEiMask()->getEiEngine()->getScalarEiDefinition()->getMap()->getValues();
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @return \rocket\ei\manage\util\model\EiuControlFactory
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
				->ext($eiCommand->getId())->toUrl();
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
		$eiGui = new EiGui($this->eiFrame, $viewMode);
		
		$eiGui->init($this->eiFrame->getContextEiEngine()->getEiMask()->createEiGuiViewFactory($eiGui));
		
		return new EiuGui($eiGui, $this);
	}
	
	/**
	 * @param int $viewMode
	 * @param \Closure $uiFactory
	 * @param array $guiIdPaths
	 * @return \rocket\ei\manage\util\model\EiuGui
	 */
	public function newCustomGui(int $viewMode, \Closure $uiFactory, array $guiIdPaths) {
		$eiGui = new EiGui($this->eiFrame, $viewMode);
		$eiuGui = new EiuGui($eiGui, $this);
		$eiuGui->initWithUiCallback($uiFactory, $guiIdPaths);
		return $eiuGui;
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