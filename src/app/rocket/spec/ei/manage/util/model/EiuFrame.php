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

use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\util\model\EntryForm;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\store\EntityInfo;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\EiEntry;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\LiveEntry;
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
use rocket\spec\ei\manage\control\HrefControl;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\manage\control\AjahControl;
use n2n\util\uri\Url;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\map\PropertyPathPart;

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
	
	public function containsId($id, int $ignoreConstraintTypes = 0): bool {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select(CrIt::c('1'));
		$this->applyIdComparison($criteria->where(), $id);
		
		return null !== $criteria->toQuery()->fetchSingle();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupLiveEntryById($id, $ignoreConstraints)
	 */
	public function lookupLiveEntryById($id, int $ignoreConstraintTypes = 0): LiveEntry {
		$criteria = $this->eiFrame->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select('e');
		$this->applyIdComparison($criteria->where(), $id);
		
		if (null !== ($entityObj = $criteria->toQuery()->fetchSingle())) {
			return LiveEntry::createFrom($this->eiFrame->getContextEiMask()->getEiEngine()->getEiSpec(), $entityObj);
		}
		
		throw new UnknownEntryException('Entity not found: ' . EntityInfo::buildEntityString(
				$this->getEntityModel(), $id));
		
	}
	
	private function applyIdComparison(CriteriaComparator $criteriaComparator, $id) {
		$criteriaComparator->match(CrIt::p('e', $this->getEiFrame()->getContextEiMask()->getEiEngine()->getEiSpec()
				->getEntityModel()->getIdDef()->getEntityProperty()), CriteriaComparator::OPERATOR_EQUAL, $id);
	}
	
	public function getDraftManager(): DraftManager {
		return $this->eiFrame->getManageState()->getDraftManager();
	}
	
	/**
	 * @param unknown $eiEntryObj
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function entry($eiEntryObj) {
		return new EiuEntry($eiEntryObj, $this);
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @return \rocket\spec\ei\manage\mapping\EiMapping
	 * @throws \rocket\spec\ei\security\InaccessibleEntryException
	 */
	public function createEiMapping(EiEntry $eiEntry) {
		return $this->determineEiMask($eiEntry)->getEiEngine()->createEiMapping($this->eiFrame, $eiEntry);
	}
	
	/**
	 * @param mixed $fromEiEntryArg
	 * @return EiuEntry
	 */
	public function copyEntryTo($fromEiEntryArg, $toEiEntryArg = null) {
		return $this->createEiMappingCopy($fromEiEntryArg, EiuFactory::buildEiEntryFromEiArg($toEiEntryArg, 'toEiEntryArg'));
	}
	
	public function copyEntry($fromEiEntryArg, bool $draft = null, $eiSpecArg = null) {
		$fromEiuEntry = EiuFactory::buildEiuEntryFromEiArg($fromEiEntryArg, $this, 'fromEiEntryArg');
		$draft = $draft ?? $fromEiuEntry->isDraft();
		
		if ($eiSpecArg !== null) {
			$eiSpec = EiuFactory::buildEiSpecFromEiArg($eiSpecArg, 'eiSpecArg', false);
		} else {
			$eiSpec = $fromEiuEntry->getEiSpec();
		}
		
		return $this->createEiMappingCopy($fromEiuEntry, $this->createNewEiEntry($draft, $eiSpec));
	}
	
	public function newEntry(bool $draft = false, EiSpec $eiSpec = null) {
		return new EiuEntry($this->createNewEiEntry($draft, $eiSpec));
	}
	
	/**
	 * @param unknown $fromEiEntryObj
	 * @param EiEntry $to
	 * @return \rocket\spec\ei\manage\mapping\EiMapping
	 */
	private function createEiMappingCopy($fromEiEntryObj, EiEntry $to = null) {
		$fromEiuEntry = EiuFactory::buildEiuEntryFromEiArg($fromEiEntryObj, $this, 'fromEiEntryObj');
		
		if ($to === null) {
			$to = $this->createNewEiEntry($fromEiuEntry->isDraft(), $fromEiuEntry->getEiSpec());
		}
		
		return $this->determineEiMask($to)->getEiEngine()
				->createEiMappingCopy($this->eiFrame, $to, $fromEiuEntry->getEiMapping());
	}
	
	public function createListView(array $eiuEntryGuis) {
		ArgUtils::valArray($eiuEntryGuis, EiuEntryGui::class);
		
		return $this->getEiMask()->createListView($this, $eiuEntryGuis);
	}

	public function createTreeView(EiuEntryGuiTree $eiuEntryGuiTree) {
		return $this->getEiMask()->createTreeView($this, $eiuEntryGuiTree);
	}
	
	public function createBulkyView(EiuEntryGui $eiuEntryGui) {
		return $eiuEntryGui->getEiMask()->createBulkyView($eiuEntryGui);
	}
		
	public function createBulkyDetailView($eiEntryObj, bool $determineEiMask = true) {
		$eiMask = null;
		if ($determineEiMask) {
			$eiMask = $this->getEiMask();
		} else {
			$eiMask = $this->determineEiMask($eiEntryObj);
		}
		
		$eiuEntryGui = $this->entry($eiEntryObj)->newGui(false, false);

		return $eiMask->createBulkyView($eiuEntryGui);
	}
	
	public function createNewEntryForm(bool $draft = false, $copyFromEiEntryObj = null): EntryForm {
		$entryModelForms = array();
		$labels = array();
		
		$contextEiSpec = $this->eiFrame->getContextEiMask()->getEiEngine()->getEiSpec();
		$contextEiMask = $this->eiFrame->getContextEiMask();
		$eiSpecs = array_merge(array($contextEiSpec->getId() => $contextEiSpec), $contextEiSpec->getAllSubEispecs());
		foreach ($eiSpecs as $subEiSpecId => $subEiSpec) {
			if ($subEiSpec->getEntityModel()->getClass()->isAbstract()) {
				continue;
			}
				
			$eiEntry = $this->createNewEiEntry($draft, $subEiSpec);
			$subEiMapping = null;
			if ($copyFromEiEntryObj !== null) {
				$subEiMapping = $this->createEiMappingCopy($copyFromEiEntryObj, $eiEntry);
			} else {
				$subEiMapping = $this->createEiMapping($eiEntry);
			}
						
			$entryModelForms[$subEiSpecId] = $this->createEntryModelForm($subEiSpec, $subEiMapping);
			$labels[$subEiSpecId] = $contextEiMask->determineEiMask($subEiSpec)->getLabelLstr()
					->t($this->eiFrame->getN2nLocale());
		}
		
		$entryForm = new EntryForm($this->eiFrame);
		$entryForm->setEntryModelForms($entryModelForms);
		$entryForm->setChoicesMap($labels);
		$entryForm->setChosenId(key($entryModelForms));
		// @todo remove hack when ContentItemEiField gets updated.
		if ($contextEiSpec->hasSubEiSpecs()) {
			$entryForm->setChoosable(true);
		}
		
		if (empty($entryModelForms)) {
			throw new EntryManageException('Can not create EntryForm of ' . $contextEiSpec
					. ' because its class is abstract an has no s of non-abstract subtypes.');
		}
		
		return $entryForm;
	}
	
	public function createEntryFormFromMapping(EiMapping $eiMapping, PropertyPath $contextPropertyPath = null) {
		$contextEiMask = $this->eiFrame->getContextEiMask();
		
		$entryForm = new EntryForm($this->eiFrame);
		$eiSpec = $eiMapping->getEiSpec();

		$entryForm->setEntryModelForms(array($eiSpec->getId() => $this->createEntryModelForm($eiSpec, $eiMapping, $contextPropertyPath)));
		$entryForm->setChosenId($eiSpec->getId());
		// @todo remove hack when ContentItemEiField gets updated.
		$entryForm->setChoicesMap(array($eiSpec->getId() => $contextEiMask->determineEiMask($eiSpec)->getLabelLstr()
				->t($this->eiFrame->getN2nLocale())));
		return $entryForm;
	}
	
	private function createEntryModelForm(EiSpec $eiSpec, EiMapping $eiMapping, PropertyPath $contextPropertyPath = null) {
		$eiMask = $this->getEiFrame()->getContextEiMask()->determineEiMask($eiSpec);
		
		$eiuEntry = new EiuEntry($eiMapping, $this);
		$eiuEntryGui = new EiuEntryGui($eiuEntry, $eiMask->createBulkyEiEntryGui(new EiuEntry($eiMapping, $this), true));
		
		if ($contextPropertyPath === null) {
			$contextPropertyPath = new PropertyPath(array());
		}
		
		$eiuEntryGui->setContextPropertyPath($contextPropertyPath->ext(
				new PropertyPathPart('entryModelForms', true, $eiSpec->getId()))->ext('dispatchable'));
		
		return new EntryModelForm($eiuEntryGui);
	}
	
	public function remove(EiEntry $eiEntry) {
		if ($eiEntry->isDraft()) {
			throw new NotYetImplementedException();
		}
		
		
		$this->eiFrame->getManageState()->getVetoableRemoveActionQueue()->removeEiEntry($eiEntry);
	}

	public function lookupPreviewController(string $previewType, EiEntry $eiEntry) {
		$entityObj = null;
		if (!$eiEntry->isDraft()) {
			$entityObj = $eiEntry->getLiveObject();
		} else {
			$eiMapping = $this->createEiMapping($eiEntry);
			$previewEiMapping = $this->createEiMappingCopy($eiMapping, 
					$this->createNewEiEntry(false, $eiEntry->getLiveEntry()->getEiSpec()));
			$previewEiMapping->write();
			$entityObj = $previewEiMapping->getEiEntry()->getLiveObject();
		}
		
		$previewModel = new PreviewModel($previewType, $eiEntry, $entityObj);
		
		return $this->getEiMask()->lookupPreviewController($this->eiFrame, $previewModel);
	}

	public function getPreviewType(EiEntry $eiEntry) {
		$previewTypeOptions = $this->getPreviewTypeOptions($eiEntry);
		
		if (empty($previewTypeOptions)) return null;
			
		return key($previewTypeOptions);
	}
	
	public function getPreviewTypeOptions(EiEntry $eiEntry) {
		$eiMask = $this->getEiMask();
		if (!$eiMask->isPreviewSupported()) {
			return array();
		}
		
		$previewController = $eiMask->lookupPreviewController($this->eiFrame);
		$previewTypeOptions = $previewController->getPreviewTypeOptions(new Eiu($this, $eiEntry));
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
	private $eiSpecs = array();

	public function __construct(EntityModelManager $entityModelManager, SpecManager $specManager, int $cascadeType) { 
		$this->entityModelManager = $entityModelManager;
		$this->specManager = $specManager;
		$this->cascader = new OperationCascader($cascadeType, $this);
	}

	public function cascade($entityObj) {
		if (!$this->cascader->markAsCascaded($entityObj)) return;

		$entityModel = $this->entityModelManager->getEntityModelByEntityObj($entityObj);
		
		$this->liveEntries[] = LiveEntry::createFrom($this->specManager
				->getEiSpecByClass($entityModel->getClass()), $entityObj);
		
		$this->cascader->cascadeProperties($entityModel, $entityObj);
	}
	
	public function getLiveEntries(): array {
		return $this->liveEntries;
	}
}

// 	private function createEntryFormPart(EiSpec $eiSpec, EiMapping $eiMapping, $levelOnly) {
// 		$eiMask = $this->eiFrame->getContextEiMask()->determineEiMask($eiSpec);
// 		$eiEntry = $eiMapping->getEiEntry();
// 		$guiDefinition = $eiMask->createGuiDefinition($this->eiFrame, $eiEntry->isDraft(), $levelOnly);
// 		return new EntryFormPart($guiDefinition, $this->eiFrame, $eiMapping);
// 	}


// 	public function applyEntryFormLevel(EntryForm $entryForm,  $eiSpec,
// 			EiEntry $orgEiEntry = null,  $org = null) {
// 		$latestEiMapping = null;
// 		foreach ($eiSpec->getSubEiSpecs() as $sub) {
// 			$latestEiMapping = $this->applyEntryFormLevel($entryForm, $sub,
// 					$orgEiEntry, $org);
// 		}

// 		$entryFormPart = null;
// 		$eiMapping = null;
// 		if ($entryForm->hasTypeOption($eiSpec->getId())) {
// 			$eiMapping = $entryForm->getEiMappingById($eiSpec->getId());
// 		}

// 		if (null === $eiMapping) {
// 			$entityClass = $eiSpec->getEntityModel()->getClass();
// 			if ($entityClass->isAbstract()) {
// 				if ($latestEiMapping === null) {
// 					throw new IllegalStateException('Cannot instance an object of ' . $eiSpec->getId()
// 							. ' because it is abstract and no sub  available.');
// 				}

// 				$eiMapping = $latestEiMapping;
// 			} else {
// 				$newEntity = ReflectionUtils::createObject($entityClass);

// 				$newEiEntry = null;
// 				if ($orgEiEntry === null) {
// 					$newEiEntry = new EiEntry(null, $newEntity);
// 				} else {
// 					OrmUtils::findLowestCommonEntityModel($org->getEntityModel(), $eiSpec->getEntityModel())
// 							->copy($orgEiEntry->getEntityObj(), $newEntity);
	
// 					if (!$orgEiEntry->isDraft()) {
// 						$newEiEntry = new EiEntry($orgEiEntry->getId(), $newEntity);
// 					} else {
// 						$draft = $orgEiEntry->getDraft();
// 						$newEiEntry = new EiEntry($orgEiEntry->getId(), $orgEiEntry->getLiveEntityObj());
// 						$newEiEntry->setDraft(new Draft($draft->getId(), $draft->getLastMod(), $draft->isPublished(),
// 								$draft->getDraftedObjectId(), new \ArrayObject()));
// 						$newEiEntry->getDraft()->setDraftedObject($newEntity);
// 					}
// 				}

// 				$eiMapping = $this->createEiMapping($newEiEntry);
// 				$entryForm->addTypeOption($eiMapping);
// 			}
// 		}

// 		if ($eiSpec->equals($this->eiFrame->getContextEiMask()->getEiEngine()->getEiSpec())) {
// 			$entryForm->setMainEntryFormPart(
// 					$this->createEntryFormPart($eiSpec, $eiMapping, false));
// 		} else {
// 			$entryForm->addLevelEntryFormPart(
// 					$this->createEntryFormPart($eiSpec, $eiMapping, true));
// 		}

// 		return $eiMapping;
// 	}
