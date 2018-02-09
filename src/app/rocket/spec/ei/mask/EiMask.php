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
namespace rocket\spec\ei\mask;

use rocket\spec\ei\manage\EiFrame;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\EiType;
use rocket\util\Identifiable;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\EiDef;
use rocket\spec\ei\manage\preview\model\PreviewModel;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\mask\model\DisplayScheme;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use rocket\spec\ei\EiEngine;
use n2n\l10n\Lstr;
use rocket\spec\ei\manage\preview\controller\PreviewController;
use n2n\util\config\InvalidConfigurationException;
use rocket\spec\ei\manage\preview\model\UnavailablePreviewException;
use rocket\spec\ei\manage\control\UnavailableControlException;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\gui\EiGui;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\manage\control\IconType;
use rocket\spec\ei\manage\gui\SummarizedStringBuilder;
use rocket\spec\ei\manage\gui\EiGuiViewFactory;
use rocket\spec\ei\manage\gui\ViewMode;
use rocket\spec\ei\EiEngineModel;
use rocket\spec\ei\mask\model\CommonEiGuiViewFactory;
use n2n\util\ex\IllegalStateException;

class EiMask implements EiEngineModel, Identifiable {
	private $id;
	private $eiType;
	private $moduleNamespace;
	private $subEiMaskIds;
	private $decoratedEiMasks;
	
	private $eiDef;
	private $eiEngine;
	private $displayScheme;
	
	private $mappingFactory;
	private $guiFactory;
	private $draftDefinitionFactory;
	private $critmodFactory;
	
	private $guiDefinition;
	private $draftDefinition;
	
	public function __construct(EiType $eiType, string $moduleNamespace, DisplayScheme $guiOrder) {
		$this->eiType = $eiType;
		$this->moduleNamespace = $moduleNamespace;
		
		$this->eiDef = new EiDef();
		$this->eiEngine = new EiEngine($this->eiType, $this);
		$this->displayScheme = $guiOrder;
		
		$eiPropCollection = $this->eiEngine->getEiPropCollection();
		$eiPropCollection->setInheritedCollection($this->eiType->getEiEngine()->getEiPropCollection());
		
		$eiCommandCollection = $this->eiEngine->getEiCommandCollection();
		$eiCommandCollection->setInheritedCollection($this->eiType->getEiEngine()->getEiCommandCollection());
		
		$eiModificatorCollection = $this->eiEngine->getEiModificatorCollection();
		$eiModificatorCollection->setInheritedCollection($this->eiType->getEiEngine()->getEiModificatorCollection());
	}

	public function hasId() {
		return $this->id !== null;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\util\Identifiable::getId()
	 */
	public function getId(): string {
		IllegalStateException::assertTrue($this->id !== null, 'Id not defined.');
		return $this->id;
	}
	
	/**
	 * @param string $id
	 */
	public function setId(string $id = null) {
		$this->id = $id;
	}

// 	public function getEiThingPath(): EiThingPath {
// 		$ids = array();
// 		$curEiThing = $this;
// 		do {
// 			if (null !== ($id = $curEiThing->getId())) {
// 				$ids[] = $id;
// 			}
// 		} while (null !== ($curEiThing = $curEiThing->getMaskedEiThing()));

// 		return new EiThingPath($ids);
// 	}
	
	public function getModuleNamespace(): string {
		return $this->moduleNamespace;
	}
	
	public function setModuleNamespace(string $moduleNamespace) {
		$this->moduleNamespace = $moduleNamespace;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\mask\EiMask::get()
	 */
	public function getEiType(): EiType {
		return $this->eiType;
	}
	
// 	public function getMaskedEiThing(): EiThing {
// 		return $this->eiType;
// 	}
	
	public function getEiDef() {
		return $this->eiDef;
	}
	
	public function getEiEngine(): EiEngine {
		return $this->eiEngine;
	}
	
	public function setDisplayScheme(DisplayScheme $displayScheme) {
		$this->displayScheme = $displayScheme;
	}
	
	/**
	 * @return DisplayScheme
	 */
	public function getDisplayScheme() {
		return $this->displayScheme;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\EiEngineModel::getLabelLstr()
	 */
	public function getLabelLstr(): Lstr {
		if (null !== ($label = $this->eiDef->getLabel())) {
			return new Lstr($label, $this->moduleNamespace);
		}
		
		return new Lstr((string) $this->eiType->getDefaultEiDef()->getLabel(), $this->moduleNamespace);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\EiEngineModel::getPluralLabelLstr()
	 */
	public function getPluralLabelLstr(): Lstr {
		if (null !== ($pluralLabel = $this->eiDef->getPluralLabel())) {
			return new Lstr($pluralLabel, $this->moduleNamespace);
		}
		
		return new Lstr((string) $this->eiType->getDefaultEiDef()->getPluralLabel(), $this->moduleNamespace);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\EiEngineModel::getIconType()
	 */
	public function getIconType(): string {
		if (null !== ($iconType = $this->eiDef->getIconType())) {
			return $iconType;
		}
		
		if (null !== ($iconType = $this->eiType->getDefaultEiDef()->getIconType())) {
			return $iconType;
		}
		
		return IconType::ICON_FILE_TEXT;
	}
	
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\mask\EiMask::isDraftDisabled()
	 */
	public function isDraftingEnabled(): bool {
		return false;
// 		if (null !== ($draftingAllowed = $this->eiDef->isDraftingAllowed())) {
// 			if (!$draftingAllowed) return false;
// 		} else if (null !== ($draftingAllowed = $this->eiType->getDefaultEiDef()->isDraftingAllowed())) {
// 			if (!$draftingAllowed) return false;
// 		}
		
// 		return !$this->eiEngine->getDraftDefinition()->isEmpty();
	}
	
	private function createDefaultIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		$idPatternPart = null;
		$namePatternPart = null;
		
		foreach ($this->eiEngine->getGuiDefinition()->getStringRepresentableGuiProps() as $guiIdPathStr => $guiProp) {
			if ($guiIdPathStr == $this->eiEngine->getEiType()->getEntityModel()->getIdDef()->getPropertyName()) {
				$idPatternPart = SummarizedStringBuilder::createPlaceholder($guiIdPathStr);
			} else {
				$namePatternPart = SummarizedStringBuilder::createPlaceholder($guiIdPathStr);
			}
			
			if ($namePatternPart !== null) break;
		}
		
		if ($idPatternPart === null) {
			$idPatternPart = $eiObject->getEiEntityObj()->hasId() ? 
					$this->eiType->idToEiId($eiObject->getEiEntityObj()->getId()) : 'new';
		}
		
		if ($namePatternPart === null) {
			$namePatternPart = $this->getLabelLstr()->t($n2nLocale);
		}
		
		return $this->eiEngine->getGuiDefinition()->createIdentityString($namePatternPart . ' #' . $idPatternPart, $eiObject, $n2nLocale);
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\mask\EiMask::createIdentityString()
	 */
	public function createIdentityString(EiObject $eiObject, N2nLocale $n2nLocale): string {
		$identityStringPattern = $this->eiDef->getIdentityStringPattern();
		
		if ($identityStringPattern === null) {
			$identityStringPattern = $this->eiType->getDefaultEiDef()->getIdentityStringPattern();
		}
		
		if ($identityStringPattern === null) {
			return $this->createDefaultIdentityString($eiObject, $n2nLocale);
		}
		
		return $this->eiEngine->getGuiDefinition()
				->createIdentityString($identityStringPattern, $eiObject, $n2nLocale);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\mask\EiMask::sortOverallControls()
	 */
	public function sortOverallControls(array $controls, EiGui $eiGui, HtmlView $view): array {
// 		$eiu = new Eiu($eiGui);
// 		$eiPermissionManager = $eiu->frame()->getEiFrame()->getManageState()->getEiPermissionManager();
		
// 		$controls = array();
		
// 		foreach ($this->eiEngine->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof OverallControlComponent)
// 					|| !$eiPermissionManager->isEiCommandAccessible($eiCommand)) continue;
				
// 			$controls = $eiCommand->createOverallControls($eiu, $view);
// 			ArgUtils::valArrayReturn($controls, $eiCommand, 'createOverallControls', Control::class);
// 			foreach ($controls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
// 			}
// 		}
		
		if (null !== ($overallControlOrder = $this->displayScheme->getOverallControlOrder())) {
			return $overallControlOrder->sort($controls);
		}
	
		return $controls;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\mask\EiMask::createEntryControls()
	 */
	public function sortEntryControls(array $controls, EiEntryGui $eiEntryGui, HtmlView $view): array {
// 		$eiu = new Eiu($eiEntryGui);
		
// 		$controls = array();
// 		foreach ($this->eiEngine->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof EntryControlComponent)
// 					|| !$eiEntryGui->getEiEntry()->isExecutableBy(EiCommandPath::from($eiCommand))) {
// 				continue;
// 			}
			
// 			$entryControls = $eiCommand->createEntryControls($eiu, $view);
// 			ArgUtils::valArrayReturn($entryControls, $eiCommand, 'createEntryControls', Control::class);
// 			foreach ($entryControls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
// 			}
// 		}
	
// 		if (null !== ($entryControlOrder = $this->guiOrder->getEntryControlOrder())) {
// 			return $entryControlOrder->sort($controls);
// 		}		
		
		return $controls;
	}
	
// 	public function createPartialControls(EiFrame $eiFrame, HtmlView $view): array {
// 		$controls = array();
// 		foreach ($this->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof PartialControlComponent)
// 					|| !$eiFrame->getManageState()->getEiPermissionManager()->isEiCommandAccessible($eiCommand)) continue;
				
// 			$executionPath = EiCommandPath::from($eiCommand);
// 			$partialControls = $eiCommand->createPartialControls($eiFrame, $view);
// 			ArgUtils::valArrayReturn($partialControls, $eiCommand, 'createPartialControls', PartialControl::class);
// 			foreach ($partialControls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
				
// 				if (!$control->hasEiCommandPath()) {
// 					$control->setExecutionPath($executionPath->ext($controlId));
// 				}
// 			}
// 		}
		
// 		if (null !== ($overallControlOrder = $this->guiOrder->getOverallControlOrder())) {
// 			return $overallControlOrder->sortControls($controls);
// 		}
	
// 		return $controls;
// 	}
	
	/**
	 * @param EiGui $eiGui
	 * @return EiGuiViewFactory
	 */
	public function createEiGuiViewFactory(EiGui $eiGui): EiGuiViewFactory {
		$displayStructure = null;
		switch ($eiGui->getViewMode()) {
			case ViewMode::BULKY_READ:
				$displayStructure = $this->displayScheme->getDetailDisplayStructure()
						?? $this->displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_EDIT:
				$displayStructure = $this->displayScheme->getEditDisplayStructure()
						?? $this->displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_ADD:
				$displayStructure = $this->displayScheme->getAddDisplayStructure()
						?? $this->displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::COMPACT_READ:
			case ViewMode::COMPACT_EDIT:
			case ViewMode::COMPACT_ADD:
				$displayStructure = $this->displayScheme->getOverviewDisplayStructure();
				break;
		}
		
		if ($displayStructure === null) {
			$displayStructure = $this->eiEngine->getGuiDefinition()->createDefaultDisplayStructure($eiGui);
		} else {
			$displayStructure = $this->eiEngine->getGuiDefinition()->purifyDisplayStructure($displayStructure, $eiGui);
		}
		
		$displayStructure = $displayStructure->whitoutAutonomics();
		
		return new CommonEiGuiViewFactory($eiGui, $this->eiEngine->getGuiDefinition(), $displayStructure);
	}
	
	public function getSubEiMaskIds() {
		return $this->subEiMaskIds;
	}
	
	public function setSubEiMaskIds(array $subEiMaskIds) {
		$this->subEiMaskIds = $subEiMaskIds;
	}
	
	public function determineEiMask(EiType $eiType): EiMask {
		$eiTypeId = $eiType->getId();
		if ($this->eiType->getId() == $eiTypeId) {
			return $this;
		}
		
		if ($this->eiType->containsSubEiTypeId($eiTypeId)) {
			return $this->getSubEiMaskByEiTypeId($eiTypeId);
		}
				
		foreach ($this->eiType->getSubEiTypes() as $subEiType) {
			if (!$subEiType->containsSubEiTypeId($eiTypeId, true)) continue;
			return $this->getSubEiMaskByEiTypeId($subEiType->getId())
					->determineEiMask($eiType);
		}
		
		throw new \InvalidArgumentException();
	}
	
	public function getSubEiMaskByEiTypeId($eiTypeId): EiMask {
		$subMaskIds = $this->getSubEiMaskIds();
		
		foreach ($this->eiType->getSubEiTypes() as $subEiType) {
			if ($subEiType->getId() != $eiTypeId) continue;
			
			if (isset($subMaskIds[$eiTypeId])) {
				return $subEiType->getEiMaskCollection()->getById($subMaskIds[$eiTypeId]);
			} else {
				return $subEiType->getEiMaskCollection()->getOrCreateDefault();
			}
		}
		
		throw new \InvalidArgumentException('EiType ' . $eiTypeId . ' is no SubEiType of ' 
				. $this->eiType->getId());
	}
	
	public function isPreviewSupported(): bool {
		return null !== $this->eiDef->getPreviewControllerLookupId() 
				|| null !== $this->eiType->getDefaultEiDef()->getPreviewControllerLookupId();
	}
	
	public function lookupPreviewController(EiFrame $eiFrame, PreviewModel $previewModel = null): PreviewController {
		$lookupId = $this->eiDef->getPreviewControllerLookupId();
		if (null === $lookupId) {
			$lookupId = $this->eiType->getDefaultEiDef()->getPreviewControllerLookupId();	
		}
		
		if ($lookupId === null) {
			throw new UnavailablePreviewException('No PreviewController available for EiMask: ' . $this);
		}
		
		$previewController = $eiFrame->getN2nContext()->lookup($lookupId);
		if (!($previewController instanceof PreviewController)) {
			throw new InvalidConfigurationException('PreviewController must implement ' . PreviewController::class 
					. ': ' . get_class($previewController));
		}
		
		if ($previewModel === null) {
			return $previewController;
		}
		
		if (!array_key_exists($previewModel->getPreviewType(), $previewController->getPreviewTypeOptions(new Eiu($eiFrame, $previewModel->getEiObject())))) {
			throw new UnavailableControlException('Unknown preview type \'' . $previewModel->getPreviewType() 
					. '\' for PreviewController: ' . get_class($previewController));
		}
		
		$previewController->setPreviewModel($previewModel);
		return $previewController;
	}
	
	public function __toString(): string {
		if ($this->id !== null) {
			return 'EiMask (id: ' . $this->id . ') of ' . $this->eiType;
		}
		
		return 'Default EiMask of ' . $this->eiType;
	}
	
	/**
	 * @todo move to EiEngine!!
	 * @param EiFrame $eiFrame
	 */
	public function setupEiFrame(EiFrame $eiFrame) {
		if (null !== ($filterGroupData = $this->eiDef->getFilterGroupData())
				|| null !== ($filterGroupData = $this->eiType->getDefaultEiDef()->getFilterGroupData())) {
			$criteriaConstraint = $this->createManagedFilterDefinition($eiFrame)
					->buildCriteriaConstraint($filterGroupData, false);
			if ($criteriaConstraint !== null) {
				$eiFrame->addCriteriaConstraint($criteriaConstraint);
			}
		}

		if (null !== ($defaultSortData = $this->eiDef->getDefaultSortData())
				|| null !== ($defaultSortData = $this->eiType->getDefaultEiDef()->getDefaultSortData())) {
			$criteriaConstraint = $this->eiEngine->createManagedSortDefinition($eiFrame)
					->builCriteriaConstraint($defaultSortData, false);
			if ($criteriaConstraint !== null) {
				$eiFrame->getCriteriaConstraintCollection()->add(CriteriaConstraint::TYPE_HARD_SORT, $criteriaConstraint);
			}
		}

		$eiu = new Eiu($eiFrame);
		foreach ($this->eiEngine->getEiModificatorCollection()->toArray() as $modificator) {
			$modificator->setupEiFrame($eiu);
		}
	}

}
