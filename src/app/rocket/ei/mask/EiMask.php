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
namespace rocket\ei\mask;

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\EiType;
use rocket\ei\manage\preview\model\PreviewModel;
use rocket\ei\mask\model\DisplayScheme;
use rocket\ei\EiEngine;
use rocket\ei\manage\preview\controller\PreviewController;
use n2n\config\InvalidConfigurationException;
use rocket\ei\manage\preview\model\UnavailablePreviewException;
use rocket\ei\manage\control\UnavailableControlException;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\EiPropCollection;
use rocket\ei\component\command\EiCommandCollection;
use rocket\ei\component\modificator\EiModificatorCollection;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\control\IconType;
use rocket\ei\EiTypeExtension;
use n2n\util\ex\NotYetImplementedException;
use rocket\spec\TypePath;
use rocket\core\model\Rocket;
use rocket\ei\EiPropPath;
use rocket\ei\manage\EiObject;
use rocket\ei\EiException;
use rocket\ei\EiPathMissmatchException;
use n2n\core\container\N2nContext;
use rocket\ei\manage\entry\EiEntry;
use n2n\util\type\ArgUtils;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\l10n\Lstr;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\EiGuiListener;

/**
 * @author andreas
 *
 */
class EiMask {
	private $eiMaskDef;
	private $eiType;
	private $subEiMaskIds;
	
	private $eiPropCollection;
	private $eiCommandCollection;
	private $eiModificatorCollection;
	
	private $displayScheme;
	private $eiTypeExtension;
	
	private $eiEngine;
	private $eiEngineCallbacks = [];
	private $mappingFactory;
	private $guiFactory;
	private $draftDefinitionFactory;
	private $critmodFactory;
	
	private $guiDefinition;
	private $draftDefinition;
	
	/**
	 * @param EiType $eiType
	 */
	public function __construct(EiType $eiType) {
		$this->eiType = $eiType;
		
		$this->eiMaskDef = new EiMaskDef();

		$this->eiPropCollection = new EiPropCollection($this);
		$this->eiCommandCollection = new EiCommandCollection($this);
		$this->eiModificatorCollection = new EiModificatorCollection($this);
	}
	
	/**
	 * @param EiTypeExtension $eiTypeExtension
	 */
	public function extends(EiTypeExtension $eiTypeExtension) {
		IllegalStateException::assertTrue($this->eiTypeExtension === null);
		$this->eiTypeExtension = $eiTypeExtension;
		
		$inheritEiMask = $eiTypeExtension->getExtendedEiMask();
		
		$this->eiPropCollection->setInheritedCollection($inheritEiMask->getEiPropCollection());
		$this->eiCommandCollection->setInheritedCollection($inheritEiMask->getEiCommandCollection());
		$this->eiModificatorCollection->setInheritedCollection($inheritEiMask->getEiModificatorCollection());
	}
	
	/**
	 * @return \rocket\spec\TypePath
	 */
	public function getEiTypePath() {
		return new TypePath($this->eiType->getId(), 
				($this->eiTypeExtension !== null ? $this->eiTypeExtension->getId() : null));
	}
	
	/**
	 * @return boolean
	 */
	public function isExtension() {
		return $this->eiTypeExtension !== null;
	}
	
	/**
	 * @return \rocket\ei\EiTypeExtension
	 * @throws IllegalStateException if {@see self::isExtension()} returns false.
	 */
	public function getExtension() {
		if ($this->eiTypeExtension === null) {
			throw new IllegalStateException('EiMask is no extension.');
		}
		
		return $this->eiTypeExtension;
	}
	
	/**
	 * @param EiPropPath $forkEiPropPath
	 * @param EiObject $eiObject
	 * @return object
	 * @throws EiPathMissmatchException
	 */
	public function getForkObject(EiPropPath $forkEiPropPath, EiObject $eiObject) {
		$ids = $forkEiPropPath->toArray();
		
		$forkObject = $eiObject->getEiEntityObj()->getEntityObj();
		$eiPropPath = new EiPropPath([]);
		
		try {
			while (null !== ($id = array_shift($ids))) {
				$eiPropPath = $eiPropPath->ext($id);
				
				$eiProp = $this->eiPropCollection->getByPath($eiPropPath);
				if ($eiProp->isPropFork()) {
					$forkObject = $eiProp->getPropForkObject($forkObject);
					continue;
				}
				
				throw new EiPathMissmatchException('EiProp ' . $eiProp . ' is not a PropFork.');
			}
		} catch (EiException $e) {
			throw new EiPathMissmatchException('Could not resolve fork object of ' . $forkEiPropPath, 0, $e);
		}
		
		return $forkObject;
	}

	/**
	 * @return \rocket\ei\EiType
	 */
	public function getEiType() {
		return $this->eiType;
	}
	
	/**
	 * @return \rocket\ei\mask\EiMaskDef
	 */
	public function getDef() {
		return $this->eiMaskDef;
	}
	
	/**
	 * @return string
	 */
	public function getIdentityStringPattern() {
		return $this->eiMaskDef->getIdentityStringPattern();
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\data\FilterSettingGroup|null
	 */
	public function getFilterSettingGroup() {
		return $this->eiMaskDef->getFilterSettingGroup();
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\sort\SortSettingGroup|null
	 */
	public function getSortSettingGroup() {
		return $this->eiMaskDef->getDefaultSortSettingGroup();
	}
	
	/**
	 * @return string
	 */
	public function getModuleNamespace() {
		return $this->eiTypeExtension !== null
				? $this->eiTypeExtension->getModuleNamespace()
				: $this->eiType->getModuleNamespace();
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask|NULL
	 */
	private function getExtendedEiMask() {
		if ($this->eiTypeExtension !== null) {
			return $this->eiTypeExtension->getExtendedEiMask();
		}
		
		throw new NotYetImplementedException('Should not happen.');
	}
	
	/**
	 * @return \n2n\l10n\Lstr
	 */
	public function getLabelLstr() {
		if (null !== ($label = $this->eiMaskDef->getLabel())) {
			return Rocket::createLstr($label, $this->getModuleNamespace());
		}
		
		return $this->getExtendedEiMask()->getLabelLstr();
	}
	
	/**
	 * @return \n2n\l10n\Lstr
	 */
	public function getPluralLabelLstr() {
		if (null !== ($pluralLabel = $this->eiMaskDef->getPluralLabel())) {
			return Rocket::createLstr($pluralLabel, $this->getModuleNamespace());
		}
		
		return $this->getExtendedEiMask()->getPluralLabelLstr();
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
	 * @return \rocket\ei\component\prop\EiPropCollection
	 */
	public function getEiPropCollection() {
		return $this->eiPropCollection;
	}
	
	/**
	 * @return \rocket\ei\component\command\EiCommandCollection
	 */
	public function getEiCommandCollection() {
		return $this->eiCommandCollection;
	}
	
	/**
	 * @return \rocket\ei\component\modificator\EiModificatorCollection
	 */
	public function getEiModificatorCollection() {
		return $this->eiModificatorCollection;
	}
	
	/**
	 * @return boolean
	 */
	public function hasEiEngine() {
		return $this->eiEngine !== null;
	}
	
	/**
	 * @return EiEngine
	 */
	public function getEiEngine() {
		if ($this->eiEngine !== null) {
			return $this->eiEngine;
		}
		
		throw new IllegalStateException('EiEngine is not set up yet.');
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \Closure[]
	 */
	public function setupEiEngine() {
		if ($this->eiEngine !== null) {
			throw new IllegalStateException('EiEngine already set up.');
		}
		
		$this->eiEngine = new EiEngine($this);
		
		$callbacks = $this->eiEngineCallbacks;
		$this->eiEngineCallbacks = array();
		return $callbacks;
	}
	
	/**
	 * @return \Closure[];
	 */
	public function getEiEngineSetupCallbacks() {
		return $this->eiEngineCallbacks;
	}
	
	/**
	 * @param \Closure $callback
	 */
	public function onEiEngineSetup(\Closure $callback) {
		if ($this->eiEngine !== null) {
			$callback($this->eiEngine);
			return;
		}
		
		$this->eiEngineCallbacks[spl_object_hash($callback)] = $callback;
	}
	
	/**
	 * @param \Closure $callback
	 */
	public function offEiEngineSetup(\Closure $callback) {
		unset($this->eiEngineCallbacks[spl_object_hash($callback)]);
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
	
	public function createEiGui(EiFrame $eiFrame, int $viewMode, bool $init) {
		if (!$this->getEiType()->isA($eiFrame->getContextEiEngine()->getEiMask()->getEiType())) {
			throw new \InvalidArgumentException('Incompatible EiGui');
		}
		
		$guiDefinition = $eiFrame->getManageState()->getDef()->getGuiDefinition($this);
		$eiGui = new EiGui($eiFrame, $guiDefinition, $viewMode);
		
		if (!$init) {
			$this->noInitCb($eiGui);
			return $eiGui;
		}
		
		foreach ($guiDefinition->getGuiDefinitionListeners() as $listener) {
			$listener->onNewEiGui($eiGui);
		}
		
		if (!$eiGui->isInit()) {
			$this->getDisplayScheme()->initEiGui($eiGui, $guiDefinition);
		}
		
		return $eiGui;
	}
	
	
	/**
	 * @param EiGui $eiGui
	 */
	private function noInitCb($eiGui) {
		
		$eiGui->registerEiGuiListener(new class() implements EiGuiListener {
			public function onInitialized(EiGui $eiGui) {
				foreach ($eiGui->getGuiDefinition()->getGuiDefinitionListeners() as $listener) {
					$listener->onNewEiGui($eiGui);
				}
				$eiGui->unregisterEiGuiListener($this);
			}
			
			public function onNewEiEntryGui(EiEntryGui $eiEntryGui) {
			}
			
			public function onNewView(HtmlView $view) {
			}
		});
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
	
	
	
// 	/**
// 	 * @param array $controls
// 	 * @param EiGui $eiGui
// 	 * @param HtmlView $view
// 	 * @return array
// 	 */
// 	public function sortOverallControls(array $controls, EiGui $eiGui, HtmlView $view): array {
// // 		$eiu = new Eiu($eiGui);
// // 		$eiPermissionManager = $eiu->frame()->getEiFrame()->getManageState()->getEiPermissionManager();
		
// // 		$controls = array();
		
// // 		foreach ($this->eiEngine->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// // 			if (!($eiCommand instanceof OverallControlComponent)
// // 					|| !$eiPermissionManager->isEiCommandAccessible($eiCommand)) continue;
				
// // 			$controls = $eiCommand->createOverallControls($eiu, $view);
// // 			ArgUtils::valArrayReturn($controls, $eiCommand, 'createOverallControls', Control::class);
// // 			foreach ($controls as $controlId => $control) {
// // 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
// // 			}
// // 		}
		
// 		if (null !== ($overallControlOrder = $this->displayScheme->getOverallControlOrder())) {
// 			return $overallControlOrder->sort($controls);
// 		}
	
// 		return $controls;
// 	}
	

	
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
	 * @return \rocket\ei\mask\EiMask
	 */
	public function getSupremeEiMask() {
		if (!$this->eiType->hasSuperEiType()) {
			return $this;
		}
		
		return $this->eiType->getSupremeEiType()->getEiMask();
	}
	
// 	public function getSubEiMaskIds() {
// 		return $this->subEiMaskIds;
// 	}
	
// 	public function setSubEiMaskIds(array $subEiMaskIds) {
// 		$this->subEiMaskIds = $subEiMaskIds;
// 	}
	
// 	public function determineEiMask(EiType $eiType): EiMask {
// 		$eiTypeId = $eiType->getId();
// 		if ($this->eiType->getId() == $eiTypeId) {
// 			return $this;
// 		}
		
// 		if ($this->eiType->containsSubEiTypeId($eiTypeId)) {
// 			return $this->getSubEiMaskByEiTypeId($eiTypeId);
// 		}
				
// 		foreach ($this->eiType->getSubEiTypes() as $subEiType) {
// 			if (!$subEiType->containsSubEiTypeId($eiTypeId, true)) continue;
			
// 			return $this->getSubEiMaskByEiTypeId($subEiType->getId())
// 					->determineEiMask($eiType);
// 		}
		
// 		// @todo
// // 		if ($this->eiType->containsSuperEiType($eiTypeId, true)) {
			
// // 		}

// 		return $eiType->getEiMask();
		
// // 		throw new \InvalidArgumentException();
// 	}

	
	/**
	 * @param EiTypeExtension[] $subEiTypeExtensions
	 */
	public function setSubEiTypeExtensions(array $subEiTypeExtensions) {
		ArgUtils::valArray($subEiTypeExtensions, EiTypeExtension::class);
		$this->subEiTypeExtensions = $subEiTypeExtensions;
	}
	
	/**
	 * @param EiType $eiType
	 * @throws \InvalidArgumentException
	 * @return \rocket\ei\mask\EiMask
	 */
	public function determineEiMask(EiType $eiType) {
		$contextEiMask = $this;
		$contextEiType = $contextEiMask->getEiType();
		if ($eiType->equals($contextEiType)) {
			return $contextEiMask;
		}
		
		if (!$contextEiType->containsSubEiTypeId($eiType->getId(), true)) {
			throw new \InvalidArgumentException('EiType ' . $eiType->getId() . ' is no SubEiType of '
					. $this->getEiType()->getId());
		}
		
		if (isset($this->subEiTypeExtensions[$eiType->getId()])) {
			return $this->subEiTypeExtensions[$eiType->getId()]->getEiMask();
		}
		
		return $eiType->getEiMask();
	}
	
	/**
	 * @param string $eiTypeId
	 * @throws \InvalidArgumentException
	 * @return EiMask
	 */
	public function getSubEiMaskByEiTypeId(string $eiTypeId): EiMask {
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
	
	/**
	 * @return bool
	 */
	public function isPreviewSupported(): bool {
		return null !== $this->eiMaskDef->getPreviewControllerLookupId();
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @param EiFrame $eiFrame
	 * @param EiObject $eiObject
	 * @param EiEntry $eiEntry
	 * @return string[]
	 */
	public function getPreviewTypeOptions(N2nContext $n2nContext, EiFrame $eiFrame, EiObject $eiObject, EiEntry $eiEntry = null) {
		$previewController = $this->lookupPreviewController($n2nContext);
		
		$options = $previewController->getPreviewTypeOptions(new Eiu($eiFrame, $eiObject, $eiEntry));
		ArgUtils::valArrayReturn($options, $previewController, 'getPreviewTypeOptions', array('string', Lstr::class));
		
		return $options;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param PreviewModel $previewModel
	 * @throws UnavailablePreviewException
	 * @throws InvalidConfigurationException
	 * @throws UnavailableControlException
	 * @return PreviewController
	 */
	public function lookupPreviewController(N2nContext $n2nContext, PreviewModel $previewModel = null): PreviewController {
		$lookupId = $this->eiMaskDef->getPreviewControllerLookupId();
		if (null === $lookupId) {
			$lookupId = $this->eiType->getEiMask()->getPreviewControllerLookupId();	
		}
		
		if ($lookupId === null) {
			throw new UnavailablePreviewException('No PreviewController available for EiMask: ' . $this);
		}
		
		$previewController = $n2nContext->lookup($lookupId);
		if (!($previewController instanceof PreviewController)) {
			throw new InvalidConfigurationException('PreviewController must implement ' . PreviewController::class 
					. ': ' . get_class($previewController));
		}
		
		if ($previewModel === null) {
			return $previewController;
		}
		
		if (!array_key_exists($previewModel->getPreviewType(), 
				$previewController->getPreviewTypeOptions($previewModel->getEiu()))) {
			throw new UnavailableControlException('Unknown preview type \'' . $previewModel->getPreviewType() 
					. '\' for PreviewController: ' . get_class($previewController));
		}
		
		$previewController->setPreviewModel($previewModel);
		return $previewController;
	}
	
	public function __toString(): string {
		return 'EiMask of ' . ($this->isExtension() ? $this->eiTypeExtension : $this->eiType);
	}
}
