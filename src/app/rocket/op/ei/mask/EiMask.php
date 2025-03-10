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
namespace rocket\op\ei\mask;

use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\EiType;
use rocket\op\ei\manage\preview\model\PreviewModel;
use rocket\op\ei\mask\model\DisplayScheme;
use rocket\op\ei\EiEngine;
use rocket\op\ei\manage\preview\controller\PreviewController;
use n2n\config\InvalidConfigurationException;
use rocket\op\ei\manage\preview\model\UnavailablePreviewException;
use rocket\ui\gui\control\UnknownGuiControlException;
use rocket\op\ei\component\prop\EiPropCollection;
use rocket\op\ei\component\command\EiCmdCollection;
use rocket\op\ei\component\modificator\EiModCollection;
use n2n\util\ex\IllegalStateException;
use rocket\ui\si\control\SiIconType;
use rocket\op\ei\EiTypeExtension;
use n2n\util\ex\NotYetImplementedException;
use rocket\op\spec\TypePath;
use rocket\core\model\Rocket;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\EiObject;
use rocket\op\ei\EiException;
use rocket\op\ei\EiPathMissmatchException;
use n2n\core\container\N2nContext;
use rocket\op\ei\manage\entry\EiEntry;
use n2n\util\type\ArgUtils;
use n2n\l10n\Lstr;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use rocket\ui\si\meta\SiMaskQualifier;
use n2n\l10n\N2nLocale;
use rocket\ui\si\meta\SiMaskIdentifier;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\UnknownEiTypeExtensionException;
use rocket\op\ei\UnknownEiTypeException;
use rocket\ui\gui\err\UnknownGuiElementException;

/**
 * @author andreas
 *
 */
class EiMask {
	private EiMaskDef $eiMaskDef;
	private EiType $eiType;

	private EiPropCollection $eiPropCollection;
	private EiCmdCollection $eiCmdCollection;
	private EiModCollection $eiModCollection;

	private $displayScheme;
	private $eiTypeExtension;

	private $eiEngine;
	private $eiEngineCallbacks = [];

	/**
	 * @param EiType $eiType
	 * @param string $label
	 * @param string $pluralLabel
	 * @param string $iconType
	 */
	public function __construct(EiType $eiType, string $label, string $pluralLabel, string $iconType) {
		$this->eiType = $eiType;

		$this->eiMaskDef = new EiMaskDef($label, $pluralLabel, $iconType);

		$this->eiPropCollection = new EiPropCollection($this);
		$this->eiCmdCollection = new EiCmdCollection($this);
		$this->eiModCollection = new EiModCollection($this);
	}

	/**
	 * @param EiTypeExtension $eiTypeExtension
	 */
	public function extends(EiTypeExtension $eiTypeExtension) {
		IllegalStateException::assertTrue($this->eiTypeExtension === null);
		$this->eiTypeExtension = $eiTypeExtension;

		$inheritEiMask = $eiTypeExtension->getExtendedEiMask();

		$this->eiPropCollection->setInheritedCollection($inheritEiMask->getEiPropCollection());
		$this->eiCmdCollection->setInheritedCollection($inheritEiMask->getEiCmdCollection());
		$this->eiModCollection->setInheritedCollection($inheritEiMask->getEiModCollection());
	}

	/**
	 * @return \rocket\op\spec\TypePath
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
	 * @return \rocket\op\ei\EiTypeExtension
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
				$eiPropNature = $eiProp->getNature();
				if ($eiPropNature->isPropFork()) {
					$forkObject = $eiPropNature->getPropForkObject($forkObject);
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
	 * @return \rocket\op\ei\EiType
	 */
	public function getEiType() {
		return $this->eiType;
	}

	/**
	 * @param EiMask $eiMask
	 * @return boolean
	 */
	function isA(EiMask $eiMask) {
		return $this->eiType->isA($eiMask->getEiType());
	}

	/**
	 * @return \rocket\op\ei\mask\EiMaskDef
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
	 * @return \rocket\op\ei\manage\critmod\filter\data\FilterSettingGroup|null
	 */
	public function getFilterSettingGroup() {
		return $this->eiMaskDef->getFilterSettingGroup();
	}

	/**
	 * @return \rocket\op\ei\manage\critmod\sort\SortSettingGroup|null
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
	 * @return \rocket\op\ei\mask\EiMask|NULL
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

		return Lstr::create('Holeradio', $this->getModuleNamespace());
	}

	/**
	 * @return \n2n\l10n\Lstr
	 */
	public function getPluralLabelLstr() {
		if (null !== ($pluralLabel = $this->eiMaskDef->getPluralLabel())) {
			return Rocket::createLstr($pluralLabel, $this->getModuleNamespace());
		}

		return Lstr::create('Holeradios', $this->getModuleNamespace());
	}

	/**
	 * @return string
	 */
	public function getIconType() {
		if (null !== ($iconType = $this->eiMaskDef->getIconType())) {
			return $iconType;
		}

		return SiIconType::ICON_STICKY_NOTE;
	}

	/**
	 * @return EiPropCollection
	 */
	public function getEiPropCollection() {
		return $this->eiPropCollection;
	}

	/**
	 * @return EiCmdCollection
	 */
	public function getEiCmdCollection() {
		return $this->eiCmdCollection;
	}

	/**
	 * @return EiModCollection
	 */
	public function getEiModCollection() {
		return $this->eiModCollection;
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
	public function setupEiEngine(N2nContext $n2nContext) {
		if ($this->eiEngine !== null) {
			throw new IllegalStateException('EiEngine already set up.');
		}

		$this->eiEngine = new EiEngine($this, $n2nContext);

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

// 	public function createEiGuiMaskDeclaration(EiFrame $eiFrame, int $viewMode, bool $init) {
// 		if (!$this->getEiType()->isA($eiFrame->getContextEiEngine()->getEiMask()->getEiType())) {
// 			throw new \InvalidArgumentException('Incompatible EiGuiMaskDeclaration');
// 		}

// 		$guiDefinition = $eiFrame->getEiLaunch()->getDef()->getEiGuiDefinition($this);
// 		$eiGuiMaskDeclaration = new EiGuiMaskDeclaration($eiFrame, $guiDefinition, $viewMode);

// 		if (!$init) {
// 			$this->noInitCb($eiGuiMaskDeclaration);
// 			return $eiGuiMaskDeclaration;
// 		}

// 		foreach ($guiDefinition->getEiGuiDefinitionListeners() as $listener) {
// 			$listener->onNewEiGuiMaskDeclaration($eiGuiMaskDeclaration);
// 		}

// 		if (!$eiGuiMaskDeclaration->isInit()) {
// 			$this->getDisplayScheme()->initEiGuiMaskDeclaration($eiGuiMaskDeclaration, $guiDefinition);
// 		}

// 		return $eiGuiMaskDeclaration;
// 	}


// 	/**
// 	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
// 	 */
// 	private function noInitCb($eiGuiMaskDeclaration) {

// 		$eiGuiMaskDeclaration->registerEiGuiListener(new class() implements EiGuiListener {
// 			public function onInitialized(EiGuiMaskDeclaration $eiGuiMaskDeclaration) {
// 				foreach ($eiGuiMaskDeclaration->getEiGuiDefinition()->getEiGuiDefinitionListeners() as $listener) {
// 					$listener->onNewEiGuiMaskDeclaration($eiGuiMaskDeclaration);
// 				}
// 				$eiGuiMaskDeclaration->unregisterEiGuiListener($this);
// 			}

// 			public function onNewEiGuiValueBoundary(EiGuiValueBoundary $eiGuiValueBoundary) {
// 			}

// 			public function onNewView(HtmlView $view) {
// 			}
// 		});
// 	}


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
// 	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
// 	 * @param HtmlView $view
// 	 * @return array
// 	 */
// 	public function sortOverallControls(array $controls, EiGuiMaskDeclaration $eiGuiMaskDeclaration, HtmlView $view): array {
// // 		$eiu = new Eiu($eiGuiMaskDeclaration);
// // 		$eiPermissionManager = $eiu->frame()->getEiFrame()->getEiLaunch()->getEiPermissionManager();

// // 		$controls = array();

// // 		foreach ($this->eiEngine->getEiCommandCollection() as $eiCmdId => $eiCmd) {
// // 			if (!($eiCmd instanceof OverallControlComponent)
// // 					|| !$eiPermissionManager->isEiCommandAccessible($eiCmd)) continue;

// // 			$controls = $eiCmd->createOverallControls($eiu, $view);
// // 			ArgUtils::valArrayReturn($controls, $eiCmd, 'createOverallControls', Control::class);
// // 			foreach ($controls as $controlId => $control) {
// // 				$controls[ControlOrder::buildControlId($eiCmdId, $controlId)] = $control;
// // 			}
// // 		}

// 		if (null !== ($overallControlOrder = $this->displayScheme->getOverallControlOrder())) {
// 			return $overallControlOrder->sort($controls);
// 		}

// 		return $controls;
// 	}



// 	public function createPartialControls(EiFrame $eiFrame, HtmlView $view): array {
// 		$controls = array();
// 		foreach ($this->getEiCommandCollection() as $eiCmdId => $eiCmd) {
// 			if (!($eiCmd instanceof PartialControlComponent)
// 					|| !$eiFrame->getEiLaunch()->getEiPermissionManager()->isEiCommandAccessible($eiCmd)) continue;

// 			$executionPath = EiCmdPath::from($eiCmd);
// 			$partialControls = $eiCmd->createPartialControls($eiFrame, $view);
// 			ArgUtils::valArrayReturn($partialControls, $eiCmd, 'createPartialControls', PartialControl::class);
// 			foreach ($partialControls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCmdId, $controlId)] = $control;

// 				if (!$control->hasEiCmdPath()) {
// 					$control->setExecutionPath($executionPath->ext($controlId));
// 				}
// 			}
// 		}

// 		if (null !== ($overallControlOrder = $this->guiOrder->getOverallControlOrder())) {
// 			return $overallControlOrder->sortControls($controls);
// 		}

// 		return $controls;
// 	}


	function matchesTypePath(TypePath $typePath, bool $checkSuper = false, bool $checkSub = false): bool {
		if ($this->eiType->getId() === $typePath->getTypeId()) {
			return true;
		}

		if ($checkSuper && $this->eiType->containsSuperEiTypeId($typePath->getTypeId(), true)) {
			return true;
		}

		if ($checkSub && $this->eiType->containsSubEiTypeId($typePath->getTypeId(), true)) {
			return true;
		}

		return false;
	}


	/**
	 * @return \rocket\op\ei\mask\EiMask
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
	 * @return \rocket\op\ei\mask\EiMask
	 */
	public function determineEiMask(EiType $eiType, bool $superIncluded = false) {
		$contextEiMask = $this;
		$contextEiType = $contextEiMask->getEiType();
		if ($eiType->equals($contextEiType)) {
			return $contextEiMask;
		}

		if ($superIncluded) {
			$contextEiType = $contextEiType->getSupremeEiType();
		}

		if (!$eiType->isA($contextEiType)) {
			throw new \InvalidArgumentException('EiType ' . $eiType->getId() . ' is no SubEiType of '
					. $contextEiType->getId());
		}

		if (isset($this->subEiTypeExtensions[$eiType->getId()])) {
			return $this->subEiTypeExtensions[$eiType->getId()]->getEiMask();
		}

		return $eiType->getEiMask();
	}


	/**
	 * @throws UnknownEiTypeExtensionException
	 * @throws UnknownEiTypeException
	 */
	function determineEiMaskByEiTypePath(TypePath $eiTypePath): EiMask {
		if ($this->getEiTypePath()->equals($eiTypePath)) {
			return $this;
		}

		$typeId = $eiTypePath->getTypeId();
		if (isset($this->subEiTypeExtensions[$typeId])) {
			return $this->subEiTypeExtensions[$typeId]->getEiMask();
		}

		return $this->eiType->determineEiMask($eiTypePath);
	}

//	/**
//	 * @param string $eiTypeId
//	 * @throws \InvalidArgumentException
//	 * @return EiMask
//	 */
//	public function getSubEiMaskByEiTypeId(string $eiTypeId): EiMask {
//		$subMaskIds = $this->getSubEiMaskIds();
//
//		foreach ($this->eiType->getSubEiTypes() as $subEiType) {
//			if ($subEiType->getId() != $eiTypeId) continue;
//
//			if (isset($subMaskIds[$eiTypeId])) {
//				return $subEiType->getEiTypeExtensionCollection()->getById($subMaskIds[$eiTypeId]);
//			} else {
//				return $subEiType->getEiMask();
//			}
//		}
//
//		throw new \InvalidArgumentException('EiType ' . $eiTypeId . ' is no SubEiType of '
//				. $this->eiType->getId());
//	}

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
	public function getPreviewTypeOptions(N2nContext $n2nContext, EiFrame $eiFrame, EiObject $eiObject, ?EiEntry $eiEntry = null) {
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
	 * @throws UnknownGuiControlException
	 * @return PreviewController
	 */
	public function lookupPreviewController(N2nContext $n2nContext, ?PreviewModel $previewModel = null): PreviewController {
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
			throw new UnknownGuiControlException('Unknown preview type \'' . $previewModel->getPreviewType()
					. '\' for PreviewController: ' . get_class($previewController));
		}

		$previewController->setPreviewModel($previewModel);
		return $previewController;
	}

	/**
	 * @param N2nLocale $n2nLocale
	 * @return \rocket\ui\si\meta\SiMaskQualifier
	 */


	function equals($obj) {
		return $obj instanceof EiMask && $obj->getEiTypePath()->equals($this->getEiTypePath());
	}

	public function __toString(): string {
		return 'EiMask of ' . ($this->isExtension() ? $this->eiTypeExtension : $this->eiType);
	}
}
