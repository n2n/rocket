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
use rocket\spec\ei\manage\EiState;
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
use rocket\spec\ei\manage\VetoableRemoveAction;
use n2n\util\ex\NotYetImplementedException;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\persistence\orm\store\operation\CascadeOperation;
use rocket\spec\config\SpecManager;
use n2n\persistence\orm\store\operation\OperationCascader;
use n2n\persistence\orm\CascadeType;
use n2n\l10n\Lstr;
use n2n\persistence\orm\util\NestedSetUtils;

class EiStateUtils extends EiUtilsAdapter {
	private $eiState;
// 	private $httpContext;
	
	public function __construct(EiState $eiState/*, HttpContext $httpContext = null*/) {
		$this->eiState = $eiState;
// 		$this->httpContext = $httpContext;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiState
	 */
	public function getEiState(): EiState {
		return $this->eiState;
	}
	
// 	/**
// 	 * @throws IllegalStateException;
// 	 * @return \n2n\web\http\HttpContext
// 	 */
// 	public function getHttpContext() {
// 		if ($this->httpContext !== null) {
// 			return $this->httpContext;
// 		}
		
// 		throw new IllegalStateException('HttpContext not avaialable.');
// 	}
	
	public function em(): EntityManager {
		return $this->eiState->getManageState()->getEntityManager();
	}
	
	public function getEiMask(): EiMask {
		return $this->eiState->getContextEiMask();
	}
	
	public function getN2nLocale(): N2nLocale {
		return $this->eiState->getN2nLocale();
	}
	
	public function containsId($id, int $ignoreConstraintTypes = 0): bool {
		$criteria = $this->eiState->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select(CrIt::c('1'));
		$this->applyIdComparison($criteria->where(), $id);
		
		return null !== $criteria->toQuery()->fetchSingle();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupLiveEntryById($id, $ignoreConstraints)
	 */
	public function lookupLiveEntryById($id, int $ignoreConstraintTypes = 0): LiveEntry {
		$criteria = $this->eiState->createCriteria('e', $ignoreConstraintTypes);
		$criteria->select('e');
		$this->applyIdComparison($criteria->where(), $id);
		
		if (null !== ($entityObj = $criteria->toQuery()->fetchSingle())) {
			return LiveEntry::createFrom($this->eiState->getContextEiMask()->getEiEngine()->getEiSpec(), $entityObj);
		}
		
		throw new UnknownEntryException('Entity not found: ' . EntityInfo::buildEntityString(
				$this->getEntityModel(), $id));
		
	}
	
	private function applyIdComparison(CriteriaComparator $criteriaComparator, $id) {
		$criteriaComparator->match(CrIt::p('e', $this->getEiState()->getContextEiMask()->getEiEngine()->getEiSpec()
				->getEntityModel()->getIdDef()->getEntityProperty()), CriteriaComparator::OPERATOR_EQUAL, $id);
	}
	
	public function getDraftManager(): DraftManager {
		return $this->eiState->getManageState()->getDraftManager();
	}
	
// 	public function lookupEiSelectionByDraftedEntityObj($draftedEntityObj) {
// 		$this->eiState->getManageState()->getDraftManager()->asdf();
// 	}
	
// 	/**
// 	 * @param EiSelection $eiSelection
// 	 * @return \rocket\spec\ei\mask\EiMask
// 	 */
// 	private function determineEiMask(EiSelection $eiSelection) {
// 		return $this->eiState->getContextEiMask()->determineEiMask(
// 					$this->determineEiSpec($eiSelection));
// 	}
	
// 	/**
// 	 * @param EiSelection $eiSelection
// 	 * @return \rocket\spec\ei\EiSpec
// 	 */
// 	private function determineEiSpec(EiSelection $eiSelection) {
// 		return $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->determineAdequateEiSpec(
// 				new \ReflectionClass($eiSelection->getLiveEntry()->getEntityObj()));
// 	}
	
// 	/**
// 	 * @param mixed $id
// 	 * @param int $draftId
// 	 * @throws UnknownEntryException
// 	 * @return \rocket\spec\ei\manage\EiSelection
// 	 */
// 	public function lookupDraftEiSelectionByIdAndDraftId($id, $draftId) {
// 		$eiSelection = $this->lookupEiSelectionById($id);
// 		$eiMask = $this->determineEiMask($eiSelection);
		
// 		if (!$eiMask->isDraftable()) {
// 			throw new UnknownEntryException('Drafts for Mask ' . (string) $eiMask . ' are disabled.');
// 		}
		
// 		$draftManager = $this->eiState->getManageState()->getDraftManager();
	
// 		$draft = $draftManager->find($eiSelection->getLiveEntityObj(), $draftId, $eiMask->getDraftDefinition());
// 		if ($draft === null) {
// 			throw new UnknownEntryException('Unknown draft id \'' . $draftId . '\' (Mask: ' . $eiMask . ')');
// 		} else if ($draft->getEntityObjId() !== $eiSelection->getId()) {
// 			throw new UnknownEntryException('Draft with id \'' . $draftId . '\' does not belong to Entity ' 
// 					. EntityInfo::buildEntityString($this->determineEiSpec($eiSelection)->getEntityModel(), $id));
// 		}
		
// 		$eiSelection->setDraft($draft);
		
// 		return $eiSelection;
// 	}
	
// 	/**
// 	 * @param mixed $id
// 	 * @throws UnknownEntryException
// 	 * @return \rocket\spec\ei\manage\EiSelection
// 	 */
// 	public function lookupLatestDraftEiSelectionById($id): EiSelection {
// 		$eiSelection = $this->lookupEiSelectionById($id);
// 		$eiMask = $this->determineEiMask($eiSelection);
		
// 		if (!$eiMask->isDraftingEnabled()) {
// 			return $eiSelection;
// 		}
		
// 		$draftManager = $this->eiState->getManageState()->getDraftManager();
		
// 		$drafts = $draftManager->findByEntityObjId($eiSelection->getLiveEntry()->getEntityObj(), $eiSelection->getLiveEntry()->getId(), 0, 1,
// 				$eiMask->getDraftDefinition());
// 		if (empty($drafts)) {
// 			return $eiSelection;
// 		}
		
// 		$draft = $drafts[0];
// 		if (!$draft->isPublished()) {
// 			$eiSelection->setDraft($draft);
// 		}
		
// 		return $eiSelection;
// 	}
	
	

// 	public function createEntityObject() {
// 		return ReflectionUtils::createObject($this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getEntityModel()->getClass());
// 	}
		
// 	public function createEntryManager(EiMapping $eiMapping = null, $applyToEiState = true) {
// 		$entryManager = new EntryManager($this->eiState, $eiMapping);
// // 		$entryManager->setDraftModel($this->draftModel);
// // 		$entryManager->setTranslationModel($this->translationModel);
// // 		if ($eiMapping !== null) {
// // 			$this->applyToEiState($eiMapping->getEiSelection());
// // 		}
// 		return $entryManager;
// 	}
	
	
// 	public function createEntryInfo(EiMapping $eiMapping) {
// 		$context = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec();
// 		$eiMask = $this->eiState->getContextEiMask()
// 				->determineEiMask($eiMapping->getEiSpec());
		
// 		$eiSelectionGui = $eiMask->createEiSelectionGui($this->eiState, $eiMapping, DisplayDefinition::VIEW_MODE_BULKY_READ, false);
		
// 		return new EntryInfo($eiMask, $eiSelectionGui, $eiMapping);
// 	}

	
	/**
	 * @param EiSelection $eiSelection
	 * @return \rocket\spec\ei\manage\mapping\EiMapping
	 * @throws \rocket\spec\ei\security\InaccessibleEntryException
	 */
	public function createEiMapping(EiSelection $eiSelection): EiMapping {
		return $this->determineEiMask($eiSelection)->getEiEngine()->createEiMapping($this->eiState, $eiSelection);
	}
	
	public function createEiMappingCopy(EiSelection $eiSelection, EiMapping $from): EiMapping {
		return $this->determineEiMask($eiSelection)->getEiEngine()->createEiMappingCopy($this->eiState, $eiSelection, $from);
	}
	
	public function createBulkyEntryGuiModel(EiMapping $eiMapping, bool $makeEditable) {
		return $this->determineEiMask($eiMapping)->createBulkyEntryGuiModel($this->eiState, $eiMapping, $makeEditable);
	}
	
	public function createDetailView(EiMapping $eiMapping) {
		$eiMask = $this->determineEiMask($eiMapping);

		$entryGuiModel = $eiMask->createBulkyEntryGuiModel($this->eiState, $eiMapping, false);
		return $eiMask->createBulkyView($this->eiState, new EntryGui($entryGuiModel));
	}
	
// 	public function createNewEiSelection(bool $draft = false) {
// 		$contextEiSpec = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec();
// 		$contextEiMask = $this->eiState->getContextEiMask();
// 		$eiSpecs = array_merge(array($contextEiSpec->getId() => $contextEiSpec), $contextEiSpec->getAllSubEispecs());
// 		foreach ($eiSpecs as $subId => $subEiSpec) {
// 			if ($subEiSpec->isAbstract()) {
// 				continue;
// 			}
			
// 			return $this->createEiSelectionFromEiSpec($subEiSpec, $draft);
// 		}
// 	}
	
	public function createNewEntryForm(bool $draft = false): EntryForm {
		$entryModelForms = array();
		$labels = array();
		
		$contextEiSpec = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec();
		$contextEiMask = $this->eiState->getContextEiMask();
		$eiSpecs = array_merge(array($contextEiSpec->getId() => $contextEiSpec), $contextEiSpec->getAllSubEispecs());
		foreach ($eiSpecs as $subEiSpecId => $subEiSpec) {
			if ($subEiSpec->getEntityModel()->getClass()->isAbstract()) {
				continue;
			}
				
			$eiSelection = $this->createNewEiSelection($draft, $subEiSpec);
			$subEiMapping = $this->createEiMapping($eiSelection);
						
			$entryModelForms[$subEiSpecId] = $this->createEntryModelForm($subEiSpec, $subEiMapping);
			$labels[$subEiSpecId] = $contextEiMask->determineEiMask($subEiSpec)->getLabelLstr()
					->t($this->eiState->getN2nLocale());
		}
		
		$entryForm = new EntryForm($this->eiState);
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
		$contextEiMask = $this->eiState->getContextEiMask();
		
		$entryForm = new EntryForm($this->eiState);
		$eiSpec = $eiMapping->getEiSpec();

		$entryForm->setEntryModelForms(array($eiSpec->getId() => $this->createEntryModelForm($eiSpec, $eiMapping)));
		$entryForm->setChosenId($eiSpec->getId());
		// @todo remove hack when ContentItemEiField gets updated.
		$entryForm->setChoicesMap(array($eiSpec->getId() => $contextEiMask->determineEiMask($eiSpec)->getLabelLstr()
				->t($this->eiState->getN2nLocale())));
		return $entryForm;
	}
	
	private function createEntryModelForm(EiSpec $eiSpec, EiMapping $eiMapping) {
		$eiMask = $this->getEiState()->getContextEiMask()->determineEiMask($eiSpec);
		
		$entryGuiModel = $eiMask->createBulkyEntryGuiModel($this->eiState, $eiMapping, true);
		return new EntryModelForm($entryGuiModel);
	}
	
	public function remove(EiSelection $eiSelection): VetoableRemoveAction {
		if ($eiSelection->isDraft()) {
			throw new NotYetImplementedException();
		}
		
		$eiCascadeOperation = new EiCascadeOperation($this->eiState->getManageState()->getEntityManager()->getEntityModelManager(),
				$this->eiState->getN2nContext()->lookup(Rocket::class)->getSpecManager(), CascadeType::REMOVE);
		$eiCascadeOperation->cascade($eiSelection->getLiveObject());
		
		$liveEntries = $eiCascadeOperation->getLiveEntries();
		$vetoableRemoveAction = new VetoableRemoveAction($liveEntries);
		
		foreach ($liveEntries as $liveEntry) {
			$liveEntry->getEiSpec()->onRemove($liveEntry, $vetoableRemoveAction, $this->eiState->getN2nContext());
			break;
		}
			
		if (!$vetoableRemoveAction->approve()) {
			return $vetoableRemoveAction;
		}
		
		$nss = $this->getNestedSetStrategy();
		if (null === $nss) {
			$this->eiState->getManageState()->getEntityManager()->remove($eiSelection->getLiveObject());
		} else {
			$nsu = new NestedSetUtils($this->eiState->getManageState()->getEntityManager(),
					$this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getEntityModel()->getClass(),
					$nss);
			$nsu->remove($eiSelection->getLiveObject());
		}
		
		return $vetoableRemoveAction;
	}

	public function lookupPreviewController(string $previewType, EiSelection $eiSelection) {
		$previewModel = new PreviewModel($previewType, $eiSelection, $eiSelection->getLiveObject());
		
		$previewType = $previewModel->getPreviewType();
		return $this->getEiMask()->lookupPreviewController($this->eiState, $previewModel);
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
		
		$previewController = $eiMask->lookupPreviewController($this->eiState);
		$previewTypeOptions = $previewController->getPreviewTypeOptions($this->eiState, $eiSelection);
		ArgUtils::valArrayReturn($previewTypeOptions, $previewController, 'getPreviewTypeOptions', 
				array('string', Lstr::class));
		
		return $previewTypeOptions;
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

		$entityModel = $this->entityModelManager->getEntityModelByEntity($entityObj);
		
		$this->liveEntries[] = LiveEntry::createFrom($this->specManager
				->getEiSpecByClass($entityModel->getClass()), $entityObj);
		
		$this->cascader->cascadeProperties($entityModel, $entityObj);
	}
	
	public function getLiveEntries(): array {
		return $this->liveEntries;
	}
}

// 	private function createEntryFormPart(EiSpec $eiSpec, EiMapping $eiMapping, $levelOnly) {
// 		$eiMask = $this->eiState->getContextEiMask()->determineEiMask($eiSpec);
// 		$eiSelection = $eiMapping->getEiSelection();
// 		$guiDefinition = $eiMask->createGuiDefinition($this->eiState, $eiSelection->isDraft(), $levelOnly);
// 		return new EntryFormPart($guiDefinition, $this->eiState, $eiMapping);
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

// 		if ($eiSpec->equals($this->eiState->getContextEiMask()->getEiEngine()->getEiSpec())) {
// 			$entryForm->setMainEntryFormPart(
// 					$this->createEntryFormPart($eiSpec, $eiMapping, false));
// 		} else {
// 			$entryForm->addLevelEntryFormPart(
// 					$this->createEntryFormPart($eiSpec, $eiMapping, true));
// 		}

// 		return $eiMapping;
// 	}
