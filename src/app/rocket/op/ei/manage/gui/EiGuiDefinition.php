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

namespace rocket\op\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\mask\EiMask;
use n2n\l10n\N2nLocale;
use rocket\ui\si\meta\SiMaskIdentifier;
use rocket\ui\si\meta\SiMaskQualifier;
use rocket\ui\gui\GuiStructureDeclaration;
use rocket\ui\gui\GuiMask;
use rocket\op\ei\manage\gui\factory\EiSiMaskIdentifierFactory;
use rocket\ui\gui\GuiPropForkWrapper;

class EiGuiDefinition {


	private EiGuiPropMap $eiGuiPropMap;
	private EiGuiCmdMap $eiGuiCmdMap;
	/**
	 * @var GuiStructureDeclaration[]|null
	 */
	private ?array $guiStructureDeclarations = null;

	function __construct(private readonly EiMask $eiMask, private readonly int $viewMode) {
		$this->eiGuiPropMap = new EiGuiPropMap($this);
		$this->eiGuiCmdMap = new EiGuiCmdMap($this);
	}
	
	function getEiMask(): EiMask {
		return $this->eiMask;
	}

	function getViewMode(): int {
		return $this->viewMode;
	}

	function getEiGuiPropMap(): EiGuiPropMap {
		return $this->eiGuiPropMap;
	}

	function getEiGuiCmdMap(): EiGuiCmdMap {
		return $this->eiGuiCmdMap;
	}

	/**
	 * @param GuiStructureDeclaration[]|null $guiStructureDeclarations
	 */
	function setGuiStructureDeclarations(?array $guiStructureDeclarations): void {
		ArgUtils::valArray($guiStructureDeclarations, GuiStructureDeclaration::class, true);
		$this->guiStructureDeclarations = $guiStructureDeclarations;
	}

	/**
	 * @return GuiStructureDeclaration[]|null
	 */
	function getGuiStructureDeclarations(): ?array {
		return $this->guiStructureDeclarations;
	}

	function createSiMaskIdentifier(): SiMaskIdentifier {
		return EiSiMaskIdentifierFactory::create($this->eiMask, $this->getViewMode());
	}

	public function createSiMaskQualifier(N2nLocale $n2nLocale): SiMaskQualifier {
		return new SiMaskQualifier($this->createSiMaskIdentifier(),
				$this->getEiMask()->getLabelLstr()->t($n2nLocale), $this->getEiMask()->getIconType());
	}

	function createGuiMask(EiFrame $eiFrame): GuiMask {
		$guiMask = new GuiMask(
				$this->createSiMaskQualifier($eiFrame->getN2nContext()->getN2nLocale()),
				$this->getGuiStructureDeclarations());

		foreach ($this->eiGuiPropMap->compileAllGuiProps() as $defPropPathStr => $guiProp) {
			$guiMask->putGuiProp(DefPropPath::create($defPropPathStr)->toGuiFieldPath(), $guiProp);
		}

		$guiMask->setGuiControlMap($this->eiGuiCmdMap->createGeneralGuiControlsMap($eiFrame));

		return $guiMask;
	}

	function getGuiPropWrapperByPath(DefPropPath $defPropPath): EiGuiPropWrapper {
		$eiGuiPropMap = $this->eiGuiPropMap;
		$guiPropWrapper = null;

		foreach ($defPropPath->toArray() as $eiPropPath) {
			if ($eiGuiPropMap === null) {
				break;
			}

			$guiPropWrapper = $eiGuiPropMap->getGuiPropWrapper($eiPropPath);
			$eiGuiPropMap = $guiPropWrapper->getChildEiGuiPropMap();
		}

		if ($guiPropWrapper !== null) {
			return $guiPropWrapper;
		}

		throw new EiGuiException('No GuiProp with path \'' . $defPropPath . '\' registered');
	}


//	/**
//	 * @return EiPropPath[]
//	 */
//	function getEiPropPaths(): array {
//		return $this->eiPropPaths;
//	}

//	/**
//	 * @return EiGuiPropWrapper[]
//	 */
//	function getEiGuiPropWrappers(): array {
//		return $this->eiGuiPropWrappers;
//	}



//	/**
//	 * @param DefPropPath $defPropPath
//	 */
//	function removeGuiPropByPath(DefPropPath $defPropPath): void {
//		$guiDefinition = $this;
//		$eiPropPaths = $defPropPath->toArray();
//		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
//			if (empty($eiPropPaths)) {
//				$guiDefinition->removeGuiProp($eiPropPath);
//				return;
//			}
//
//			$guiDefinition = $guiDefinition->getGuiPropFork($eiPropPath)->getForkedEiGuiDefinition();
//
//			if ($guiDefinition === null) {
//				return;
//			}
//		}
//	}






//	/**
//	 * @return GuiPropWrapper[]
//	 */
//	function getGuiPropWrappers() {
//		return $this->eiGuiPropWrappers;
//	}

//	/**
//	 * @param DefPropPath $defPropPath
//	 * @return boolean
//	 */
//	function containsGuiProp(DefPropPath $defPropPath): bool {
//		$eiPropPaths = $defPropPath->toArray();
//		$guiDefinition = $this;
//		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
//			if (empty($eiPropPaths)) {
//				return $guiDefinition->containsEiPropPath($eiPropPath);
//			}
//
//			$guiDefinition = $guiDefinition->getGuiPropFork($eiPropPath)->getForkedEiGuiDefinition();
//		}
//
//		return true;
//	}

// 	/**
// 	 * @param string $eiPropPath
// 	 * @param GuiPropFork $eiGuiPropFork
// 	 */
// 	function putGuiPropFork(EiPropPath $eiPropPath, GuiPropFork $eiGuiPropFork) {
// 		$eiPropPathStr = (string) $eiPropPath;

// 		$this->eiGuiPropForkWrappers[$eiPropPathStr] = new GuiPropForkWrapper($this, $eiPropPath, $eiGuiPropFork);
// 		$this->eiPropPaths[$eiPropPathStr] = $eiPropPath;
// 	}

// 	/**
// 	 * @param string $id
// 	 * @return boolean
// 	 */
// 	function containsLevelGuiPropForkId(string $id) {
// 		return isset($this->eiGuiPropForkWrappers[$id]);
// 	}

// 	/**
// 	 * @param string $id
// 	 * @throws GuiException
// 	 * @return GuiPropFork
// 	 */
// 	function getGuiPropFork(EiPropPath $eiPropPath) {
// 		$eiPropPathStr = (string) $eiPropPath;
// 		if (!isset($this->eiGuiPropForkWrappers[$eiPropPathStr])) {
// 			throw new GuiException('No GuiPropFork with id \'' . $eiPropPathStr . '\' registered.');
// 		}

// 		return $this->eiGuiPropForkWrappers[$eiPropPathStr];
// 	}

// 	function getAllGuiProps() {
// 		return $this->buildGuiProps(array());
// 	}

// 	protected function buildGuiProps(array $baseEiPropPaths) {
// 		$eiGuiProps = array();

// 		foreach ($this->eiPropPaths as $eiPropPath) {
// 			$eiPropPathStr = (string) $eiPropPath;

// 			if (isset($this->eiGuiPropWrappers[$eiPropPathStr])) {
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;
// 				$eiGuiProps[(string) new DefPropPath($currentEiPropPaths)] = $this->eiGuiPropWrappers[$eiPropPathStr];
// 			}

// 			if (isset($this->eiGuiPropForkWrappers[$eiPropPathStr])) {
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;

// 				$eiGuiProps = array_merge($eiGuiProps, $this->eiGuiPropForkWrappers[$eiPropPathStr]->getForkedEiGuiDefinition()
// 						->buildGuiProps($currentEiPropPaths));
// 			}
// 		}

// 		return $eiGuiProps;
// 	}

// 	/**
// 	 * @param DefPropPath[] $defPropPaths
// 	 * @return DefPropPath[]
// 	 */
// 	function filterDefPropPaths(array $defPropPaths) {
// 		return array_filter($defPropPaths, function (DefPropPath $defPropPath) {
// 			return $this->containsGuiProp($defPropPath);
// 		});
// 	}



	/**
	 * @return DefPropPath[]
	 */
	function getDefPropPaths(): array {
		$defPropPaths = array();

		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			$defPropPath = new DefPropPath([$eiPropPath]);

			$defPropPaths[] = $defPropPath;

			foreach ($this->eiGuiPropWrappers[$eiPropPathStr]->getForkedDefPropPaths()
					 as $forkedDefPropPath) {
				$defPropPaths[] = $defPropPath->ext($forkedDefPropPath);
			}
		}

		return $defPropPaths;
	}

// 	function assembleDefaultGuiProps() {
// 		$eiGuiPropAssemblies = [];
// 		$this->composeGuiPropAssemblies($eiGuiPropAssemblies, []);
// 		return $eiGuiPropAssemblies;
// 	}

// 	function assembleGuiProps(EiGuiMaskDeclaration $eiGuiMaskDeclaration, array $defPropPaths) {
// 		ArgUtils::valArray($defPropPaths, DefPropPath::class);

// // 		$eiu = new Eiu($eiGuiMaskDeclaration);

// 		$eiGuiPropAssemblies = [];

// 		foreach ($defPropPaths as $defPropPath) {
// 			$eiGuiProp = $this->getGuiPropByDefPropPath($defPropPath);

// 			$displayDefinition = $eiGuiProp->getDisplayDefinition();
// 			if ($displayDefinition === null) {
// 				continue;
// 			}

// 			$eiGuiPropAssemblies[(string) $defPropPath] = new GuiPropAssembly($defPropPath, $displayDefinition);
// 		}

// 		return $eiGuiPropAssemblies;
// 	}


// 	/**
// 	 * @param array $baseEiPropPaths
// 	 * @param Eiu $eiu
// 	 * @param int $minTestLevel
// 	 */
// 	protected function composeGuiPropAssemblies(array &$eiGuiPropAssemblies, array $baseEiPropPaths) {
// 		foreach ($this->eiPropPaths as $eiPropPath) {
// 			$eiPropPathStr = (string) $eiPropPath;

// 			$displayDefinition = null;
// 			if (isset($this->eiGuiPropWrappers[$eiPropPathStr])
// 					&& null !== ($displayDefinition = $this->eiGuiPropWrappers[$eiPropPathStr]->getDisplayDefinition())
// 					&& $displayDefinition->isDefaultDisplayed()) {

// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;

// 				$defPropPath = new DefPropPath($currentEiPropPaths);
// 				$eiGuiPropAssemblies[(string) $defPropPath] = new GuiPropAssembly($defPropPath, $displayDefinition);
// 			}

// 			if (isset($this->eiGuiPropForkWrappers[$eiPropPathStr])
// 					&& null !== ($forkedEiGuiDefinition = $this->eiGuiPropForkWrappers[$eiPropPathStr]->getForkedEiGuiDefinition())) {
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;
// 				$forkedEiGuiDefinition->composeGuiPropAssemblies($eiGuiPropAssemblies, $currentEiPropPaths);
// 			}
// 		}
// 	}

// 	function createDefaultDisplayStructure(EiGuiMaskDeclaration $eiGuiMaskDeclaration) {
// 		$displayStructure = new DisplayStructure();
// 		$this->composeDisplayStructure($displayStructure, array(), new Eiu($eiGuiMaskDeclaration));
// 		return $displayStructure;
// 	}


//	/**
//	 * @param DefPropPath $defPropPath
//	 * @return EiGuiPropWrapper
//	 * @throws EiGuiException
//	 */
//	function getEiGuiPropWrapperByDefPropPath(DefPropPath $defPropPath): EiGuiPropWrapper {
//		$eiGuiPropWrapper = $this->getGuiPropWrapper($defPropPath->getFirstEiPropPath());
//
//		if (!$defPropPath->hasMultipleEiPropPaths()) {
//			return $eiGuiPropWrapper;
//		}
//
//		try {
//			return $eiGuiPropWrapper->getForkedGuiPropWrapper($defPropPath->getShifted());
//		} catch (\rocket\ui\gui\UnresolvableDefPropPathExceptionEi $e) {
//			throw new \rocket\ui\gui\UnresolvableDefPropPathExceptionEi('DefPropPath could not be resolved: ' . $defPropPath);
//		}
//
//	}
//
//
//	function determineEiFieldAbstraction(N2nContext $n2nContext, EiEntry $eiEntry, DefPropPath $defPropPath): EiFieldAbstraction {
//		$eiPropPaths = $defPropPath->toArray();
//		$id = array_shift($eiPropPaths);
//		if (empty($eiPropPaths)) {
//			return $eiEntry->getEiField($id);
//		}
//
//		throw new NotYetImplementedException();
//	}

//	/**
//	 * @return GuiPropForkWrapper[]
//	 */
//	function getGuiPropForkWrappers() {
//		return $this->eiGuiPropForkWrappers;
//	}

//	/**
//	 * @param string $id
//	 * @param EiGuiProp $eiGuiProp
//	 * @param EiPropPath $defPropPath
//	 * @throws EiGuiException
//	 */
//	function putEiGuiCommand(EiCmdPath $eiCmdPath, EiGuiCmd $eiGuiCmd): void {
//		$eiCmdPathStr = (string) $eiCmdPath;
//
//		if (isset($this->eiGuiCmdWrappers[$eiCmdPathStr])) {
//			throw new EiGuiException('GuiCommand for EiCmdPath \'' . $eiCmdPathStr . '\' is already registered');
//		}
//
//		$this->eiGuiCmdWrappers[$eiCmdPathStr] = $eiGuiCmd;
//		$this->eiCmdPaths[$eiCmdPathStr] = $eiCmdPath;
//	}

//	/**
//	 * @return EiGuiCmd[]
//	 */
//	function getEiGuiCmdWrappers(): array {
//		return $this->eiGuiCmdWrappers;
//	}
//
//	/**
//	 * @param EiFrame $eiFrame
//	 * @param EiEntry $eiEntry
//	 * @param EiGuiControlName $guiControlPath
//	 * @return GuiControl
//	 * @throws UnknownGuiControlException
//	 */
//	function createEntryGuiControl(EiFrame $eiFrame, EiGuiMaskDeclaration $eiGuiMaskDeclaration, EiEntry $eiEntry, EiGuiControlName $guiControlPath): GuiControl {
//		if ($guiControlPath->size() != 2) {
//			throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
//		}
//
//		$eiu = new Eiu($eiFrame, $eiGuiMaskDeclaration, $eiEntry);
//		$cmdId = $guiControlPath->getFirstId();
//		$controlId = $guiControlPath->getLastId();
//
//		foreach ($this->eiGuiCmdWrappers as $id => $guiCommand) {
//			if ($cmdId != $id) {
//				continue;
//			}
//
//			$guiControls = $this->extractEntryGuiControls($guiCommand, $id, $eiu);
//			if (isset($guiControls[$controlId])) {
//				return $guiControls[$controlId];
//			}
//		}
//
//		throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
//	}
//
//	function createEntryGuiControlsMap(EiFrame $eiFrame, EiEntry $eiEntry): GuiControlMap {
//		$guiControlsMap = new GuiControlMap();
//
//		$guiControls = [];
//		foreach ($this->eiGuiCmdWrappers as $eiCmdPathStr => $guiCommand) {
//			$eiCmdPath = $this->eiCmdPaths[$eiCmdPathStr];
//			$eiu = new Eiu($eiFrame, $this, $eiEntry, $eiCmdPath);
//
////			$apiUrl = $eiFrame->getApiUrl($eiCmdPath);
//
//			foreach ($this->extractEntryGuiControls($guiCommand, $eiCmdPathStr, $eiu) as $name => $entryGuiControl) {
//				$guiControlPath = new EiGuiControlName([$eiCmdPathStr, $name]);
//				$apiControlCallId = ApiControlCallId::create($this->eiMask, $guiControlPath, $eiEntry);
//
//				$guiControlsMap->putGuiControl($guiControlPath, $entryGuiControl);
//			}
//		}
//
//		return $guiControlsMap;
//	}
//
//	/**
//	 * @param EiGuiCmd $guiCommand
//	 * @param string $guiCommandId
//	 * @param Eiu $eiu
//	 * @return GuiControl[]
//	 */
//	private function extractEntryGuiControls(EiGuiCmd $guiCommand, string $guiCommandId, Eiu $eiu): array {
//		$entryGuiControls = $guiCommand->createEntryGuiControls($eiu);
//		ArgUtils::valArrayReturn($entryGuiControls, $guiCommand, 'createEntryGuiControls', GuiControl::class);
//
//		return $this->mapGuiControls($entryGuiControls, $guiCommand, GuiControl::class);
//	}
//
//
//	/**
//	 * @return GuiControl[]
//	 */
//	function createGeneralGuiControls() {
//		$this->putEiGuiCommand();
//	}
//
//	/**
//	 * @return GuiControl[]
//	 */
//	private function createGeneralGuiControlsR(array $eiGui): array {
//
//	}
//
//
//	function createGeneralGuiControlsMap(EiFrame $eiFrame, EiGuiMaskDeclaration $eiGuiMaskDeclaration): GuiControlMap {
//		$guiControlsMap = new GuiControlMap();
//
//		$guiControls = [];
//		foreach ($this->eiGuiCmdWrappers as $eiCmdPathStr => $guiCommand) {
//
//			$eiCmdPath = $this->eiCmdPaths[$eiCmdPathStr];
//			$eiu = new Eiu($eiFrame, $eiGuiMaskDeclaration, $eiCmdPath);
//
//
//
//			foreach ($this->extractGeneralGuiControls($guiCommand, $eiCmdPathStr, $eiu) as $controlName => $generalGuiControl) {
//				$guiControlPath = new EiGuiControlName([$eiCmdPathStr, $controlName]);
//
//				$guiControlsMap->putGuiControl($guiControlPath, $generalGuiControl);
//			}
//		}
//
//		return $guiControlsMap;
//	}
//
//
////	/**
////	 * @param EiFrame $eiFrame
////	 * @param EiEntry $eiEntry
////	 * @return GuiControl[]
////	 */
////	function createEntryGuiControls(EiFrame $eiFrame, EiGuiMaskDeclaration $eiGuiMaskDeclaration, EiEntry $eiEntry): array {
////		$guiControls = [];
////		foreach ($this->guiCommands as $eiCmdPathStr => $guiCommand) {
////			$eiu = new Eiu($eiFrame, $eiGuiMaskDeclaration, $eiEntry, $this->eiCmdPaths[$eiCmdPathStr]);
////			foreach ($this->extractEntryGuiControls($guiCommand, $eiCmdPathStr, $eiu) as $entryGuiControl) {
////				$guiControlPath = new GuiControlPath([$eiCmdPathStr, $entryGuiControl->getId()]);
////
////				$guiControls[(string) $guiControlPath] = $entryGuiControl;
////			}
////		}
////		return $guiControls;
////	}
//
////	/**
////	 * @param EiGuiCommand $guiCommand
////	 * @param string $guiCommandId
////	 * @param Eiu $eiu
////	 * @return \rocket\ui\gui\control\GuiControl[]
////	 */
////	private function extractEntryGuiControls(EiGuiCommand $guiCommand, string $guiCommandId, Eiu $eiu) {
////		$entryGuiControls = $guiCommand->createEntryGuiControls($eiu);
////		ArgUtils::valArrayReturn($entryGuiControls, $guiCommand, 'createEntryGuiControls', GuiControl::class);
////
////		return $this->mapGuiControls($entryGuiControls, $guiCommand, GuiControl::class);
////	}
//
//	/**
//	 * @param EiFrame $eiFrame
//	 * @param EiEntry $eiEntry
//	 * @param EiGuiControlName $guiControlPath
//	 * @return GuiControl
//	 * @throws UnknownGuiControlException
//	 */
//	function createGeneralGuiControl(EiFrame $eiFrame, EiGuiMaskDeclaration $eiGuiMaskDeclaration, EiGuiControlName $guiControlPath) {
//		if ($guiControlPath->size() < 2) {
//			throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
//		}
//
//		$eiu = new Eiu($eiFrame, $eiGuiMaskDeclaration);
//		$ids = $guiControlPath->toArray();
//		$cmdId = array_shift($ids);
//		$controlId = array_shift($ids);
//
//		foreach ($this->eiGuiCmdWrappers as $id => $guiCommand) {
//			if ($cmdId != $id) {
//				continue;
//			}
//
//			$guiControls = $this->extractGeneralGuiControls($guiCommand, $id, $eiu);
//			if (null !== ($guiControl = $this->findGuiControl($guiControls[$controlId] ?? null, $ids))) {
//				return $guiControl;
//			}
//		}
//
//		throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
//	}
//
//	private function findGuiControl($guiControl, $ids) {
//		if (empty($ids) || $guiControl === null) {
//			return $guiControl;
//		}
//
//		$id = array_shift($ids);
//		return $this->findGuiControl($guiControl->getChildById($id), $ids);
//	}
//
//
//	/**
//	 * @param EiFrame $eiFrame
//	 * @return GuiControl[]
//	 */
//	function createGeneralGuiControls(EiFrame $eiFrame): array {
//		$siControls = [];
//		foreach ($this->eiGuiCmdWrappers as $eiCmdPath => $guiCommand) {
//			$eiu = new Eiu($eiFrame, $this, $this->eiCmdPaths[$eiCmdPath]);
//			foreach ($this->extractGeneralGuiControls($guiCommand, $eiCmdPath, $eiu) as $generalGuiControl) {
//				$guiControlPath = new EiGuiControlName([$eiCmdPath, $generalGuiControl->getId()]);
//				$siControls[(string) $guiControlPath] = $generalGuiControl;
//			}
//		}
//		return $siControls;
//	}
//
//	/**
//	 * @param EiGuiCmd $guiCommand
//	 * @param string $guiCommandId
//	 * @param Eiu $eiu
//	 * @return \rocket\ui\gui\control\GuiControl[]
//	 */
//	private function extractGeneralGuiControls(EiGuiCmd $guiCommand, string $guiCommandId, Eiu $eiu) {
//		$generalGuiControls = $guiCommand->createGeneralGuiControls($eiu);
//		ArgUtils::valArrayReturn($generalGuiControls, $guiCommand, 'extractGeneralGuiControls', GuiControl::class);
//
//		return $this->mapGuiControls($generalGuiControls, $guiCommand, GuiControl::class);
//	}
//
//// 	/**
//// 	 * @param EiFrame $eiFrame
//// 	 * @param GuiControlPath $guiControlPath
//// 	 * @return GuiControl
//// 	 * @throw UnknownGuiControlException
//// 	 */
//// 	function createSelectionGuiControl(EiFrame $eiFrame, GuiControlPath $guiControlPath) {
//// 		if ($guiControlPath->size() != 2) {
//// 			throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
//// 		}
//
//// 		$eiu = new Eiu($eiFrame);
//// 		$ids = $guiControlPath->toArray();
//// 		$cmdId = array_shift($ids);
//// 		$controlId = array_shift($ids);
//
//// 		foreach ($this->guiCommands as $id => $guiCommand) {
//// 			if ($cmdId != $id) {
//// 				continue;
//// 			}
//
//// 			$guiControls = $this->extractSelectionGuiControls($guiCommand, $id, $eiu);
//// 			if (null !== ($guiControl = $this->findGuiControl($guiControls[$controlId] ?? null, $ids))) {
//// 				return $guiControl;
//// 			}
//// 		}
//
//// 		throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
//// 	}
//
//// 	/**
//// 	 * @param EiFrame $eiFrame
//// 	 * @return GuiControl[]
//// 	 */
//// 	function createSelectionGuiControls(EiFrame $eiFrame): array {
//// 		$eiu = new Eiu($eiFrame);
//
//// 		$guiControls = [];
//// 		foreach ($this->guiCommands as $id => $guiCommand) {
//// 			foreach ($this->extractSelectionGuiControls($guiCommand, $id, $eiu) as $selectionGuiControl) {
//// 				$guiControlPath = new GuiControlPath([$id, $selectionGuiControl->getId()]);
//
//// 				$guiControls[(string) $guiControlPath] = $selectionGuiControl;
//// 			}
//// 		}
//// 		return $guiControls;
//// 	}
//
//// 	/**
//// 	 * @param GuiCommand $guiCommand
//// 	 * @param string $guiCommandId
//// 	 * @param Eiu $eiu
//// 	 * @return \rocket\op\ei\manage\gui\control\GuiControl[]
//// 	 */
//// 	private function extractSelectionGuiControls(GuiCommand $guiCommand, string $guiCommandId, Eiu $eiu) {
//// 		$selectionGuiControls = $guiCommand->createSelectionGuiControls($eiu);
//// 		ArgUtils::valArrayReturn($selectionGuiControls, $guiCommand, 'createSelectionGuiControls', GuiControl::class);
//
//// 		return $this->mapGuiControls($selectionGuiControls, $guiCommand, GuiControl::class);
//// 	}
//
//	/**
//	 * @param GuiControl[] $guiControls
//	 * @return GuiControl[]
//	 */
//	private function mapGuiControls($guiControls, $guiCommand, $guiControlClassName): array {
//		$mappedGuiControls = [];
//
//		foreach ($guiControls as $key => $guiControl) {
//			$id = (string) $key;
//
////			$id = $guiControl->getId();
//
//			if (!IdPath::isIdValid($id)) {
//				throw new \InvalidArgumentException(StringUtils::strOf($guiCommand) . ' returns '
//						. $guiControlClassName . ' with illegal id: ' . $id);
//			}
//
//			if (isset($mappedGuiControls[$id])) {
//				throw new \InvalidArgumentException(StringUtils::strOf($guiCommand) . ' returns multiple '
//						. $guiControlClassName . ' objects with id: ' . $id);
//			}
//
//			$mappedGuiControls[$id] = $guiControl;
//		}
//
//		return $mappedGuiControls;
//	}
//
//	/**
//	 * @return Lstr[]
//	 */
//	function getLabelLstrs() {
//		return $this->buildLabelLstrs([]);
//	}
//
//	/**
//	 * @param EiPropPath[] $contextEiPropPaths
//	 * @return Lstr[]
//	 */
//	private function buildLabelLstrs(array $contextEiPropPaths) {
//		$labelLstrs = array();
//
//		foreach ($this->eiPropPaths as $eiPropPath) {
//			$eiPropPathStr = (string) $eiPropPath;
//
//			if (isset($this->eiGuiPropWrappers[$eiPropPathStr])) {
//				$currentEiPropPaths = $contextEiPropPaths;
//				$currentEiPropPaths[] = $eiPropPath;
//				$labelLstrs[(string) new DefPropPath($currentEiPropPaths)] = $this->eiGuiPropWrappers[$eiPropPathStr]
//						->getEiProp()->getNature()->getLabelLstr();
//			}
//
////			if (isset($this->eiGuiPropForkWrappers[$eiPropPathStr])) {
////				$currentEiPropPaths = $contextEiPropPaths;
////				$currentEiPropPaths[] = $eiPropPath;
////
////				$labelLstrs = array_merge($labelLstrs, $this->eiGuiPropForkWrappers[$eiPropPathStr]
////						->getForkedEiGuiDefinition()->buildLabelLstrs($currentEiPropPaths));
////			}
//		}
//
//		return $labelLstrs;
//	}
//
//// 	/**
//// 	 * @param EiFrame $eiFrame
//// 	 * @param int $viewMode
//// 	 * @param DefPropPath[]|null $defPropPaths
//// 	 * @return \rocket\op\ei\manage\gui\EiGuiDeclaration
//// 	 */
//// 	function createEiGuiDeclaration(N2nContext $n2nContext, int $viewMode, array $defPropPaths = null) {
//// 		$eiGuiMaskDeclaration = new EiGuiMaskDeclaration($this, $viewMode);
//
//
//// 		return new EiGuiDeclaration($guiStructureDeclarations, $eiGuiMaskDeclaration);
//// 	}
//
//	/**
//	 * @param N2nContext $n2nContext
//	 * @param int $viewMode
//	 * @param array|null $defPropPaths
//	 * @return \rocket\ui\gui\EiGuiMaskDeclaration
//	 */
////	function createEiGuiMaskDeclaration(N2nContext $n2nContext, int $viewMode, ?array $defPropPaths): EiGuiMaskDeclaration {
////		$eiGuiMaskDeclaration = new EiGuiMaskDeclaration($viewMode, $this, null);
////
////		if ($defPropPaths === null) {
////			$guiStructureDeclarations = $this->initEiGuiMaskDeclarationFromDisplayScheme($n2nContext, $eiGuiMaskDeclaration);
////		} else {
////			$guiStructureDeclarations = $this->semiAutoInitEiGuiMaskDeclaration($n2nContext, $eiGuiMaskDeclaration, $defPropPaths);
////		}
////
////		$eiGuiMaskDeclaration->setGuiStructureDeclarations($guiStructureDeclarations);
////
////// 		if (ViewMode::isBulky($eiGuiDeclaration->getViewMode())) {
////// 			$guiStructureDeclarations = $this->groupGsds($guiStructureDeclarations);
////// 		}
////
////		return $eiGuiMaskDeclaration;
////	}
////
//////	/**
//////	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
//////	 * @param DefPropPath[] $defPropPaths
//////	 * @return DefPropPath[]
//////	 */
//////	private function filterDefPropPaths($eiGuiMaskDeclaration, $defPropPaths) {
//////		$filteredDefPropPaths = [];
//////		foreach ($defPropPaths as $key => $defPropPath) {
//////			if ($this->containsGuiProp($defPropPath)) {
//////				$filteredDefPropPaths[$key] = $defPropPath;
//////			}
//////		}
//////		return $filteredDefPropPaths;
//////	}
////
////	/**
////	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
////	 */
////	private function initEiGuiMaskDeclaration(EiGuiMaskDeclaration $eiGuiMaskDeclaration): void {
////		$this->eiMask->getEiModCollection()->setupEiGuiMaskDeclaration($eiGuiMaskDeclaration);
////
////		$eiGuiMaskDeclaration->markInitialized();
////	}
////
////	/**
////	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
////	 * @return \rocket\ui\gui\GuiStructureDeclaration
////	 */
////	private function initEiGuiMaskDeclarationFromDisplayScheme(N2nContext $n2nContext, EiGuiMaskDeclaration $eiGuiMaskDeclaration): array {
////		$displayScheme = $this->eiMask->getDisplayScheme();
////
////		$displayStructure = null;
////		switch ($eiGuiMaskDeclaration->getViewMode()) {
////			case \rocket\ui\gui\ViewMode::BULKY_READ:
////				$displayStructure = $displayScheme->getDetailDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
////				break;
////			case \rocket\ui\gui\ViewMode::BULKY_EDIT:
////				$displayStructure = $displayScheme->getEditDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
////				break;
////			case \rocket\ui\gui\ViewMode::BULKY_ADD:
////				$displayStructure = $displayScheme->getAddDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
////				break;
////			case \rocket\ui\gui\ViewMode::COMPACT_READ:
////			case \rocket\ui\gui\ViewMode::COMPACT_EDIT:
////			case \rocket\ui\gui\ViewMode::COMPACT_ADD:
////				$displayStructure = $displayScheme->getOverviewDisplayStructure();
////				break;
////		}
////
////		if ($displayStructure === null) {
////			return $this->autoInitEiGuiMaskDeclaration($n2nContext, $eiGuiMaskDeclaration);
////		}
////
////		return $this->nonAutoInitEiGuiMaskDeclaration($n2nContext, $eiGuiMaskDeclaration, $displayStructure);
////	}
//
////	/**
////	 * @param EiLaunch $eiLaunch ;
////	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
////	 * @param DisplayStructure $displayStructure
////	 * @return \rocket\ui\gui\GuiStructureDeclaration
////	 */
////	private function nonAutoInitEiGuiMaskDeclaration(N2nContext $n2nContext, $eiGuiMaskDeclaration, $displayStructure): array {
////		$assemblerCache = new \rocket\op\gui\EiFieldAssemblerCache($n2nContext, $eiGuiMaskDeclaration, $displayStructure->getAllDefPropPaths());
////		$guiStructureDeclarations = $this->assembleDisplayStructure($assemblerCache, $eiGuiMaskDeclaration, $displayStructure);
////		$this->initEiGuiMaskDeclaration($eiGuiMaskDeclaration);
////		return $guiStructureDeclarations;
////	}
//
//// 	/**
//// 	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
//// 	 */
//// 	private function groupGsds(array $guiStructureDeclarations) {
//// 		$groupedGsds = [];
//
//// 		$curUngroupedGsds = [];
//
//// 		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
//// 			if ($guiStructureDeclaration->getSiStructureType() === SiStructureType::ITEM
//// 					|| ($guiStructureDeclaration->getSiStructureType() === SiStructureType::PANEL
//// 							&& $this->containsNonGrouped($guiStructureDeclaration))) {
//// 				$curUngroupedGsds[] = $guiStructureDeclaration;
//// 				continue;
//// 			}
//
//// 			$this->appendToGoupedGsds($curUngroupedGsds, $groupedGsds);
//// 			$curUngroupedGsds = [];
//
//// 			$groupedGsds[] = $guiStructureDeclaration;
//// 		}
//
//// 		$this->appendToGoupedGsds($curUngroupedGsds, $groupedGsds);
//
//// 		return $groupedGsds;
//// 	}
//
//	/**
//	 * @param \rocket\ui\gui\GuiStructureDeclaration $guiStructureDeclaration
//	 * @return boolean
//	 */
//	private function containsNonGrouped(\rocket\ui\gui\GuiStructureDeclaration $guiStructureDeclaration) {
//		if (!$guiStructureDeclaration->hasChildrean()) return false;
//
//		foreach ($guiStructureDeclaration->getChildren() as $guiStructureDeclaration) {
//			if (SiStructureType::isGroup($guiStructureDeclaration->getSiStructureType())
//					|| ($guiStructureDeclaration->getSiStructureType() === SiStructureType::PANEL
//							&& !$this->containsNonGrouped($guiStructureDeclaration))) {
//				continue;
//			}
//
//			return true;
//		}
//
//		return false;
//	}
//
//	/**
//	 * @param \rocket\ui\gui\GuiStructureDeclaration $curNonGroups
//	 * @param \rocket\ui\gui\GuiStructureDeclaration $groupedGsds
//	 */
//	function appendToGoupedGsds($curUngroupedGsds, &$groupedGsds) {
//		if (empty($curUngroupedGsds)) {
//			return;
//		}
//
//		$groupedGsds[] = \rocket\ui\gui\GuiStructureDeclaration::createGroup($curUngroupedGsds, SiStructureType::SIMPLE_GROUP, null);
//	}
//
//	/**
//	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
//	 * @param DefPropPath[] $possibleDefPropPaths
//	 * @return \rocket\ui\gui\GuiStructureDeclaration
//	 */
//	private function semiAutoInitEiGuiMaskDeclaration($n2nContext, $eiGuiMaskDeclaration, $possibleDefPropPaths) {
//		$assemblerCache = new \rocket\op\gui\EiFieldAssemblerCache($n2nContext, $eiGuiMaskDeclaration, $possibleDefPropPaths);
//		$guiStructureDeclarations = $this->assembleSemiAutoGuiStructureDeclarations($assemblerCache, $eiGuiMaskDeclaration, $possibleDefPropPaths, true);
//		$this->initEiGuiMaskDeclaration($eiGuiMaskDeclaration);
//		return $guiStructureDeclarations;
//	}
//
//	/**
//	 * @param \rocket\op\gui\EiFieldAssemblerCache $assemblerCache
//	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
//	 * @param DefPropPath[]
//	 * @param string $siStructureType
//	 * @return \rocket\ui\gui\GuiStructureDeclaration
//	 */
//	private function assembleSemiAutoGuiStructureDeclarations($assemblerCache, $eiGuiMaskDeclaration, $defPropPaths, $siStructureTypeRequired) {
//		$guiStructureDeclarations = [];
//
//		foreach ($defPropPaths as $defPropPath) {
//			$displayDefinition = $assemblerCache->assignDefPropPath($defPropPath);
//
//			if ($displayDefinition === null) {
//				continue;
//			}
//
//			$siStructureType = !$siStructureTypeRequired ? null : ($displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
//
//			$guiStructureDeclarations[] = \rocket\ui\gui\GuiStructureDeclaration::createField($defPropPath, $siStructureType,
//					$displayDefinition->getOverwriteLabel(), $displayDefinition->getOverwriteHelpText());
//		}
//
//		return $guiStructureDeclarations;
//	}
//
//
//
//// 	/**
//// 	 * @param DefPropPath $defPropPath
//// 	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
//// 	 * @param bool $defaultDisplayedRequired
//// 	 * @return DisplayDefinition|null
//// 	 */
//// 	private function buildDisplayDefinition($defPropPath, $eiGuiMaskDeclaration, $defaultDisplayedRequired) {
//// 		$eiPropPathStr = (string) $defPropPath->getFirstEiPropPath();
//
//// 		if (!$defPropPath->hasMultipleEiPropPaths()) {
//// 			if (!isset($this->eiGuiPropWrappers[$eiPropPathStr])) {
//// 				return null;
//// 			}
//
//// 			return $this->eiGuiPropWrappers[$eiPropPathStr]->buildDisplayDefinition($eiGuiMaskDeclaration, $defaultDisplayedRequired);
//// 		}
//
//// 		if (!isset($this->eiGuiPropWrappers[$eiPropPathStr])) {
//// 			return null;
//// 		}
//
//// 		return $this->eiGuiPropWrappers[$eiPropPathStr]
//// 				->buildForkDisplayDefinition($defPropPath->getShifted(), $eiGuiMaskDeclaration, $defaultDisplayedRequired);
//// 	}
//
////	/**
////	 * @param \rocket\op\gui\EiFieldAssemblerCache $assemblerCache
////	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
////	 * @param DisplayStructure $displayStructure
////	 */
////	private function assembleDisplayStructure($assemblerCache, $eiGuiMaskDeclaration, $displayStructure) {
////		$guiStructureDeclarations = [];
////
////		foreach ($displayStructure->getDisplayItems() as $displayItem) {
////			if ($displayItem->hasDisplayStructure()) {
////				$guiStructureDeclarations[] = \rocket\ui\gui\GuiStructureDeclaration::createGroup(
////						$this->assembleDisplayStructure($assemblerCache, $eiGuiMaskDeclaration, $displayItem->getDisplayStructure()),
////						$displayItem->getSiStructureType(), $displayItem->getLabel(), $displayItem->getHelpText());
////				continue;
////			}
////
////			$defPropPath = $displayItem->getDefPropPath();
////			$displayDefinition = $assemblerCache->assignDefPropPath($defPropPath);
////			if (null === $displayDefinition) {
////				continue;
////			}
////
////			$guiStructureDeclarations[] = \rocket\ui\gui\GuiStructureDeclaration::createField($defPropPath,
////					$displayItem->getSiStructureType() ?? $displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
////		}
////
////		return $guiStructureDeclarations;
////	}
//
//	/**
//	 * @param N2nContext $n2nContext
//	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
//	 */
////	private function autoInitEiGuiMaskDeclaration(N2nContext $n2nContext, EiGuiMaskDeclaration $eiGuiMaskDeclaration): array {
////// 		$n2nLocale = $eiGuiMaskDeclaration->getEiFrame()->getN2nContext()->getN2nLocale();
////
////		$guiStructureDeclarations = [];
////		foreach ($this->eiGuiPropWrappers as $eiGuiPropWrapper) {
////			$eiPropPath = $eiGuiPropWrapper->getEiPropPath();
////			$eiGuiPropSetup = $eiGuiPropWrapper->buildGuiPropSetup($n2nContext, $eiGuiMaskDeclaration, null);
////
////			if ($eiGuiPropSetup === null) {
////				continue;
////			}
////
////			$eiGuiMaskDeclaration->putEiGuiField($eiPropPath, $eiGuiPropSetup->getEiGuiField());
////
////			$defPropPath = new DefPropPath([$eiPropPath]);
////
////			$displayDefinition = $eiGuiPropSetup->getDisplayDefinition();
////			if (null !== $displayDefinition && $displayDefinition->isDefaultDisplayed()) {
////				$eiGuiMaskDeclaration->putDisplayDefintion($defPropPath, $displayDefinition);
////				$guiStructureDeclarations[(string) $defPropPath] = \rocket\ui\gui\GuiStructureDeclaration
////						::createField($defPropPath, $displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
////			}
////
////			foreach ($eiGuiPropWrapper->getForkedDefPropPaths() as $forkedDefPropPath) {
////				$absDefPropPath = $defPropPath->ext($forkedDefPropPath);
////				$displayDefinition = $eiGuiPropSetup->getForkedDisplayDefinition($forkedDefPropPath);
////
////				if ($displayDefinition === null/* || !$displayDefinition->isDefaultDisplayed()*/) {
////					continue;
////				}
////				$eiGuiMaskDeclaration->putDisplayDefintion($absDefPropPath, $displayDefinition);
////
////				$guiStructureDeclarations[(string) $absDefPropPath] = \rocket\ui\gui\GuiStructureDeclaration
////						::createField($absDefPropPath, $displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
////			}
////		}
////
////		$this->initEiGuiMaskDeclaration($eiGuiMaskDeclaration);
////
////		return $guiStructureDeclarations;
////	}
//
////	/**
////	 * @param DefPropPath $defPropPath
////	 * @param DisplayDefinition $displayDefinition
////	 * @param DisplayItem $displayItem
////	 */
////	private function createGuiStructureDeclaration($defPropPath, $displayDefinition, $displayItem) {
////		if ($displayItem === null) {
////
////		}
////
////		return GuiStructureDeclaration::createField($defPropPath,
////				$displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
////	}
//
//
//// 	/**
//// 	 * @param DefPropPath $defPropPath
//// 	 * @param DisplayItem|null $displayItem
//// 	 * @return GuiStructureDeclaration
//// 	 */
//// 	private function createFieldDeclaration($defPropPath, $eiProp, $displayItem) {
//// 		$eiGuiProp = $this->getGuiPropWrapper($eiPropPath);
//// 	}
//
//	private array $guiDefinitionListeners = array();
//
//	function registerEiGuiDefinitionListener(\rocket\op\ei\manage\gui\EiGuiDefinitionListener $guiDefinitionListener) {
//		$this->guiDefinitionListeners[spl_object_hash($guiDefinitionListener)] = $guiDefinitionListener;
//	}
//
//	function unregisterEiGuiDefinitionListener(\rocket\op\ei\manage\gui\EiGuiDefinitionListener $guiDefinitionListener) {
//		unset($this->guiDefinitionListeners[spl_object_hash($guiDefinitionListener)]);
//	}
//
//	/**
//	 * @return \rocket\op\ei\manage\gui\EiGuiDefinitionListener
//	 */
//	function getEiGuiDefinitionListeners() {
//		return $this->guiDefinitionListeners;
//	}
//
//	function createEiSiMaskId(): EiSiMaskId {
//		return new EiSiMaskId($this->eiMask->getEiTypePath(), $this->viewMode);
//	}
//
//	function createSiMaskIdentifier(): SiMaskIdentifier {
//		$eiSiMaskId = $this->createEiSiMaskId();
//		return new SiMaskIdentifier($eiSiMaskId->__toString(), $this->eiMask->getEiType()->getSupremeEiType()->getId());
//	}
//
//	public function createSiMaskQualifier(N2nLocale $n2nLocale): SiMaskQualifier {
//		return new SiMaskQualifier($this->createSiMaskIdentifier(),
//				$this->getEiMask()->getLabelLstr()->t($n2nLocale), $this->getEiMask()->getIconType());
//	}
//
////	function createGuiMask(EiFrame $eiFrame): GuiMask {
////		$guiMask = new GuiMask($this->createSiMaskQualifier($eiFrame->getN2nContext()->getN2nLocale()), null);
////
////		foreach ($this->eiGuiPropWrappers as $eiGuiPropWrapper) {
////			$eiGuiPropWrapper->getForkedDefPropPaths();
////			$eiGuiPropWrapper->
////			$guiMask->putGuiProp(new GuiFieldPath([]), $eiGuiPropWrapper->get);setGuiProps(createGuiProps);
////		}
////
////		$guiMask->setGuiControlMap($this->createGeneralGuiControlsMap($eiFrame));
////
////		return $guiMask;
////	}



}