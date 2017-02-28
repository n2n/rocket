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
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\LiveEntry;
use rocket\spec\ei\security\InaccessibleEntryException;
use rocket\spec\ei\manage\EntryGui;
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
use rocket\spec\ei\manage\util\model\EiuPerimeterException;
use rocket\spec\ei\EiCommandPath;
use n2n\reflection\ReflectionUtils;

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
	
	public function em(): EntityManager {
		return $this->eiFrame->getManageState()->getEntityManager();
	}
	
	public function getEiMask(): EiMask {
		return $this->eiFrame->getContextEiMask();
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
	 * @param EiSelection $eiSelection
	 * @return \rocket\spec\ei\manage\mapping\EiMapping
	 * @throws \rocket\spec\ei\security\InaccessibleEntryException
	 */
	public function createEiMapping(EiSelection $eiSelection): EiMapping {
		return $this->determineEiMask($eiSelection)->getEiEngine()->createEiMapping($this->eiFrame, $eiSelection);
	}
	
	public function createEiMappingCopy(EiSelection $eiSelection, EiMapping $from): EiMapping {
		return $this->determineEiMask($eiSelection)->getEiEngine()->createEiMappingCopy($this->eiFrame, $eiSelection, $from);
	}
	
	public function createBulkyEntryGuiModel(EiMapping $eiMapping, bool $makeEditable) {
		return $this->determineEiMask($eiMapping)->createBulkyEntryGuiModel($this->eiFrame, $eiMapping, $makeEditable);
	}
	
	public function createDetailView(EiMapping $eiMapping) {
		$eiMask = $this->determineEiMask($eiMapping);

		$entryGuiModel = $eiMask->createBulkyEntryGuiModel($this->eiFrame, $eiMapping, false);
		return $eiMask->createBulkyView($this->eiFrame, new EntryGui($entryGuiModel));
	}
	
	public function createNewEntryForm(bool $draft = false): EntryForm {
		$entryModelForms = array();
		$labels = array();
		
		$contextEiSpec = $this->eiFrame->getContextEiMask()->getEiEngine()->getEiSpec();
		$contextEiMask = $this->eiFrame->getContextEiMask();
		$eiSpecs = array_merge(array($contextEiSpec->getId() => $contextEiSpec), $contextEiSpec->getAllSubEispecs());
		foreach ($eiSpecs as $subEiSpecId => $subEiSpec) {
			if ($subEiSpec->getEntityModel()->getClass()->isAbstract()) {
				continue;
			}
				
			$eiSelection = $this->createNewEiSelection($draft, $subEiSpec);
			$subEiMapping = $this->createEiMapping($eiSelection);
						
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
	
	public function createEntryFormFromMapping(EiMapping $eiMapping) {
		$contextEiMask = $this->eiFrame->getContextEiMask();
		
		$entryForm = new EntryForm($this->eiFrame);
		$eiSpec = $eiMapping->getEiSpec();

		$entryForm->setEntryModelForms(array($eiSpec->getId() => $this->createEntryModelForm($eiSpec, $eiMapping)));
		$entryForm->setChosenId($eiSpec->getId());
		// @todo remove hack when ContentItemEiField gets updated.
		$entryForm->setChoicesMap(array($eiSpec->getId() => $contextEiMask->determineEiMask($eiSpec)->getLabelLstr()
				->t($this->eiFrame->getN2nLocale())));
		return $entryForm;
	}
	
	private function createEntryModelForm(EiSpec $eiSpec, EiMapping $eiMapping) {
		$eiMask = $this->getEiFrame()->getContextEiMask()->determineEiMask($eiSpec);
		
		$entryGuiModel = $eiMask->createBulkyEntryGuiModel($this->eiFrame, $eiMapping, true);
		return new EntryModelForm($entryGuiModel);
	}
	
	public function remove(EiSelection $eiSelection) {
		if ($eiSelection->isDraft()) {
			throw new NotYetImplementedException();
		}
		
		
		$this->eiFrame->getManageState()->getVetoableRemoveActionQueue()->removeEiSelection($eiSelection);
	}

	public function lookupPreviewController(string $previewType, EiSelection $eiSelection) {
		$entityObj = null;
		if (!$eiSelection->isDraft()) {
			$entityObj = $eiSelection->getLiveObject();
		} else {
			$eiMapping = $this->createEiMapping($eiSelection);
			$previewEiMapping = $this->createEiMappingCopy(
					$this->createNewEiSelection(false, $eiSelection->getLiveEntry()->getEiSpec()),
					$eiMapping);
			$previewEiMapping->write();
			$entityObj = $previewEiMapping->getEiSelection()->getLiveObject();
		}
		
		$previewModel = new PreviewModel($previewType, $eiSelection, $entityObj);
		
		return $this->getEiMask()->lookupPreviewController($this->eiFrame, $previewModel);
	}

	public function getPreviewType(EiSelection $eiSelection) {
		$previewTypeOptions = $this->getPreviewTypeOptions($eiSelection);
		
		if (empty($previewTypeOptions)) return null;
			
		return key($previewTypeOptions);
	}
	
	public function getPreviewTypeOptions(EiSelection $eiSelection) {
		$eiMask = $this->getEiMask();
		if (!$eiMask->isPreviewSupported()) {
			return array();
		}
		
		$previewController = $eiMask->lookupPreviewController($this->eiFrame);
		$previewTypeOptions = $previewController->getPreviewTypeOptions($this->eiFrame, $eiSelection);
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
// 		$eiSelection = $eiMapping->getEiSelection();
// 		$guiDefinition = $eiMask->createGuiDefinition($this->eiFrame, $eiSelection->isDraft(), $levelOnly);
// 		return new EntryFormPart($guiDefinition, $this->eiFrame, $eiMapping);
// 	}


// 	public function applyEntryFormLevel(EntryForm $entryForm,  $eiSpec,
// 			EiSelection $orgEiSelection = null,  $org = null) {
// 		$latestEiMapping = null;
// 		foreach ($eiSpec->getSubEiSpecs() as $sub) {
// 			$latestEiMapping = $this->applyEntryFormLevel($entryForm, $sub,
// 					$orgEiSelection, $org);
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

// 				$newEiSelection = null;
// 				if ($orgEiSelection === null) {
// 					$newEiSelection = new EiSelection(null, $newEntity);
// 				} else {
// 					OrmUtils::findLowestCommonEntityModel($org->getEntityModel(), $eiSpec->getEntityModel())
// 							->copy($orgEiSelection->getEntityObj(), $newEntity);
	
// 					if (!$orgEiSelection->isDraft()) {
// 						$newEiSelection = new EiSelection($orgEiSelection->getId(), $newEntity);
// 					} else {
// 						$draft = $orgEiSelection->getDraft();
// 						$newEiSelection = new EiSelection($orgEiSelection->getId(), $orgEiSelection->getLiveEntityObj());
// 						$newEiSelection->setDraft(new Draft($draft->getId(), $draft->getLastMod(), $draft->isPublished(),
// 								$draft->getDraftedObjectId(), new \ArrayObject()));
// 						$newEiSelection->getDraft()->setDraftedObject($newEntity);
// 					}
// 				}

// 				$eiMapping = $this->createEiMapping($newEiSelection);
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
