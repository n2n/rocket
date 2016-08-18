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
namespace rocket\spec\ei\component\command\impl\common\model;

use rocket\spec\ei\manage\EiState;
use n2n\util\uri\Url;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\model\EntryGuiModel;
use rocket\spec\ei\manage\EntryGui;
use n2n\http\HttpContext;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\util\model\EiStateUtils;


class EntryCommandViewModel {
	private $title;
	private $eiStateUtils;
	private $eiState;
	private $entryGuiModel;
	private $eiSelection;
	private $cancelUrl;
	private $eiMask;
	
	public function __construct(EiStateUtils $eiStateUtils, $entryGuiModel, Url $cancelUrl = null) {
		$this->eiStateUtils = $eiStateUtils;
		$this->eiState = $eiStateUtils->getEiState();
		if ($entryGuiModel instanceof EntryGuiModel) {
			$this->entryGuiModel = $entryGuiModel;
			$this->eiSelection = $entryGuiModel->getEiMapping()->getEiSelection();
		} else if ($entryGuiModel instanceof EiSelection) {
			$this->eiSelection = $entryGuiModel;
		}
		$this->cancelUrl = $cancelUrl;
	}
	
	public function getEiSelection() {
		return $this->eiSelection;
	}
	
	private function getEiMask() {
		if ($this->entryGuiModel !== null) {
			return $this->entryGuiModel->getEiMask();
		}
		
		return $this->eiState->getContextEiMask();
	}
	
	public function getTitle() {
		if ($this->title !== null) return $this->title;
			
		$eiSelection = $this->getEiSelection();
		
		return $this->title = $this->getEiEntryUtils()->createIdentityString();
		
// 		if ($this->entryGuiModel && !$eiSelection->isNew()) {
// 			return $this->title = $this->entryGuiModel->getEiMask()
// 					->createIdentityString($eiSelection, $this->eiState->getN2nLocale());
// 		} 
		
// 		if ($this->entryGuiModel !== null) {
// 			return $this->title = $this->entryGuiModel->getEiMask()->getLabelLstr()->t($this->eiState->getN2nLocale());
// 		}
		
// 		return $this->title = $this->eiState->getContextEiMask()->getLabelLstr()->t($this->eiState->getN2nLocale());
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\util\model\EiStateUtils
	 */
	public function getEiStateUtils() {
		return $this->eiStateUtils;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\util\model\EiEntryUtils
	 */
	public function getEiEntryUtils() {
		return $this->eiStateUtils->toEiEntryUtils($this->eiSelection);
	}
	
	public function getEiState() {
		return $this->eiState;
	}
	
	private $historicizedDrafts = array();
	
	public function hasDraftHistory() {
		return !empty($this->historicizedDrafts);
	}
	
	public function setLatestDrafts(array $latestDrafts) {
		$this->historicizedDrafts = $latestDrafts;
	}
	
	public function getCurrentDraft() {
		return $this->getEiSelection()->getDraft();
	}
	
	public function getHistoricizedDrafts() {
		$eiSelection = $this->getEiSelection();
		
		// @todo limit and num
		return $this->eiState->getManageState()->getDraftManager()->findByEntityObjId(
				$eiSelection->getLiveEntityObj(), $eiSelection->getId(), 
				0, 30, $this->entryGuiModel->getEiMask()->getDraftDefinition());
	}
	
	public function isPreviewAvailable() {
		return $this->getEiEntryUtils()->isPreviewAvailable();
	}
	
	public function getEntryGuiModel(): EntryGuiModel {
		if ($this->entryGuiModel === null) {
			throw new IllegalStateException();
		}
		
		return $this->entryGuiModel;
	}
	
	
// 	public function getInfoPathExt() {
// 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiState->toEntryNavPoint()->copy(false, false, true));
// 	}
	
// 	public function getPreviewPathExt() {
// 		$previewType = $this->eiState->getPreviewType();
// 		if (is_null($previewType)) $previewType = PreviewController::PREVIEW_TYPE_DEFAULT;

// 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiState->toEntryNavPoint(null, null, $previewType));
// 	}
	
// 	public function getCurrentPreviewType() {
// 		return $this->currentPreviewType;
// 	}
	
// 	public function setCurrentPreviewType($currentPreviewType) {
// 		$this->currentPreviewType = $currentPreviewType;
// 	}
	
	public function getPreviewTypOptions() {
		return $this->eiMask->getPreviewTypeOptions($this->eiState, 
				$this->entryGuiModel->getEiSelectionGui()->getViewMode());
	}
	
	public function getLiveEntryUrl(HttpContext $httpContext) {
		return $this->eiState->getDetailUrl($httpContext,
				$this->entryGuiModel->getEiMapping()->toEntryNavPoint());
	}
	
	public function setCancelUrl(Url $cancelUrl) {
		$this->cancelUrl = $cancelUrl;
	}
	
	public function determineCancelUrl(HttpContext $httpContext) {
		if ($this->cancelUrl !== null) {
			return $this->cancelUrl;
		}
		
		$eiSelection = $this->getEiSelection();
		
		if ($eiSelection === null || $eiSelection->isNew()) {
			return $this->eiState->getOverviewUrl($httpContext);
		}
		
		return $this->eiState->getDetailUrl($httpContext, 
				$this->entryGuiModel->getEiMapping()->toEntryNavPoint());	
	}
	
	public function createDetailView() {
		return $this->entryGuiModel->getEiMask()->createBulkyView($this->eiState, new EntryGui($this->entryGuiModel));
	}
}
// class EntryViewInfo {
// 	private $eiState;
// 	private $commandEntryModel;
// 	private $entryModel;
// 	private $eiSelection;
// 	private $context;
// 	private $exact;
// 	private $previewController;
// 	private $title;
	
// 	public function __construct(CommandEntryModel $commandEntryModel = null, EntryModel $entryModel, PreviewController $previewController = null, $title = null) {
// 		$this->eiState = $entryModel->getEiState();
// 		$this->commandEntryModel = $commandEntryModel;
// 		$this->entryModel = $entryModel;
// 		$this->eiSelection = $this->entryModel->getEiSelection();
		
// 		$this->context = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec();
// 		$this->exact = $this->entryModel->getEiSpec();
		
// 		$this->previewController = $previewController;
		
// 		if (isset($title)) {
// 			$this->title = $title;
// 		} else {
// 			$this->title = $this->exact->createIdentityString($this->eiSelection->getEntityObj(),
// 					$this->eiState->getN2nLocale());
// 		}
// 	}
	
// 	public function getTitle() {
// 		return $this->title;
// 	}
	
// 	public function getEiState()  {
// 		return $this->eiState;
// 	}
	
// 	public function getEiSelection() {
// 		return $this->eiSelection;
// 	}
	
// 	public function isInEditMode() {
// 		return $this->entryModel instanceof EditEntryModel;
// 	}
	
// 	public function isNew() {
// 		return $this->entryModel instanceof EditEntryModel && $this->entryModel->isNew();
// 	}
	

	
// 	public function getLangNavPoints() {
// 		$currentTranslationN2nLocale = $this->eiSelection->getTranslationN2nLocale();
		
// 		$navPoints = array();
		
// 		$this->ensureCommandEntryModel();
		
// 		$mainTranslationN2nLocale = $this->commandEntryModel->getMainTranslationN2nLocale();
// 		$navPoints[] = array(
// 				'pathExt' => PathUtils::createPathExtFromEntryNavPoint(null, 
// 						$this->eiState->toEntryNavPoint()->copy(false, true, false)),
// 				'label' => $mainTranslationN2nLocale->getName($this->eiState->getN2nLocale()),
// 				'active' => null === $currentTranslationN2nLocale);

// 		foreach ($this->commandEntryModel->getTranslationN2nLocales() as $translationN2nLocale) {
// 			$navPoints[] = array(
// 					'pathExt' => PathUtils::createPathExtFromEntryNavPoint(null, 
// 							$this->eiState->toEntryNavPoint(null, $translationN2nLocale)),
// 					'label' => $translationN2nLocale->getName($this->eiState->getN2nLocale()),
// 					'active'=> $translationN2nLocale->equals($currentTranslationN2nLocale));
// 		}
		
// 		return $navPoints;
// 	}
	
// 	public function getLiveEntryPathExt() {
// 		$previewType = $this->eiState->getPreviewType();
// 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiState->toEntryNavPoint()->copy(true));
// 	}
	
// 	private function ensureCommandEntryModel() {
// 		if (!isset($this->commandEntryModel)) {
// 			throw IllegalStateException::createDefault();
// 		}
// 	}
	
// 	public function buildPathToDraft(Draft $draft) {
// 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiState->toEntryNavPoint($draft->getId()));
// 	}
	
// 	public function getCurrentDraft() {
// 		$this->ensureCommandEntryModel();
// 		return $this->commandEntryModel->getCurrentDraft();
// 	}
	
// 	public function getHistoricizedDrafts() {
// 		$this->ensureCommandEntryModel();
		
// 		return $this->commandEntryModel->getHistoricizedDrafts();
// 	}
	
// 	public function isInPeview() {
// 		return isset($this->previewController);
// 	}
	
// 	public function hasPreviewTypeNav() {
// 		return isset($this->previewController) && sizeof((array) $this->previewController->getPreviewTypeOptions());
// 	}
	
// 	public function getPreviewTypeNavInfos() {
// 		if (is_null($this->previewController)) return array();
		
// 		$currentPreviewType = $this->eiState->getPreviewType();
// 		$navPoints = array();
// 		foreach ((array) $this->previewController->getPreviewTypeOptions() as $previewType => $label) {
// 			$navPoints[(string) $previewType] = array('label' => $label,
// 					'pathExt' => PathUtils::createPathExtFromEntryNavPoint(null, $this->eiState->toEntryNavPoint(null, null, $previewType)),
// 					'active' => ($previewType == $currentPreviewType));
// 		}
// 		return $navPoints;
// 	}
	
// // 	public function get
// }
