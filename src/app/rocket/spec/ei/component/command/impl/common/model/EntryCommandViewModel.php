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

use rocket\spec\ei\manage\EiFrame;
use n2n\util\uri\Url;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\model\EntryGuiModel;
use rocket\spec\ei\manage\EntryGui;
use n2n\web\http\HttpContext;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\draft\Draft;
use rocket\user\model\RocketUserDao;

class EntryCommandViewModel {
	private $title;
	private $eiFrameUtils;
	private $eiFrame;
	private $entryGuiModel;
	private $eiSelection;
	private $cancelUrl;
	private $eiMask;
	
	public function __construct(EiuFrame $eiFrameUtils, $entryGuiModel, Url $cancelUrl = null) {
		$this->eiFrameUtils = $eiFrameUtils;
		$this->eiFrame = $eiFrameUtils->getEiFrame();
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
		
		return $this->eiFrame->getContextEiMask();
	}
	
	public function getTitle() {
		if ($this->title !== null) return $this->title;
			
		$eiSelection = $this->getEiSelection();
		
		return $this->title = $this->getEiuEntry()->createIdentityString();
		
// 		if ($this->entryGuiModel && !$eiSelection->isNew()) {
// 			return $this->title = $this->entryGuiModel->getEiMask()
// 					->createIdentityString($eiSelection, $this->eiFrame->getN2nLocale());
// 		} 
		
// 		if ($this->entryGuiModel !== null) {
// 			return $this->title = $this->entryGuiModel->getEiMask()->getLabelLstr()->t($this->eiFrame->getN2nLocale());
// 		}
		
// 		return $this->title = $this->eiFrame->getContextEiMask()->getLabelLstr()->t($this->eiFrame->getN2nLocale());
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function getEiuFrame() {
		return $this->eiFrameUtils;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function getEiuEntry() {
		return $this->eiFrameUtils->toEiuEntry($this->eiSelection);
	}
	
	public function getEiFrame() {
		return $this->eiFrame;
	}
	
	private $latestDraft = null;
	private $historicizedDrafts = array();
	
	public function initializeDrafts() {
		$entryEiuEntry = $this->getEiuEntry();
		if ($entryEiuEntry->hasLiveId() && $this->eiFrameUtils->isDraftingEnabled()) {
			$this->historicizedDrafts = $entryEiuEntry->lookupDrafts(0, 30);
		}
		
		if ($this->eiSelection->isDraft() && $this->eiSelection->isNew()) {
			$this->latestDraft = $this->eiSelection->getDraft();
		}
	
		if (empty($this->historicizedDrafts) || $this->latestDraft !== null) return;
		
		$latestDraft = reset($this->historicizedDrafts);
		if (!$latestDraft->isPublished()) {
			$this->latestDraft = $latestDraft;
			array_shift($this->historicizedDrafts);
		}
	}
	
	public function hasDraftHistory() {
		return $this->latestDraft !== null || !empty($this->historicizedDrafts);
	}
	
	public function getSelectedDraft() {
		if ($this->getEiSelection()->isDraft()) {
			return $this->getEiSelection()->getDraft();
		}
		
		return null;
	}
	
	public function getLatestDraft() {
		return $this->latestDraft;
	}
	
	public function getHistoricizedDrafts() {
		return $this->historicizedDrafts;
	}
	
	public function isPreviewAvailable() {
		return $this->getEiuEntry()->isPreviewAvailable();
	}
	
	public function getEntryGuiModel(): EntryGuiModel {
		if ($this->entryGuiModel === null) {
			throw new IllegalStateException();
		}
		
		return $this->entryGuiModel;
	}
	
	
// 	public function getInfoPathExt() {
// 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiFrame->toEntryNavPoint()->copy(false, false, true));
// 	}
	
// 	public function getPreviewPathExt() {
// 		$previewType = $this->eiFrame->getPreviewType();
// 		if (is_null($previewType)) $previewType = PreviewController::PREVIEW_TYPE_DEFAULT;

// 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiFrame->toEntryNavPoint(null, null, $previewType));
// 	}
	
// 	public function getCurrentPreviewType() {
// 		return $this->currentPreviewType;
// 	}
	
// 	public function setCurrentPreviewType($currentPreviewType) {
// 		$this->currentPreviewType = $currentPreviewType;
// 	}
	
	public function getPreviewTypOptions() {
		return $this->eiMask->getPreviewTypeOptions($this->eiFrame, 
				$this->entryGuiModel->getEiSelectionGui()->getViewMode());
	}
	
	public function getLiveEntryUrl(HttpContext $httpContext) {
		return $this->eiFrame->getDetailUrl($httpContext, $this->entryGuiModel->getEiMapping()->toEntryNavPoint());
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
			return $this->eiFrame->getOverviewUrl($httpContext);
		}
		
		return $this->eiFrame->getDetailUrl($httpContext, $this->entryGuiModel->getEiMapping()->toEntryNavPoint());	
	}
	
	public function createDetailView() {
		return $this->entryGuiModel->getEiMask()->createBulkyView($this->eiFrame, new EntryGui($this->entryGuiModel));
	}
}
// class EntryViewInfo {
// 	private $eiFrame;
// 	private $commandEntryModel;
// 	private $entryModel;
// 	private $eiSelection;
// 	private $context;
// 	private $exact;
// 	private $previewController;
// 	private $title;
	
// 	public function __construct(CommandEntryModel $commandEntryModel = null, EntryModel $entryModel, PreviewController $previewController = null, $title = null) {
// 		$this->eiFrame = $entryModel->getEiFrame();
// 		$this->commandEntryModel = $commandEntryModel;
// 		$this->entryModel = $entryModel;
// 		$this->eiSelection = $this->entryModel->getEiSelection();
		
// 		$this->context = $this->eiFrame->getContextEiMask()->getEiEngine()->getEiSpec();
// 		$this->exact = $this->entryModel->getEiSpec();
		
// 		$this->previewController = $previewController;
		
// 		if (isset($title)) {
// 			$this->title = $title;
// 		} else {
// 			$this->title = $this->exact->createIdentityString($this->eiSelection->getEntityObj(),
// 					$this->eiFrame->getN2nLocale());
// 		}
// 	}
	
// 	public function getTitle() {
// 		return $this->title;
// 	}
	
// 	public function getEiFrame()  {
// 		return $this->eiFrame;
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
// 						$this->eiFrame->toEntryNavPoint()->copy(false, true, false)),
// 				'label' => $mainTranslationN2nLocale->getName($this->eiFrame->getN2nLocale()),
// 				'active' => null === $currentTranslationN2nLocale);

// 		foreach ($this->commandEntryModel->getTranslationN2nLocales() as $translationN2nLocale) {
// 			$navPoints[] = array(
// 					'pathExt' => PathUtils::createPathExtFromEntryNavPoint(null, 
// 							$this->eiFrame->toEntryNavPoint(null, $translationN2nLocale)),
// 					'label' => $translationN2nLocale->getName($this->eiFrame->getN2nLocale()),
// 					'active'=> $translationN2nLocale->equals($currentTranslationN2nLocale));
// 		}
		
// 		return $navPoints;
// 	}
	
// 	public function getLiveEntryPathExt() {
// 		$previewType = $this->eiFrame->getPreviewType();
// 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiFrame->toEntryNavPoint()->copy(true));
// 	}
	
// 	private function ensureCommandEntryModel() {
// 		if (!isset($this->commandEntryModel)) {
// 			throw IllegalStateException::createDefault();
// 		}
// 	}
	
// 	public function buildPathToDraft(Draft $draft) {
// 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiFrame->toEntryNavPoint($draft->getId()));
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
		
// 		$currentPreviewType = $this->eiFrame->getPreviewType();
// 		$navPoints = array();
// 		foreach ((array) $this->previewController->getPreviewTypeOptions() as $previewType => $label) {
// 			$navPoints[(string) $previewType] = array('label' => $label,
// 					'pathExt' => PathUtils::createPathExtFromEntryNavPoint(null, $this->eiFrame->toEntryNavPoint(null, null, $previewType)),
// 					'active' => ($previewType == $currentPreviewType));
// 		}
// 		return $navPoints;
// 	}
	
// // 	public function get
// }
