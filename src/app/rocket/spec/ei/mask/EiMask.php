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
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\preview\model\PreviewModel;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\mask\model\DisplayScheme;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use rocket\spec\ei\EiEngine;
use rocket\spec\ei\manage\preview\controller\PreviewController;
use n2n\util\config\InvalidConfigurationException;
use rocket\spec\ei\manage\preview\model\UnavailablePreviewException;
use rocket\spec\ei\manage\control\UnavailableControlException;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\gui\EiGui;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\manage\gui\SummarizedStringBuilder;
use rocket\spec\ei\manage\gui\EiGuiViewFactory;
use rocket\spec\ei\manage\gui\ViewMode;
use rocket\spec\ei\mask\model\CommonEiGuiViewFactory;
use rocket\spec\ei\component\prop\EiPropCollection;
use rocket\spec\ei\component\command\EiCommandCollection;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use n2n\util\ex\IllegalStateException;
use n2n\l10n\Lstr;
use rocket\spec\ei\manage\control\IconType;

class EiMask {
	private $eiMaskDef;
	private $eiType;
	private $subEiMaskIds;
	
	private $eiPropCollection;
	private $eiCommandCollection;
	private $eiModificatorCollection;
	
	private $displayScheme;
	private $eiMaskExtension;
	
	private $eiEngine;
	private $mappingFactory;
	private $guiFactory;
	private $draftDefinitionFactory;
	private $critmodFactory;
	
	private $guiDefinition;
	private $draftDefinition;
	
	public function __construct(EiType $eiType) {
		$this->eiType = $eiType;
		
		$this->eiMaskDef = new EiMaskDef();

		$this->eiEngine = new EiEngine($this);
		$this->eiPropCollection = new EiPropCollection($this);
		$this->eiCommandCollection = new EiCommandCollection($this);
		$this->eiModificatorCollection = new EiModificatorCollection($this);
	}
	
	public function extends(EiTypeExtension $eiMaskExtension) {
		IllegalStateException::assertTrue($this->eiMaskExtension === null);
		$this->eiMaskExtension = $eiMaskExtension;
		
		$inheritEiMask = $eiMaskExtension->getExtendedEiMask();
		
		$this->eiPropCollection->setInheritedCollection($inheritEiMask->getEiPropCollection());
		$this->eiCommandCollection->setInheritedCollection($inheritEiMask->getEiCommandCollection());
		$this->eiModificatorCollection->setInheritedCollection($inheritEiMask->getEiModificatorCollection());
	}
	
	/**
	 * @return boolean
	 */
	public function isExtension() {
		return $this->eiMaskExtension !== null;
	}
	
	/**
	 * @return \rocket\spec\ei\mask\EiTypeExtension
	 * @throws IllegalStateException if {@see self::isExtension()} returns false.
	 */
	public function getExtension() {
		if ($this->eiMaskExtension === null) {
			throw new IllegalStateException('EiMask is no extension.');
		}
		
		return $this->eiMaskExtension;
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
	
	/**
	 * @return \rocket\spec\ei\EiType
	 */
	public function getEiType() {
		return $this->eiType;
	}
	
	/**
	 * @return \rocket\spec\ei\mask\EiMaskDef
	 */
	public function getDef() {
		return $this->eiMaskDef;
	}
	
	public function getModuleNamespace() {
		return $this->eiMaskExtension !== null
				? $this->eiMaskExtension->getModuleNamespace()
				: $this->eiType->getModuleNamespace();
	}
	
	/**
	 * @return \n2n\l10n\Lstr
	 */
	public function getLabelLstr() {
		return new Lstr((string) $this->eiMaskDef->getLabel(), $this->getModuleNamespace());
	}
	
	/**
	 * @return \n2n\l10n\Lstr
	 */
	public function getPluralLabelLstr() {
		return new Lstr((string) $this->eiMaskDef->getPluralLabel(), $this->getModuleNamespace());
	}
	
	/**
	 * @return string
	 */
	public function getIconType() {
		if (null !== ($iconType = $this->eiMaskDef->getIconType())) {
			return $iconType;
		}
		
		return IconType::ICON_FILE_TEXT;
	}
	
	/**
	 * @return \rocket\spec\ei\component\prop\EiPropCollection
	 */
	public function getEiPropCollection() {
		return $this->eiPropCollection;
	}
	
	/**
	 * @return \rocket\spec\ei\component\command\EiCommandCollection
	 */
	public function getEiCommandCollection() {
		return $this->eiCommandCollection;
	}
	
	/**
	 * @return \rocket\spec\ei\component\modificator\EiModificatorCollection
	 */
	public function getEiModificatorCollection() {
		return $this->eiModificatorCollection;
	}
	
	/**
	 * @return EiEngine
	 */
	public function getEiEngine() {
		return $this->eiEngine;
	}
	
	/**
	 * @param DisplayScheme $displayScheme
	 */
	public function setDisplayScheme(DisplayScheme $displayScheme) {
		$this->displayScheme = $displayScheme;
	}
	
	/**
	 * @return DisplayScheme
	 */
	public function getDisplayScheme() {
		return $this->displayScheme ?? $this->displayScheme = new DisplayScheme();
	}
	

	
	/**
	 * @return boolean
	 */
	public function isDraftingEnabled() {
		return false;
// 		if (null !== ($draftingAllowed = $this->eiDef->isDraftingAllowed())) {
// 			if (!$draftingAllowed) return false;
// 		} else if (null !== ($draftingAllowed = $this->eiType->getEiMask()->isDraftingAllowed())) {
// 			if (!$draftingAllowed) return false;
// 		}
		
// 		return !$this->eiEngine->getDraftDefinition()->isEmpty();
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	private function createDefaultIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		$idPatternPart = null;
		$namePatternPart = null;
		
		foreach ($this->eiEngine->getGuiDefinition()->getStringRepresentableGuiProps() as $guiIdPathStr => $guiProp) {
			if ($guiIdPathStr == $this->eiEngine->getEiMask()->getEiType()->getEntityModel()->getIdDef()->getPropertyName()) {
				$idPatternPart = SummarizedStringBuilder::createPlaceholder($guiIdPathStr);
			} else {
				$namePatternPart = SummarizedStringBuilder::createPlaceholder($guiIdPathStr);
			}
			
			if ($namePatternPart !== null) break;
		}
		
		if ($idPatternPart === null) {
			$idPatternPart = $eiObject->getEiEntityObj()->hasId() ? 
					$this->eiType->idToPid($eiObject->getEiEntityObj()->getId()) : 'new';
		}
		
		if ($namePatternPart === null) {
			$namePatternPart = $this->getLabelLstr()->t($n2nLocale);
		}
		
		return $this->eiEngine->getGuiDefinition()->createIdentityString($namePatternPart . ' #' . $idPatternPart, $eiObject, $n2nLocale);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiObject $eiObject, N2nLocale $n2nLocale): string {
		$identityStringPattern = $this->eiMaskDef->getIdentityStringPattern();
		
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
				$displayStructure = $this->getDisplayScheme()->getDetailDisplayStructure()
						?? $this->displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_EDIT:
				$displayStructure = $this->getDisplayScheme()->getEditDisplayStructure()
						?? $this->displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_ADD:
				$displayStructure = $this->getDisplayScheme()->getAddDisplayStructure()
						?? $this->displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::COMPACT_READ:
			case ViewMode::COMPACT_EDIT:
			case ViewMode::COMPACT_ADD:
				$displayStructure = $this->getDisplayScheme()->getOverviewDisplayStructure();
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
		
		// @todo
// 		if ($this->eiType->containsSuperEiType($eiTypeId, true)) {
			
// 		}

		return $eiType->getEiMask();
		
// 		throw new \InvalidArgumentException();
	}
	
	public function getSubEiMaskByEiTypeId($eiTypeId): EiMask {
		$subMaskIds = $this->getSubEiMaskIds();
		
		foreach ($this->eiType->getSubEiTypes() as $subEiType) {
			if ($subEiType->getId() != $eiTypeId) continue;
			
			if (isset($subMaskIds[$eiTypeId])) {
				return $subEiType->getEiTypeExtensionCollection()->getById($subMaskIds[$eiTypeId]);
			} else {
				return $subEiType->getEiMask();
			}
		}
		
		throw new \InvalidArgumentException('EiType ' . $eiTypeId . ' is no SubEiType of ' 
				. $this->eiType->getId());
	}
	
	public function isPreviewSupported(): bool {
		return null !== $this->eiMaskDef->getPreviewControllerLookupId();
	}
	
	public function lookupPreviewController(EiFrame $eiFrame, PreviewModel $previewModel = null): PreviewController {
		$lookupId = $this->eiMaskDef->getPreviewControllerLookupId();
		if (null === $lookupId) {
			$lookupId = $this->eiType->getEiMask()->getPreviewControllerLookupId();	
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
		if (null !== ($filterGroupData = $this->eiMaskDef->getFilterGroupData())) {
			$criteriaConstraint = $this->createManagedFilterDefinition($eiFrame)
					->buildCriteriaConstraint($filterGroupData, false);
			if ($criteriaConstraint !== null) {
				$eiFrame->addCriteriaConstraint($criteriaConstraint);
			}
		}

		if (null !== ($defaultSortData = $this->eiMaskDef->getDefaultSortData())) {
			$criteriaConstraint = $this->eiEngine->createManagedSortDefinition($eiFrame)
					->builCriteriaConstraint($defaultSortData, false);
			if ($criteriaConstraint !== null) {
				$eiFrame->getCriteriaConstraintCollection()->add(CriteriaConstraint::TYPE_HARD_SORT, $criteriaConstraint);
			}
		}

		$eiu = new Eiu($eiFrame);
		foreach ($this->eiModificatorCollection->toArray() as $modificator) {
			$modificator->setupEiFrame($eiu);
		}
	}

}
