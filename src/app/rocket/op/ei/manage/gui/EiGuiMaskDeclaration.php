<?php
///*
// * Copyright (c) 2012-2016, Hofmänner New Media.
// * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
// *
// * This file is part of the n2n module ROCKET.
// *
// * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
// * GNU Lesser General Public License as published by the Free Software Foundation, either
// * version 2.1 of the License, or (at your option) any later version.
// *
// * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
// * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
// *
// * The following people participated in this project:
// *
// * Andreas von Burg...........:	Architect, Lead Developer, Concept
// * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
// * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
// */
//namespace rocket\op\ei\manage\gui;
//
//use rocket\op\ei\manage\frame\EiFrame;
//use rocket\op\ei\manage\entry\EiEntry;
//use n2n\util\type\ArgUtils;
//use n2n\util\ex\IllegalStateException;
//use rocket\op\ei\manage\DefPropPath;
//use rocket\ui\gui\control\UnknownGuiControlException;
//use rocket\op\ei\manage\api\ApiControlCallId;
//use rocket\op\ei\EiPropPath;
//use rocket\ui\si\meta\SiProp;
//use n2n\l10n\N2nLocale;
//use rocket\op\ei\manage\api\ApiController;
//use rocket\op\ei\mask\EiMask;
//use rocket\ui\gui\GuiEntry;
//use rocket\ui\gui\GuiMask;
//use rocket\ui\gui\GuiStructureDeclaration;
//use rocket\ui\gui\control\GuiControlMap;
//use rocket\ui\gui\GuiProp;
//
///**
// * @author andreas
// *
// */
//class EiGuiMaskDeclaration {
//	/**
//	 * @var GuiStructureDeclaration[]
//	 */
//	private $guiStructureDeclarations;
//	/**
//	 * @var EiPropPath[]
//	 */
//	private $eiPropPaths = [];
//	/**
//	 * @var EiGuiField[]
//	 */
//	private $eiGuiFields = [];
//	/**
//	 * @var DefPropPath[]
//	 */
//	private $defPropPaths = [];
//	/**
//	 * @var DisplayDefinition[]
//	 */
//	private $displayDefinitions = [];
//	/**
//	 * @var EiGuiListener[]
//	 */
//	private $eiGuiMaskDeclarationListeners = array();
//	/**
//	 * @var bool
//	 */
//	private bool $init = false;
//
//	/**
//	 * @param int $viewMode Use constants from {@see ViewMode}
//	 * @param EiGuiDefinition $guiDefinition
//	 * @param array|null $guiStructureDeclarations
//	 */
//	function __construct(private readonly int $viewMode, private readonly EiGuiDefinition $guiDefinition,
//			?array $guiStructureDeclarations) {
//		$this->setGuiStructureDeclarations($guiStructureDeclarations);
//	}
//
////	function createSiMaskIdentifier(): SiMaskIdentifier {
////		$eiMask = $this->guiDefinition->getEiMask();
////
////		$eiSiMaskId = new EiSiMaskId($eiMask->getEiTypePath(), $this->viewMode);
////		return new SiMaskIdentifier($eiSiMaskId->__toString(), $eiMask->getEiType()->getSupremeEiType()->getId());
////	}
////
////	public function createSiMaskQualifier(N2nLocale $n2nLocale): SiMaskQualifier {
////		return new SiMaskQualifier($this->createSiMaskIdentifier(),
////				$this->getEiMask()->getLabelLstr()->t($n2nLocale), $this->getEiMask()->getIconType());
////	}
//
//	function getEiMask(): EiMask {
//		return $this->guiDefinition->getEiMask();
//	}
//
//	function getViewMode(): int {
//		return $this->viewMode;
//	}
//
//	function getEiGuiDefinition(): EiGuiDefinition {
//		return $this->guiDefinition;
//	}
//
//	/**
//	 * @param GuiStructureDeclaration[]|null $guiStructureDeclarations
//	 */
//	function setGuiStructureDeclarations(?array $guiStructureDeclarations): void {
//		ArgUtils::valArray($guiStructureDeclarations, GuiStructureDeclaration::class, true);
//		$this->guiStructureDeclarations = $guiStructureDeclarations;
//	}
//
//	/**
//	 * @return GuiStructureDeclaration[]|null
//	 */
//	function getGuiStructureDeclarations(): ?array {
//		return $this->guiStructureDeclarations;
//	}
//
//	/**
//	 * @param EiPropPath $eiPropPath
//	 * @return EiGuiField
//	 * @throws EiGuiException
//	 */
//	function getEiGuiField(EiPropPath $eiPropPath): EiGuiField {
//		$eiPropPathStr = (string) $eiPropPath;
//		if (isset($this->eiGuiFields[$eiPropPathStr])) {
//			return $this->eiGuiFields[$eiPropPathStr];
//		}
//
//		throw new EiGuiException('Unknown EiGuiField for ' . $eiPropPath);
//	}
//
//	function putEiGuiField(EiPropPath $eiPropPath, EiGuiField $eiGuiField): void {
//		$this->ensureNotInit();
//
//		$this->eiGuiFields[(string) $eiPropPath] = $eiGuiField;
//	}
//
//	/**
//	 * @param EiPropPath $eiPropPath
//	 * @return bool
//	 */
//	function containsEiGuiField(EiPropPath $eiPropPath): bool {
//		return isset($this->eiGuiFields[(string) $eiPropPath]);
//	}
//
//	/**
//	 * @return EiPropPath[]
//	 */
//	function getEiPropPaths(): array {
//		return $this->eiPropPaths;
//	}
//
//	function putDisplayDefintion(DefPropPath $defPropPath, DisplayDefinition $displayDefinition): void {
//		$this->ensureNotInit();
//
//		$eiPropPath = $defPropPath->getFirstEiPropPath();
//		$this->eiPropPaths[(string) $eiPropPath] = $eiPropPath;
//
//		$defPropPathStr = (string) $defPropPath;
//		$this->defPropPaths[$defPropPathStr] = $defPropPath;
//		$this->displayDefinitions[$defPropPathStr] = $displayDefinition;
//	}
//
//	/**
//	 * @param DefPropPath $defPropPath
//	 * @return bool
//	 */
//	function containsDisplayDefinition(DefPropPath $defPropPath): bool {
//		return isset($this->displayDefinitions[(string) $defPropPath]);
//	}
//
//	/**
//	 * @param DefPropPath $defPropPath
//	 * @return DisplayDefinition
//	 * @throws UnresolvableDefPropPathExceptionEi
//	 */
//	function getDisplayDefintion(DefPropPath $defPropPath): DisplayDefinition {
//		$defPropPathStr = (string) $defPropPath;
//		if (isset($this->displayDefinitions[$defPropPathStr])) {
//			return $this->displayDefinitions[$defPropPathStr];
//		}
//
//		throw new UnresolvableDefPropPathExceptionEi('Unknown DefPropPath for ' . $defPropPath);
//	}
//
//	/**
//	 * @return DefPropPath[]
//	 */
//	function getDefPropPaths(): array {
//		return $this->defPropPaths;
//	}
//
//	/**
//	 * @param DefPropPath $defPropPath
//	 * @return bool
//	 */
//	function containsDefPropPath(DefPropPath $defPropPath): bool {
//		return isset($this->defPropPaths[(string) $defPropPath]);
//	}
//
////	function createSiMask(N2nLocale $n2nLocale): GuiMask {
////		IllegalStateException::assertTrue($this->guiStructureDeclarations !== null,
////				'EiGuiMaskDeclaration has no GuiStructureDeclarations.');
////
////		return new GuiMask(
////				$this->createSiMaskQualifier($n2nLocale),
////				$this->applyGuiProps($n2nLocale),
////				$this->createSiStructureDeclarations($this->guiStructureDeclarations));
////
////	}
//
//	function createGeneralGuiControlsMap(EiFrame $eiFrame): GuiControlMap {
//		return $this->guiDefinition->createGeneralGuiControlsMap($eiFrame, $this);
//	}
//
////	/**
////	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
////	 * @return SiStructureDeclaration[]
////	 */
////	private function createSiStructureDeclarations(array $guiStructureDeclarations): array {
////		$siStructureDeclarations = [];
////
////		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
////			if ($guiStructureDeclaration->hasDefPropPath()) {
////				$siStructureDeclarations[] = SiStructureDeclaration::createProp(
////						$guiStructureDeclaration->getSiStructureType(),
////						$guiStructureDeclaration->getDefPropPath());
////				continue;
////			}
////
////			$siStructureDeclarations[] = SiStructureDeclaration
////					::createGroup($guiStructureDeclaration->getSiStructureType(), $guiStructureDeclaration->getLabel(),
////							$guiStructureDeclaration->getHelpText())
////					->setChildren($this->createSiStructureDeclarations($guiStructureDeclaration->getChildren()));
////		}
////
////		return $siStructureDeclarations;
////	}
//
////	/**
////	 * @return SiMask
////	 */
////	function createGuiMask(EiFrame $eiFrame): GuiMask {
////		$guiMask = new GuiMask($this->createSiMaskQualifier($n2nLocale), null);
////
////		foreach ($this->eiGuiFields as $eiGuiField) {
////			$guiMask->setGuiProps(createGuiProps);
////		}
////
////		$guiMask->setGuiControlMap($this->guiDefinition->createGeneralGuiControlsMap($eiFrame, $this));
////
////		return $guiMask;
////	}
//
//	/**
//	 * @param N2nLocale $n2nLocale
//	 * @return SiProp[]
//	 */
//	private function applyGuiProps(GuiMask $guiMask, N2nLocale $n2nLocale): array {
//		$deter = new ContextSiFieldDeterminer();
//
//		$siProps = [];
//		foreach ($this->defPropPaths as $defPropPath) {
//			$eiProp = $this->guiDefinition->getEiGuiPropWrapperByDefPropPath($defPropPath)->getEiProp();
//			$eiPropNature = $eiProp->getNature();
//			$label = $eiPropNature->getLabelLstr()->t($n2nLocale);
//			$helpText = null;
//			if (null !== ($helpTextLstr = $eiPropNature->getHelpTextLstr())) {
//				$helpText = $helpTextLstr->t($n2nLocale);
//			}
//
//			$guiMask->putGuiProp((string) $defPropPath, new GuiProp($label, $helpText));
//
//			$deter->reportDefPropPath($defPropPath);
//		}
//
//		return array_merge($deter->applyContextSiProps($guiMask, $n2nLocale, $this), $siProps);
//	}
//
//	function createEntryGuiControlsMap(EiFrame $eiFrame, EiEntry $eiEntry): GuiControlMap {
//		return $this->guiDefinition->createEntryGuiControlsMap($eiFrame, $this, $eiEntry);
//	}
//
////	function createGeneralGuiControlsMap(EiFrame $eiFrame): GuiControlMap {
////		return $this->guiDefinition->createGeneralGuiControlsMap($eiFrame, $this);
////	}
//
//
//// 	function getRootEiPropPaths() {
//// 		$eiPropPaths = [];
//// 		foreach ($this->getDefPropPaths() as $defPropPath) {
//// 			$eiPropPath = $defPropPath->getFirstEiPropPath();
//// 			$eiPropPaths[(string) $eiPropPath] = $eiPropPath;
//// 		}
//// 		return $eiPropPaths;
//// 	}
//
//// 	/**
//// 	 * @param DefPropPath $defPropPath
//// 	 * @throws GuiException
//// 	 * @return \rocket\op\ei\manage\gui\GuiPropAssembly
//// 	 */
//// 	function getGuiPropAssemblyByDefPropPath(DefPropPath $defPropPath) {
//// 		$defPropPathStr = (string) $defPropPath;
//
//// 		if (isset($this->guiPropAssemblies[$defPropPathStr])) {
//// 			return $this->guiPropAssemblies[$defPropPathStr];
//// 		}
//
//// 		throw new GuiException('No GuiPropAssembly for DefPropPath available: ' . $defPropPathStr);
//// 	}
//
//	/**
//	 * @throws IllegalStateException
//	 */
//	function markInitialized(): void {
//		if ($this->isInit()) {
//			throw new IllegalStateException('EiGuiMaskDeclaration already initialized.');
//		}
//
//		$this->init = true;
//
//		foreach ($this->eiGuiMaskDeclarationListeners as $listener) {
//			$listener->onInitialized($this);
//		}
//	}
//
//	/**
//	 * @return boolean
//	 */
//	function isInit(): bool {
//		return $this->init;
//	}
//
//	/**
//	 * @throws IllegalStateException
//	 */
//	private function ensureInit(): void {
//		if ($this->init) return;
//
//		throw new IllegalStateException('EiGuiMaskDeclaration not yet initialized.');
//	}
//
//	/**
//	 * @throws IllegalStateException
//	 */
//	private function ensureNotInit(): void {
//		if (!$this->init) return;
//
//		throw new IllegalStateException('EiGuiMaskDeclaration is already initialized.');
//	}
//
//// 	/**
//// 	 * @param GuiStructureDeclaration $guiStructureDeclaration
//// 	 * @return SiProp
//// 	 */
//// 	private function createSiProp(GuiStructureDeclaration $guiStructureDeclaration) {
//// 		return new SiProp($guiStructureDeclaration->getDefPropPath(),
//// 				$guiStructureDeclaration->getLabel(), $guiStructureDeclaration->getHelpText());
//// 	}
//
//// 	/**
//// 	 * @return \rocket\si\meta\SiMask
//// 	 */
//// 	function createSiTypDeclaration() {
//// 		$siMaskQualifier = $this->guiDefinition->getEiMask()->createSiMaskQualifier($this->eiFrame->getN2nContext()->getN2nLocale());
//// 		$siType = new SiType($siMaskQualifier, $this->getSiProps());
//
//// 		return new SiMask($siType, $this->createSiStructureDeclarations($this->guiStructureDeclarations));
//// 	}
//
//// 	/**
//// 	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
//// 	 * @return SiStructureDeclaration[]
//// 	 */
//// 	private function createSiStructureDeclarations($guiStructureDeclarations) {
//// 		$siStructureDeclarations = [];
//
//// 		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
//// 			if ($guiStructureDeclaration->hasDefPropPath()) {
//// 				$siStructureDeclarations[] = new SiStructureDeclaration($guiStructureDeclaration->getSiStructureType(),
//// 						$guiStructureDeclaration->getDefPropPath(), $guiStructureDeclaration->getLabel(),
//// 						$guiStructureDeclaration->getHelpText());
//// 				continue;
//// 			}
//
//// 			$siStructureDeclarations[] = new SiStructureDeclaration($guiStructureDeclaration->getSiStructureType(),
//// 					null, $guiStructureDeclaration->getLabel(), $guiStructureDeclaration->getHelpText(),
//// 					$this->createSiStructureDeclarations($guiStructureDeclaration->getChildren()));
//// 		}
//
//// 		return $siStructureDeclarations;
//// 	}
//
//// 	/**
//// 	 * @param EiPropPath $forkEiPropPath
//// 	 * @return DefPropPath[]
//// 	 */
//// 	function getForkedDefPropPathsByEiPropPath(EiPropPath $forkEiPropPath) {
//// 		$forkDefPropPaths = [];
//// 		foreach ($this->getDefPropPaths() as $defPropPath) {
//// 			if ($defPropPath->getFirstEiPropPath()->equals($eiPropPath)) {
//// 				continue;
//// 			}
//
//// 			$forkDefPropPaths[] = $defPropPath->getShifted();
//// 		}
//// 		return $forkDefPropPaths;
//// 	}
//
//	function createGuiEntry(EiFrame $eiFrame, EiEntry $eiEntry, bool $entryGuiControlsIncluded): GuiEntry {
//		$this->ensureInit();
//
//		$eiGuiEntry = EiGuiEntryFactory::createGuiEntry($eiFrame, $this, $eiEntry, $entryGuiControlsIncluded);
//
//		foreach ($this->eiGuiMaskDeclarationListeners as $eiGuiMaskDeclarationListener) {
//			$eiGuiMaskDeclarationListener->onNewEiGuiEntry($eiGuiEntry);
//		}
//
//		return $eiGuiEntry;
//	}
//
//	/**
//	 * @param EiFrame $eiFrame
//	 * @param bool $entryGuiControlsIncluded
//	 * @return GuiEntry
//	 */
//	function createNewEiGuiEntry(EiFrame $eiFrame, bool $entryGuiControlsIncluded): GuiEntry {
//		$this->ensureInit();
//
//		$eiObject = $this->getEiGuiDefinition()->getEiMask()->getEiType()->createNewEiObject();
//		$eiEntry = $eiFrame->createEiEntry($eiObject);
//
//		$eiGuiEntry = EiGuiEntryFactory::createGuiEntry($eiFrame, $this, $eiEntry, $entryGuiControlsIncluded);
//
//		foreach ($this->eiGuiMaskDeclarationListeners as $eiGuiMaskDeclarationListener) {
//			$eiGuiMaskDeclarationListener->onNewEiGuiEntry($eiGuiEntry);
//		}
//
//		return $eiGuiEntry;
//	}
//
////	/**
////	 * @return \rocket\si\control\SiControl[]
////	 */
////	function createSelectionSiControls(EiFrame $eiFrame): array {
////		$siControls = [];
////		foreach ($this->guiDefinition->createSelectionGuiControls($eiFrame, $this)
////				as $guiControlPathStr => $selectionGuiControl) {
////			$guiControlPath = GuiControlPath::create($guiControlPathStr);
////			$siControls[$guiControlPathStr] = $selectionGuiControl->toSiControl(
////					$eiFrame->getApiUrl($guiControlPath->getEiCmdPath(), ApiController::API_CONTROL_SECTION),
////					new ApiControlCallId($guiControlPath,
////							$this->guiDefinition->getEiMask()->getEiTypePath(),
////							$this->eiGuiDeclaration->getViewMode(), null));
////		}
////		return $siControls;
////	}
//
//	/**
//	 * @return \rocket\ui\si\control\SiControl[]
//	 */
//	function createGeneralSiControls(EiFrame $eiFrame): array {
//		$siControls = [];
//		foreach ($this->guiDefinition->createGeneralGuiControls($eiFrame, $this)
//				as $guiControlPathStr => $generalGuiControl) {
//			$guiControlPath = EiGuiControlName::create($guiControlPathStr);
//			$siControls[$guiControlPathStr] = $generalGuiControl->toSiControl(
//					$eiFrame->getApiUrl($guiControlPath->getEiCmdPath(), ApiController::API_CONTROL_SECTION),
//					new ApiControlCallId($guiControlPath,
//							$this->guiDefinition->getEiMask()->getEiTypePath(),
//							$this->viewMode, null, null));
//		}
//		return $siControls;
//	}
//
////	/**
////	 * @param EiFrame $eiFrame
////	 * @param GuiControlPath $guiControlPath
////	 * @return GuiControl
////	 * @throws UnknownGuiControlException
////	 */
////	function createGeneralGuiControl(EiFrame $eiFrame, GuiControlPath $guiControlPath) {
////		return $this->guiDefinition->createGeneralGuiControl($eiFrame, $this, $guiControlPath);
////	}
//
//	/**
//	 * @param EiFrame $eiFrame
//	 * @param EiEntry $eiEntry
//	 * @param EiGuiControlName $guiControlPath
//	 * @return \rocket\ui\gui\control\GuiControl
//	 * @throws UnknownGuiControlException
//	 */
//	function createEntryGuiControl(EiFrame $eiFrame, EiEntry $eiEntry, EiGuiControlName $guiControlPath) {
//		return $this->guiDefinition->createEntryGuiControl($eiFrame, $this, $eiEntry, $guiControlPath);
//	}
//
//// 	/**
//// 	 * @return \rocket\si\content\SiEntry
//// 	 */
//// 	function createSiEntry(EiFrame $eiFrame, EiGuiValueBoundary $eiGuiValueBoundary, bool $siControlsIncluded = true) {
//// 		$eiEntry = $eiGuiValueBoundary->getEiEntry();
//// 		$eiType = $eiEntry->getEiType();
//// 		$siIdentifier = $eiEntry->getEiObject()->createSiEntryIdentifier();
//// 		$viewMode = $this->getViewMode();
//
//// 		$siValueBoundary = new SiEntry($siIdentifier, ViewMode::isReadOnly($viewMode), ViewMode::isBulky($viewMode));
//// 		$siValueBoundary->putBuildup($eiType->getId(), $this->createSiEntry($eiFrame, $eiGuiValueBoundary, $siControlsIncluded));
//// 		$siValueBoundary->setSelectedTypeId($eiType->getId());
//
//// 		return $siValueBoundary;
//// 	}
//
//
//
//	/**
//	 * @param DefPropPath $prefixDefPropPath
//	 * @return DefPropPath[]
//	 */
//	function filterDefPropPaths(DefPropPath $prefixDefPropPath) {
//		$defPropPaths = [];
//
//		foreach ($this->defPropPaths as $defPropPathStr => $defPropPath) {
//			$defPropPath = DefPropPath::create($defPropPathStr);
//			if ($defPropPath->equals($prefixDefPropPath)
//					|| !$defPropPath->startsWith($prefixDefPropPath, false)) {
//				continue;
//			}
//
//			$defPropPaths[] = $defPropPath;
//		}
//
//		return $defPropPaths;
//	}
//
//	/**
//	 * @param EiGuiListener $eiGuiMaskDeclarationListener
//	 */
//	function registerEiGuiListener(EiGuiListener $eiGuiMaskDeclarationListener) {
//		$this->eiGuiMaskDeclarationListeners[spl_object_hash($eiGuiMaskDeclarationListener)] = $eiGuiMaskDeclarationListener;
//	}
//
//	/**
//	 * @param EiGuiListener $eiGuiMaskDeclarationListener
//	 */
//	function unregisterEiGuiListener(EiGuiListener $eiGuiMaskDeclarationListener): void {
//		unset($this->eiGuiMaskDeclarationListeners[spl_object_hash($eiGuiMaskDeclarationListener)]);
//	}
//}
////
////class ContextSiFieldDeterminer {
////	private $defPropPaths = [];
////	private $forkDefPropPaths = [];
////	private $forkedDefPropPaths = [];
////
////	/**
////	 * @param DefPropPath $defPropPath
////	 */
////	function reportDefPropPath(DefPropPath $defPropPath) {
////		$defPropPathStr = (string) $defPropPath;
////
////		$this->defPropPaths[$defPropPathStr] = $defPropPath;
////		unset($this->forkDefPropPaths[$defPropPathStr]);
////		unset($this->forkedDefPropPaths[$defPropPathStr]);
////
////		$forkDefPropPath = $defPropPath;
////		while ($forkDefPropPath->hasMultipleEiPropPaths()) {
////			$forkDefPropPath = $forkDefPropPath->getPoped();
////			$this->reportFork($forkDefPropPath, $defPropPath);
////		}
////	}
////
////	/**
////	 * @param DefPropPath $forkDefPropPath
////	 * @param DefPropPath $defPropPath
////	 */
////	private function reportFork(DefPropPath $forkDefPropPath, DefPropPath $defPropPath) {
////		$forkDefPropPathStr = (string) $forkDefPropPath;
////
////		if (isset($this->defPropPaths[$forkDefPropPathStr])) {
////			return;
////		}
////
////		if (!isset($this->forkDefPropPaths[$forkDefPropPathStr])) {
////			$this->forkDefPropPaths[$forkDefPropPathStr] = [];
////		}
////		$this->forkedDefPropPaths[$forkDefPropPathStr][] = $defPropPath;
////		$this->forkDefPropPaths[$forkDefPropPathStr] = $forkDefPropPath;
////
////		if ($forkDefPropPath->hasMultipleEiPropPaths()) {
////			$this->reportFork($forkDefPropPath->getPoped(), $forkDefPropPath);
////		}
////	}
////
////	function applyContextSiProps(GuiMask $guiMask, N2nLocale $n2nLocale, \rocket\op\ei\manage\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration): void {
////
////		foreach ($this->forkDefPropPaths as $forkDefPropPath) {
////			$eiProp = $eiGuiMaskDeclaration->getEiGuiDefinition()->getGuiPropWrapperByDefPropPath($forkDefPropPath)->getEiProp();
////
////
////			$guiProp = (new GuiProp($eiProp->getNature()->getLabelLstr()->t($n2nLocale)))
////					->setDescendantGuiPropNames(array_map(
////							function ($defPropPath) { return (string) $defPropPath; },
////							$this->forkedDefPropPaths[(string) $forkDefPropPath]));
////
////			if (null !== ($helpTextLstr = $eiProp->getNature()->getHelpTextLstr())) {
////				$guiProp->setHelpText($helpTextLstr->t($n2nLocale));
////			}
////
////			$guiMask->putGuiControl((string) $forkDefPropPath, $guiProp);
////		}
////	}
////}