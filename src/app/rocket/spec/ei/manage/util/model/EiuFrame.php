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
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\mapping\EiEntry;
use rocket\spec\ei\manage\util\model\EntryForm;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\store\EntityInfo;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\EiType;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\EiEntityObj;
use rocket\spec\ei\security\InaccessibleEntryException;
use rocket\core\model\Rocket;
use n2n\persistence\orm\EntityManager;
use rocket\spec\ei\mask\EiMask;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\util\model\EiUtilsAdapter;
use rocket\spec\ei\manage\draft\DraftManager;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\preview\controller\PreviewController;
use rocket\spec\ei\manage\preview\model\PreviewModel;
use n2n\util\ex\NotYetImplementedException;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\persistence\orm\store\operation\CascadeOperation;
use rocket\spec\config\SpecManager;
use n2n\persistence\orm\store\operation\OperationCascader;
use n2n\persistence\orm\CascadeType;
use n2n\l10n\Lstr;
use n2n\core\container\N2nContext;
use rocket\spec\ei\EiCommandPath;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\component\command\EiCommand;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\manage\control\AjahControl;
use n2n\util\uri\Url;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\map\PropertyPathPart;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\manage\gui\EiGui;

class EiuFrame extends EiUtilsAdapter {
	private $eiFrame;
	
	public function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiFrame
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
		return $this->eiFrame->getContextEiMask();
	}

	/**
	 * @return \rocket\spec\ei\EiThingPath
	 */
	public function getEiThingPath() {
		return $this->eiFrame->getContextEiMask()->getEiThingPath();
	}
	
	public function getN2nContext(): N2nContext {
		return $this->eiFrame->getN2nContext();
	}
	
	public function getN2nLocale(): N2nLocale {
		return $this->eiFrame->getN2nLocale();
	}
	
	/**
	 * @param unknown $eiObjectObj
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function entry($eiObjectObj) {
		return new EiuEntry($eiObjectObj, $this);
	}
	
	/**
	 * @param bool $draft
	 * @param EiType $eiType
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function newEntry(bool $draft = false, EiType $eiType = null) {
		return new EiuEntry($this->createNewEiObject($draft, $eiType));
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
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function lookupEntry($id, int $ignoreConstraintTypes = 0) {
		return $this->entry($this->lookupEiEntityObj($id, $ignoreConstraintTypes));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupEiEntityObj($id, $ignoreConstraints)
	 */
	public function lookupEiEntityObj($id, int $ignoreConstraintTypes = 0): EiEntityObj {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select('e');
		$this->applyIdComparison($criteria->where(), $id);
		
		if (null !== ($entityObj = $criteria->toQuery()->fetchSingle())) {
			return EiEntityObj::createFrom($this->eiFrame->getContextEiMask()->getEiEngine()->getEiType(), $entityObj);
		}
		
		throw new UnknownEntryException('Entity not found: ' . EntityInfo::buildEntityString(
				$this->getEntityModel(), $id));
		
	}
	
	private function applyIdComparison(CriteriaComparator $criteriaComparator, $id) {
		$criteriaComparator->match(CrIt::p('e', $this->getEiFrame()->getContextEiMask()->getEiEngine()->getEiType()
				->getEntityModel()->getIdDef()->getEntityProperty()), CriteriaComparator::OPERATOR_EQUAL, $id);
	}
	
	public function getDraftManager(): DraftManager {
		return $this->eiFrame->getManageState()->getDraftManager();
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return \rocket\spec\ei\manage\mapping\EiEntry
	 * @throws \rocket\spec\ei\security\InaccessibleEntryException
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
		
		return $this->createEiEntryCopy($fromEiuEntry, $this->createNewEiObject($draft, $eiType));
	}
	
	/**
	 * @param unknown $fromEiObjectObj
	 * @param EiObject $to
	 * @return \rocket\spec\ei\manage\mapping\EiEntry
	 */
	private function createEiEntryCopy($fromEiObjectObj, EiObject $to = null) {
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
	
	public function newEntryForm(bool $draft = false, $copyFromEiObjectObj = null, PropertyPath $contextPropertyPath = null): EntryForm {
		$entryModelForms = array();
		$labels = array();
		
		$contextEiType = $this->eiFrame->getContextEiMask()->getEiEngine()->getEiType();
		$contextEiMask = $this->eiFrame->getContextEiMask();
		
		$eiGui = $contextEiMask->createEiGui($this->eiFrame, DisplayDefinition::BULKY_VIEW_MODES);
		
		$eiTypes = array_merge(array($contextEiType->getId() => $contextEiType), $contextEiType->getAllSubEiTypes());
		foreach ($eiTypes as $subEiTypeId => $subEiType) {
			if ($subEiType->getEntityModel()->getClass()->isAbstract()) {
				continue;
			}
				
			$eiObject = $this->createNewEiObject($draft, $subEiType);
			$subEiEntry = null;
			if ($copyFromEiObjectObj !== null) {
				$subEiEntry = $this->createEiEntryCopy($copyFromEiObjectObj, $eiObject);
			} else {
				$subEiEntry = $this->createEiEntry($eiObject);
			}
						
			$entryModelForms[$subEiTypeId] = $this->createEntryTypeForm($subEiType, $subEiEntry, $contextPropertyPath);
			$labels[$subEiTypeId] = $contextEiMask->determineEiMask($subEiType)->getLabelLstr()
					->t($this->eiFrame->getN2nLocale());
		}
		
		$entryForm = new EntryForm($this->eiFrame);
		$entryForm->setEntryTypeForms($entryModelForms);
		$entryForm->setChoicesMap($labels);
		$entryForm->setChosenId(key($entryModelForms));
		// @todo remove hack when ContentItemEiProp gets updated.
		if ($contextEiType->hasSubEiTypes()) {
			$entryForm->setChoosable(true);
		}
		
		if (empty($entryModelForms)) {
			throw new EntryManageException('Can not create EntryForm of ' . $contextEiType
					. ' because its class is abstract an has no s of non-abstract subtypes.');
		}
		
		return $entryForm;
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param PropertyPath $contextPropertyPath
	 * @return \rocket\spec\ei\manage\util\model\EntryForm
	 */
	public function entryForm(EiEntry $eiEntry, PropertyPath $contextPropertyPath = null) {
		$contextEiMask = $this->eiFrame->getContextEiMask();
		
		$entryForm = new EntryForm($this->eiFrame);
		$eiType = $eiEntry->getEiType();

		$entryForm->setEntryTypeForms(array($eiType->getId() => $this->createEntryTypeForm($eiType, $eiEntry, $contextPropertyPath)));
		$entryForm->setChosenId($eiType->getId());
		// @todo remove hack when ContentItemEiProp gets updated.
		$entryForm->setChoicesMap(array($eiType->getId() => $contextEiMask->determineEiMask($eiType)->getLabelLstr()
				->t($this->eiFrame->getN2nLocale())));
		return $entryForm;
	}
	
	private function createEntryTypeForm(EiType $eiType, EiEntry $eiEntry, PropertyPath $contextPropertyPath = null) {
		$eiMask = $this->getEiFrame()->getContextEiMask()->determineEiMask($eiType);
		$eiGui = $eiMask->createEiGui($this->eiFrame, DisplayDefinition::BULKY_VIEW_MODES);
		$eiEntryGui = $eiGui->createEiEntryGui($eiEntry, true);
		
		if ($contextPropertyPath === null) {
			$contextPropertyPath = new PropertyPath(array());
		}
		
		$eiEntryGui->setContextPropertyPath($contextPropertyPath->ext(
				new PropertyPathPart('entryModelForms', true, $eiType->getId()))->ext('dispatchable'));
		
		return new EntryTypeForm(new EiuEntryGui($eiEntryGui));
	}
	
	public function remove(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			throw new NotYetImplementedException();
		}
		
		
		$this->eiFrame->getManageState()->getVetoableRemoveActionQueue()->removeEiObject($eiObject);
	}

	public function lookupPreviewController(string $previewType, EiObject $eiObject) {
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
	 * @return \rocket\spec\ei\manage\generic\ScalarEiProperty[]
	 */
	public function getScalarEiProperties() {
		return $this->getEiMask()->getEiEngine()->getScalarEiDefinition()->getScalarEiProperties()->getValues();
	}
	
	public function controlFactory(HtmlView $view) {
		return new EiuControlFactory($this, $view);
	}
	
	public function getCurrentUrl() {
		return $this->eiFrame->getCurrentUrl($this->getN2nContext()->getHttpContext());
	}
	
	/**
	 * @return EiuGui
	 */
	public function newGui(bool $bulky) {
		$eiGui = $this->eiFrame->getContextEiMask()->createEiGui($this->eiFrame, 
				($bulky ? DisplayDefinition::BULKY_VIEW_MODES : DisplayDefinition::COMPACT_VIEW_MODES));
		return new EiuGui($eiGui, $this);
	}
}

class EiuControlFactory {
	private $eiuFrame;
	private $view;
	
	public function __construct(EiuFrame $eiuFrame, HtmlView $view) {
		$this->eiuFrame = $eiuFrame;
		$this->view = $view;
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @param ControlButton $controlButton
	 * @param Url $urlExt
	 * @return \rocket\spec\ei\manage\control\AjahControl
	 */
	public function createAjah(EiCommand $eiCommand, ControlButton $controlButton, Url $urlExt = null) {
		$url = $this->view->getHttpContext()
				->getControllerContextPath($this->eiuFrame->getEiFrame()->getControllerContext())
				->ext($eiCommand->getId())->toUrl()->ext($urlExt);
		return new AjahControl($url, $controlButton);
	}
}

class EiCascadeOperation implements CascadeOperation {
	private $cascader;
	private $entityModelManager;
	private $entityObjs = array();
	private $eiTypes = array();

	public function __construct(EntityModelManager $entityModelManager, SpecManager $specManager, int $cascadeType) { 
		$this->entityModelManager = $entityModelManager;
		$this->specManager = $specManager;
		$this->cascader = new OperationCascader($cascadeType, $this);
	}

	public function cascade($entityObj) {
		if (!$this->cascader->markAsCascaded($entityObj)) return;

		$entityModel = $this->entityModelManager->getEntityModelByEntityObj($entityObj);
		
		$this->liveEntries[] = EiEntityObj::createFrom($this->specManager
				->getEiTypeByClass($entityModel->getClass()), $entityObj);
		
		$this->cascader->cascadeProperties($entityModel, $entityObj);
	}
	
	public function getLiveEntries(): array {
		return $this->liveEntries;
	}
}

// 	private function createEntryFormPart(EiType $eiType, EiEntry $eiEntry, $levelOnly) {
// 		$eiMask = $this->eiFrame->getContextEiMask()->determineEiMask($eiType);
// 		$eiObject = $eiEntry->getEiObject();
// 		$guiDefinition = $eiMask->createGuiDefinition($this->eiFrame, $eiObject->isDraft(), $levelOnly);
// 		return new EntryFormPart($guiDefinition, $this->eiFrame, $eiEntry);
// 	}


// 	public function applyEntryFormLevel(EntryForm $entryForm,  $eiType,
// 			EiObject $orgEiObject = null,  $org = null) {
// 		$latestEiEntry = null;
// 		foreach ($eiType->getSubEiTypes() as $sub) {
// 			$latestEiEntry = $this->applyEntryFormLevel($entryForm, $sub,
// 					$orgEiObject, $org);
// 		}

// 		$entryFormPart = null;
// 		$eiEntry = null;
// 		if ($entryForm->hasTypeOption($eiType->getId())) {
// 			$eiEntry = $entryForm->getEiEntryById($eiType->getId());
// 		}

// 		if (null === $eiEntry) {
// 			$entityClass = $eiType->getEntityModel()->getClass();
// 			if ($entityClass->isAbstract()) {
// 				if ($latestEiEntry === null) {
// 					throw new IllegalStateException('Cannot instance an object of ' . $eiType->getId()
// 							. ' because it is abstract and no sub  available.');
// 				}

// 				$eiEntry = $latestEiEntry;
// 			} else {
// 				$newEntity = ReflectionUtils::createObject($entityClass);

// 				$newEiObject = null;
// 				if ($orgEiObject === null) {
// 					$newEiObject = new EiObject(null, $newEntity);
// 				} else {
// 					OrmUtils::findLowestCommonEntityModel($org->getEntityModel(), $eiType->getEntityModel())
// 							->copy($orgEiObject->getEntityObj(), $newEntity);
	
// 					if (!$orgEiObject->isDraft()) {
// 						$newEiObject = new EiObject($orgEiObject->getId(), $newEntity);
// 					} else {
// 						$draft = $orgEiObject->getDraft();
// 						$newEiObject = new EiObject($orgEiObject->getId(), $orgEiObject->getLiveEntityObj());
// 						$newEiObject->setDraft(new Draft($draft->getId(), $draft->getLastMod(), $draft->isPublished(),
// 								$draft->getDraftedObjectId(), new \ArrayObject()));
// 						$newEiObject->getDraft()->setDraftedObject($newEntity);
// 					}
// 				}

// 				$eiEntry = $this->createEiEntry($newEiObject);
// 				$entryForm->addTypeOption($eiEntry);
// 			}
// 		}

// 		if ($eiType->equals($this->eiFrame->getContextEiMask()->getEiEngine()->getEiType())) {
// 			$entryForm->setMainEntryFormPart(
// 					$this->createEntryFormPart($eiType, $eiEntry, false));
// 		} else {
// 			$entryForm->addLevelEntryFormPart(
// 					$this->createEntryFormPart($eiType, $eiEntry, true));
// 		}

// 		return $eiEntry;
// 	}
