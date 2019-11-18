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
namespace rocket\ei\manage\gui;

use rocket\ei\EiPropPath;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\util\Eiu;
use rocket\core\model\Rocket;
use n2n\core\container\N2nContext;
use rocket\ei\manage\entry\UnknownEiFieldExcpetion;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use rocket\ei\manage\gui\control\EntryGuiControl;
use rocket\ei\manage\gui\control\SelectionGuiControl;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\gui\control\GuiControl;
use rocket\ei\manage\gui\control\GeneralGuiControl;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\IdPath;
use n2n\util\StringUtils;
use rocket\ei\EiCommandPath;
use n2n\l10n\Lstr;
use rocket\ei\mask\model\DisplayStructure;
use rocket\si\meta\SiStructureType;
use rocket\ei\mask\EiMask;
use rocket\ei\component\prop\EiPropWrapper;

class GuiDefinition {
	/**
	 * @var EiMask
	 */
	private $eiMask;
	/**
	 * @var GuiPropWrapper[]
	 */
	private $guiPropWrappers = array();
	/**
	 * @var EiPropPath[]
	 */
	private $eiPropPaths = array();
	/**
	 * @var GuiCommand[]
	 */
	private $guiCommands;
	
	function __construct(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @param string $id
	 * @param GuiProp $guiProp
	 * @param EiPropPath $guiPropPath
	 * @throws GuiException
	 */
	function putGuiProp(EiPropPath $eiPropPath, GuiProp $guiProp) {
		$eiPropPathStr = (string) $eiPropPath;
		
		if (isset($this->guiPropWrappers[$eiPropPathStr])) {
			throw new GuiException('GuiProp for EiPropPath \'' . $eiPropPathStr . '\' is already registered');
		}
		
		$this->guiPropWrappers[$eiPropPathStr] = new GuiPropWrapper($this, $eiPropPath, $guiProp);
		$this->eiPropPaths[$eiPropPathStr] = $eiPropPath;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 */
	function removeGuiProp(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		
		unset($this->guiPropWrappers[$eiPropPathStr]);
		unset($this->eiPropPaths[$eiPropPathStr]);
	}
		
	/**
	 * @param GuiPropPath $guiPropPath
	 */
	function removeGuiPropByPath(GuiPropPath $guiPropPath) {
		$guiDefinition = $this;
		$eiPropPaths = $guiPropPath->toArray();
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			if (empty($eiPropPaths)) {
				$guiDefinition->removeGuiProp($eiPropPath);
				return;
			}
			
			$guiDefinition = $guiDefinition->getGuiPropFork($eiPropPath)->getForkedGuiDefinition();
		
			if ($guiDefinition === null) {
				return;
			}
		}
	}
	
	
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws GuiException
	 * @return GuiPropWrapper
	 */
	function getGuiPropWrapper(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->guiPropWrappers[$eiPropPathStr])) {
			throw new GuiException('No GuiProp with id \'' . $eiPropPathStr . '\' registered');
		}
		
		return $this->guiPropWrappers[$eiPropPathStr];
	}

	/**
	 * @return GuiPropWrapper[]
	 */
	function getGuiPropWrappers() {
		return $this->guiPropWrappers;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return boolean
	 */
	function containsGuiProp(GuiPropPath $guiPropPath) {
		$eiPropPaths = $guiPropPath->toArray();
		$guiDefinition = $this;
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			if (empty($eiPropPaths)) {
				return $guiDefinition->containsEiPropPath($eiPropPath);
			}
			
			$guiDefinition = $guiDefinition->getGuiPropFork($eiPropPath)->getForkedGuiDefinition();
		}
		
		return true;
	}
	
	/**
	 * @param string $eiPropPath
	 * @param GuiPropFork $guiPropFork
	 */
	function putGuiPropFork(EiPropPath $eiPropPath, GuiPropFork $guiPropFork) {
		$eiPropPathStr = (string) $eiPropPath;
		
		$this->guiPropForkWrappers[$eiPropPathStr] = new GuiPropForkWrapper($this, $eiPropPath, $guiPropFork);
		$this->eiPropPaths[$eiPropPathStr] = $eiPropPath;
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	function containsLevelGuiPropForkId(string $id) {
		return isset($this->guiPropForkWrappers[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiPropFork
	 */
	function getGuiPropFork(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->guiPropForkWrappers[$eiPropPathStr])) {
			throw new GuiException('No GuiPropFork with id \'' . $eiPropPathStr . '\' registered.');
		}
		
		return $this->guiPropForkWrappers[$eiPropPathStr];
	}
	
// 	function getAllGuiProps() {
// 		return $this->buildGuiProps(array());
// 	}
	
// 	protected function buildGuiProps(array $baseEiPropPaths) {
// 		$guiProps = array();
		
// 		foreach ($this->eiPropPaths as $eiPropPath) {
// 			$eiPropPathStr = (string) $eiPropPath;
			
// 			if (isset($this->guiPropWrappers[$eiPropPathStr])) {
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;
// 				$guiProps[(string) new GuiPropPath($currentEiPropPaths)] = $this->guiPropWrappers[$eiPropPathStr];
// 			}
				
// 			if (isset($this->guiPropForkWrappers[$eiPropPathStr])) {
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;
					
// 				$guiProps = array_merge($guiProps, $this->guiPropForkWrappers[$eiPropPathStr]->getForkedGuiDefinition()
// 						->buildGuiProps($currentEiPropPaths));
// 			}
// 		}
		
// 		return $guiProps;
// 	}
	
// 	/**
// 	 * @param GuiPropPath[] $guiPropPaths
// 	 * @return GuiPropPath[]
// 	 */
// 	function filterGuiPropPaths(array $guiPropPaths) {
// 		return array_filter($guiPropPaths, function (GuiPropPath $guiPropPath) {
// 			return $this->containsGuiProp($guiPropPath);
// 		});
// 	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return bool
	 */
	function containsEiPropPath(EiPropPath $eiPropPath) {
		return isset($this->eiPropPaths[(string) $eiPropPath]);
	}
	
	/**
	 * @return \rocket\ei\manage\gui\field\GuiPropPath[]
	 */
	function getGuiPropPaths() {
		$guiPropPaths = array();
		
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			$guiPropPath = new GuiPropPath([$eiPropPath]);

			$guiPropPaths[] = $guiPropPath;
			
			foreach ($this->guiPropWrappers[$eiPropPathStr]->getForkedGuiPropPaths() 
					as $forkedGuiPropPath) {
				$guiPropPaths[] = $guiPropPath->ext($forkedGuiPropPath);			
			}
		}
		
		return $guiPropPaths;
	}
	
// 	function assembleDefaultGuiProps() {
// 		$guiPropAssemblies = [];
// 		$this->composeGuiPropAssemblies($guiPropAssemblies, []);
// 		return $guiPropAssemblies;
// 	}
	
// 	function assembleGuiProps(EiGui $eiGui, array $guiPropPaths) {
// 		ArgUtils::valArray($guiPropPaths, GuiPropPath::class);
		
// // 		$eiu = new Eiu($eiGui);
		
// 		$guiPropAssemblies = [];
		
// 		foreach ($guiPropPaths as $guiPropPath) {
// 			$guiProp = $this->getGuiPropByGuiPropPath($guiPropPath);
			
// 			$displayDefinition = $guiProp->getDisplayDefinition();
// 			if ($displayDefinition === null) {
// 				continue;
// 			}
			
// 			$guiPropAssemblies[(string) $guiPropPath] = new GuiPropAssembly($guiPropPath, $displayDefinition);
// 		}
		
// 		return $guiPropAssemblies;
// 	}
	
	
// 	/**
// 	 * @param array $baseEiPropPaths
// 	 * @param Eiu $eiu
// 	 * @param int $minTestLevel
// 	 */
// 	protected function composeGuiPropAssemblies(array &$guiPropAssemblies, array $baseEiPropPaths) {
// 		foreach ($this->eiPropPaths as $eiPropPath) {
// 			$eiPropPathStr = (string) $eiPropPath;
			
// 			$displayDefinition = null;
// 			if (isset($this->guiPropWrappers[$eiPropPathStr])
// 					&& null !== ($displayDefinition = $this->guiPropWrappers[$eiPropPathStr]->getDisplayDefinition())
// 					&& $displayDefinition->isDefaultDisplayed()) {
						
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;
				
// 				$guiPropPath = new GuiPropPath($currentEiPropPaths);
// 				$guiPropAssemblies[(string) $guiPropPath] = new GuiPropAssembly($guiPropPath, $displayDefinition);
// 			}
			
// 			if (isset($this->guiPropForkWrappers[$eiPropPathStr])
// 					&& null !== ($forkedGuiDefinition = $this->guiPropForkWrappers[$eiPropPathStr]->getForkedGuiDefinition())) {
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;
// 				$forkedGuiDefinition->composeGuiPropAssemblies($guiPropAssemblies, $currentEiPropPaths);
// 			}
// 		}
// 	}
	
// 	function createDefaultDisplayStructure(EiGui $eiGui) {
// 		$displayStructure = new DisplayStructure();
// 		$this->composeDisplayStructure($displayStructure, array(), new Eiu($eiGui));
// 		return $displayStructure;
// 	}
	

	
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return \rocket\ei\manage\gui\GuiPropWrapper
	 * @throws GuiException
	 */
	function getGuiPropWrapperByGuiPropPath(GuiPropPath $guiPropPath) {
		$ids = $guiPropPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->getGuiPropWrapper($id);
			}
			
			$guiDefinition = $guiDefinition->getGuiPropFork($id)->getForkedGuiDefinition();
			if ($guiDefinition === null) {
				break;
			}
		}	
		
		throw new GuiException('GuiPropPath could not be resolved: ' . $guiPropPath);
	}
	
	

	
	/**
	 * @param EiEntry $eiEntry
	 * @param GuiPropPath $guiPropPath
	 * @throws UnknownEiFieldExcpetion
	 * @return \rocket\ei\manage\gui\EiFieldAbstraction|null
	 */
	function determineEiFieldAbstraction(N2nContext $n2nContext, EiEntry $eiEntry, GuiPropPath $guiPropPath) {
		$eiPropPaths = $guiPropPath->toArray();
		$id = array_shift($eiPropPaths);
		if (empty($eiPropPaths)) {
			return $eiEntry->getEiFieldWrapper($id);
		}
		
		$guiPropFork = $this->getGuiPropFork($id);
		return $guiPropFork->determineEiFieldAbstraction(new Eiu($n2nContext, $eiEntry), new GuiPropPath($eiPropPaths));
	}
	
	/**
	 * @return GuiPropFork[]
	 */
	function getGuiPropForkWrappers() {
		return $this->guiPropForkWrappers;
	}
		
	/**
	 * @param string $id
	 * @param GuiProp $guiProp
	 * @param EiPropPath $guiPropPath
	 * @throws GuiException
	 */
	function putGuiCommand(EiCommandPath $eiCommandPath, GuiCommand $guiCommand) {
		$eiCommandPathStr = (string) $eiCommandPath;
		
		if (isset($this->guiCommand[$eiCommandPathStr])) {
			throw new GuiException('GuiCommand for EiCommandPath \'' . $eiCommandPathStr . '\' is already registered');
		}
		
		$this->guiCommands[$eiCommandPathStr] = $guiCommand;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @param GuiControlPath $guiControlPath
	 * @return EntryGuiControl
	 * @throws UnknownGuiControlException
	 */
	function createEntryGuiControl(EiFrame $eiFrame, EiEntry $eiEntry, GuiControlPath $guiControlPath): EntryGuiControl {
		if ($guiControlPath->size() != 2) {
			throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
		}
		
		$eiu = new Eiu($eiFrame, $eiEntry);
		$cmdId = $guiControlPath->getFirstId();
		$controlId = $guiControlPath->getLastId();
		
		foreach ($this->guiCommands as $id => $guiCommand) {
			if ($cmdId != $id) {
				continue;
			}
			
			$guiControls = $this->extractEntryGuiControls($guiCommand, $id, $eiu);
			if ($guiControls[$controlId]) {
				return $guiControls[$controlId];
			}
		}
		
		throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @return GuiControl[]
	 */
	function createEntryGuiControls(EiGui $eiGui, EiEntry $eiEntry): array {
		$eiu = new Eiu($eiGui, $eiEntry);
		
		$guiControls = [];
		foreach ($this->guiCommands as $id => $guiCommand) {
			foreach ($this->extractEntryGuiControls($guiCommand, $id, $eiu) as $entryGuiControl) {
				$guiControlPath = new GuiControlPath([$id, $entryGuiControl->getId()]);
				
				$guiControls[(string) $guiControlPath] = $entryGuiControl;
			}
		}
		return $guiControls;
	}
	
	/**
	 * @param GuiCommand $guiCommand
	 * @param string $guiCommandId
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\gui\control\GuiControl[]
	 */
	private function extractEntryGuiControls(GuiCommand $guiCommand, string $guiCommandId, Eiu $eiu) {
		$entryGuiControls = $guiCommand->createEntryGuiControls($eiu);
		ArgUtils::valArrayReturn($entryGuiControls, $guiCommand, 'createEntryGuiControls', EntryGuiControl::class);
		
		return $this->mapGuiControls($entryGuiControls, $guiCommand, EntryGuiControl::class);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @param GuiControlPath $guiControlPath
	 * @return GeneralGuiControl
	 * @throws UnknownGuiControlException
	 */
	function createGeneralGuiControl(EiGui $eiGui, GuiControlPath $guiControlPath) {
		if ($guiControlPath->size() != 2) {
			throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
		}
		
		$eiu = new Eiu($eiGui);
		$cmdId = $guiControlPath->getFirstId();
		$controlId = $guiControlPath->getLastId();
		
		foreach ($this->guiCommands as $id => $guiCommand) {
			if ($cmdId != $id) {
				continue;
			}
			
			$guiControls = $this->extractGeneralGuiControls($guiCommand, $id, $eiu);
			if ($guiControls[$controlId]) {
				return $guiControls[$controlId];
			}
		}
		
		throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
	}
	
	
	/**
	 * @param EiFrame $eiFrame
	 * @return EntryGuiControl[]
	 */
	function createGeneralGuiControls(EiGui $eiGui): array {
		$eiu = new Eiu($eiGui);
		
		$siControls = [];
		foreach ($this->guiCommands as $id => $guiCommand) {
			foreach ($this->extractGeneralGuiControls($guiCommand, $id, $eiu) as $generalGuiControl) {
				$guiControlPath = new GuiControlPath([$id, $generalGuiControl->getId()]);
				
				$siControls[(string) $guiControlPath] = $generalGuiControl;
			}
		}
		return $siControls;
	}
	
	/**
	 * @param GuiCommand $guiCommand
	 * @param string $guiCommandId
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\gui\control\GeneralGuiControl[]
	 */
	private function extractGeneralGuiControls(GuiCommand $guiCommand, string $guiCommandId, Eiu $eiu) {
		$generalGuiControls = $guiCommand->createGeneralGuiControls($eiu);
		ArgUtils::valArrayReturn($generalGuiControls, $guiCommand, 'extractGeneralGuiControls', GeneralGuiControl::class);
		
		return $this->mapGuiControls($generalGuiControls, $guiCommand, GeneralGuiControl::class);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiControlPath $guiControlPath
	 * @return SelectionGuiControl
	 * @throw UnknownGuiControlException
	 */
	function createSelectionGuiControl(EiFrame $eiFrame, GuiControlPath $guiControlPath) {
		if ($guiControlPath->size() != 2) {
			throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
		}
		
		$eiu = new Eiu($eiFrame);
		$cmdId = $guiControlPath->getFirstId();
		$controlId = $guiControlPath->getLastId();
		
		foreach ($this->guiCommands as $id => $guiCommand) {
			if ($cmdId != $id) {
				continue;
			}
			
			$guiControls = $this->extractSelectionGuiControls($guiCommand, $id, $eiu);
			if ($guiControls[$controlId]) {
				return $guiControls[$controlId];
			}
		}
		
		throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @return SelectionGuiControl[]
	 */
	function createSelectionGuiControls(EiFrame $eiFrame): array {
		$eiu = new Eiu($eiFrame);
		
		$guiControls = [];
		foreach ($this->guiCommands as $id => $guiCommand) {
			foreach ($this->extractSelectionGuiControls($guiCommand, $id, $eiu) as $selectionGuiControl) {
				$guiControlPath = new GuiControlPath([$id, $selectionGuiControl->getId()]);
				
				$guiControls[(string) $guiControlPath] = $selectionGuiControl;
			}
		}
		return $guiControls;
	}
	
	/**
	 * @param GuiCommand $guiCommand
	 * @param string $guiCommandId
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\gui\control\GeneralGuiControl[]
	 */
	private function extractSelectionGuiControls(GuiCommand $guiCommand, string $guiCommandId, Eiu $eiu) {
		$selectionGuiControls = $guiCommand->createSelectionGuiControls($eiu);
		ArgUtils::valArrayReturn($selectionGuiControls, $guiCommand, 'createSelectionGuiControls', SelectionGuiControl::class);
		
		return $this->mapGuiControls($selectionGuiControls, $guiCommand, SelectionGuiControl::class);
	}
	
	/**
	 * @param GuiControl[] $guiControls
	 * @return GuiControl[]
	 */
	private function mapGuiControls($guiControls, $guiCommand, $guiControlClassName) {
		$mappedGuiControls = [];
		
		foreach ($guiControls as $guiControl) {
			$id = $guiControl->getId();
			
			if (!IdPath::isIdValid($id)) {
				throw new \InvalidArgumentException(StringUtils::strOf($guiCommand) . ' returns '
						. $guiControlClassName . ' with illegal id: ' . $id);
			}
			
			if (isset($mappedGuiControls[$id])) {
				throw new \InvalidArgumentException(StringUtils::strOf($guiCommand) . ' returns multiple '
						. $guiControlClassName . ' objects with id: ' . $id);
			}
			
			$mappedGuiControls[$id] = $guiControl;
		}
		
		return $mappedGuiControls;
	}
	
	/**
	 * @return Lstr[] 
	 */
	function getLabelLstrs() {
		return $this->buildLabelLstrs([]);
	}
	
	/**
	 * @param EiPropPath[] $contextEiPropPaths
	 * @return Lstr[]
	 */
	private function buildLabelLstrs(array $contextEiPropPaths) {
		$labelLstrs = array();
		
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			
			if (isset($this->guiPropWrappers[$eiPropPathStr])) {
				$currentEiPropPaths = $contextEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$labelLstrs[(string) new GuiPropPath($currentEiPropPaths)] = $this->guiPropWrappers[$eiPropPathStr]->getLabelLstr();
			}
			
			if (isset($this->guiPropForkWrappers[$eiPropPathStr])) {
				$currentEiPropPaths = $contextEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				
				$guiPropPaths = array_merge($labelLstrs, $this->guiPropForkWrappers[$eiPropPathStr]
						->getForkedGuiDefinition()->buildLabelLstrs($currentEiPropPaths));
			}
		}
		
		return $guiPropPaths;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param int $viewMode
	 * @return \rocket\ei\manage\gui\EiGuiLayout
	 */
	function createEiGuiLayout(EiFrame $eiFrame, int $viewMode) {
		ArgUtils::assertTrue($this->eiMask->isA($eiFrame->getContextEiEngine()->getEiMask()));
		
		$eiGui = new EiGui($eiFrame, $this, $viewMode);
		
		$guiStructureDeclarations = $this->initEiGuiFromDisplayScheme($eiGui);
		
		return new EiGuiLayout($guiStructureDeclarations, $eiGui);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param int $viewMode
	 * @param array $guiPropPaths
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	function createEiGui(EiFrame $eiFrame, int $viewMode, array $guiPropPaths) {
		ArgUtils::assertTrue($this->eiMask->isA($eiFrame->getContextEiEngine()->getEiMask()));
		
		$eiGui = new EiGui($eiFrame, $this, $viewMode);
		
		$this->semiAutoInitEiGui($eiGui, $guiPropPaths);
		
		return $eiGui;
	}
	
	/**
	 * @param EiGui $eiGui
	 * @param GuiPropPath[] $guiPropPaths
	 * @return GuiPropPath[]
	 */
	private function filterGuiPropPaths($eiGui, $guiPropPaths) {
		$filteredGuiPropPaths = [];
		foreach ($guiPropPaths as $key => $guiPropPath) {
			if ($this->containsGuiProp($guiPropPath)) {
				$filteredGuiPropPaths[$key] = $guiPropPath;
			}
		}
		return $filteredGuiPropPaths;
	}
	
	/**
	 * @param EiGui $eiGui
	 * @param GuiPropPath[] $guiPropPaths
	 */
	private function initEiGui($eiGui, $guiPropPaths) {
		$this->eiMask->getEiModificatorCollection()->setupEiGui($eiGui);
		
		$eiGui->markInitialized();
	}
	
	/**
	 * @param EiGui $eiGui
	 * @return GuiStructureDeclaration[]
	 */
	private function initEiGuiFromDisplayScheme(EiGui $eiGui) {
		$displayScheme = $this->eiMask->getDisplayScheme();
		
		$displayStructure = null;
		switch ($eiGui->getViewMode()) {
			case ViewMode::BULKY_READ:
				$displayStructure = $displayScheme->getDetailDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_EDIT:
				$displayStructure = $displayScheme->getEditDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_ADD:
				$displayStructure = $displayScheme->getAddDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::COMPACT_READ:
			case ViewMode::COMPACT_EDIT:
			case ViewMode::COMPACT_ADD:
				$displayStructure = $displayScheme->getOverviewDisplayStructure();
				break;
		}
		
		if ($displayStructure === null) {
			return $this->autoInitEiGui($eiGui);
		} 
		
		return $this->semiAutoInitEiGui($eiGui, $displayStructure->getAllGuiPropPaths());
	}
	
	/**
	 * @param EiGui $eiGui
	 * @param GuiPropPath[] $possibleGuiPropPaths
	 * @return GuiStructureDeclaration[]
	 */
	private function semiAutoInitEiGui($eiGui, $possibleGuiPropPaths) {
		$assemblerCache = new EiFieldAssemblerCache($eiGui, $possibleGuiPropPaths);
		$guiStructureDeclarations = $this->assembleDisplayStructure($assemblerCache, $eiGui, $displayStructure->purified($eiGui));
		$this->initEiGui($eiGui, $assemblerCache->getGuiPropPaths());
		return $guiStructureDeclarations;
	}
	
	/**
	 * @param EiGui $eiGui
	 * @param string $siStructureType
	 * @return GuiStructureDeclaration[]
	 */
	private function assembleAutoGuiStructureDeclarations($eiGui, $siStructureTypeRequired) {
		$guiStructureDeclarations = [];
		
		foreach ($this->getGuiPropPaths() as $guiPropPath) {
			$displayDefinition = $this->buildDisplayDefinition($guiPropPath, $eiGui, true);
			
			if ($displayDefinition === null) {
				continue;
			}
			
			$siStructureType = !$siStructureTypeRequired ? null : ($displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
			
			$guiStructureDeclarations[] = GuiStructureDeclaration::createField($guiPropPath, $siStructureType, 
					$displayDefinition->getOverwriteLabel(), $displayDefinition->getOverwriteHelpText());
		}
			
		return $guiStructureDeclarations;
	}
	
	
	
// 	/**
// 	 * @param GuiPropPath $guiPropPath
// 	 * @param EiGui $eiGui
// 	 * @param bool $defaultDisplayedRequired
// 	 * @return DisplayDefinition|null
// 	 */
// 	private function buildDisplayDefinition($guiPropPath, $eiGui, $defaultDisplayedRequired) {
// 		$eiPropPathStr = (string) $guiPropPath->getFirstEiPropPath();
		
// 		if (!$guiPropPath->hasMultipleEiPropPaths()) {
// 			if (!isset($this->guiPropWrappers[$eiPropPathStr])) {
// 				return null;
// 			}
			
// 			return $this->guiPropWrappers[$eiPropPathStr]->buildDisplayDefinition($eiGui, $defaultDisplayedRequired);
// 		}
		
// 		if (!isset($this->guiPropWrappers[$eiPropPathStr])) {
// 			return null;
// 		}
		
// 		return $this->guiPropWrappers[$eiPropPathStr]
// 				->buildForkDisplayDefinition($guiPropPath->getShifted(), $eiGui, $defaultDisplayedRequired);
// 	}
	
	/**
	 * @param EiFieldAssemblerCache $assemblerCache
	 * @param EiGui $eiGui
	 * @param DisplayStructure $displayStructure
	 */
	private function assembleDisplayStructure($assemblerCache, $eiGui, $displayStructure) {
		$guiStructureDeclarations = [];
		
		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			if ($displayItem->hasDisplayStructure()) {
				$guiStructureDeclarations[] = GuiStructureDeclaration::createGroup(
						$this->assembleDisplayStructure($assemblerCache, $displayItem->getDisplayStructure()),
						$displayItem->getSiStructureType(), $displayItem->getLabel(), $displayItem->getHelpText());
				continue;
			}
			
			$guiPropPath = $displayItem->getGuiPropPath();
			$displayDefinition = $assemblerCache->buildDisplayDefinition($guiPropPath);
			if (null === $displayDefinition) {
				continue;
			}
			
			$guiPropWrapper = $eiGui->getGuiDefinition()->getGuiPropWrapperByGuiPropPath($guiPropPath);
			$guiStructureDeclarations[] = $this->createGuiStructureDeclaration($guiPropPath, $guiPropWrapper, $displayDefinition, $n2nLocale);
		}
		
		return $guiStructureDeclarations;
	}
	
	/**
	 * @param EiGui $eiGui
	 */
	private function autoInitEiGui($eiGui) {
		$n2nLocale = $eiGui->getEiFrame()->getN2nContext()->getN2nLocale();
		
		$guiPropPaths = [];
		$guiStructureDeclarations = [];
		
		foreach ($this->guiPropWrappers as $guiPropWrapper) {
			$eiPropPath = $guiPropWrapper->getEiPropPath();
			$guiPropSetup = $guiPropWrapper->buildGuiPropSetup($eiGui, null);
			
			if ($guiPropSetup === null) {
				continue;
			}
			
			$eiGui->putGuiFieldAssembler($eiPropPath, $guiPropSetup->getGuiFieldAssembler());
			
			$guiPropPath = new GuiPropPath([$eiPropPath]);
			
			if (null !== ($displayDefinition = $guiPropSetup->getDisplayDefinition())) {
				$eiGui->putDisplayDefintion($guiPropPath, $displayDefinition);
				
				$guiStructureDeclarations[(string) $guiPropPath] = $this->createGuiStructureDeclaration(
						$guiPropPath, $guiPropWrapper, $displayDefinition, $n2nLocale);
			}
			
			foreach ($guiPropWrapper->getForkedGuiPropPaths() as $forkedGuiPropPath) {
				$rGuiPropPath = $guiPropPath->ext($forkedGuiPropPath);
				$displayDefinition = $guiPropSetup->getForkedDisplayDefinition($rGuiPropPath);
				
				if ($displayDefinition === null) {
					continue;
				}
				
				$eiGui->putDisplayDefintion($rGuiPropPath, $displayDefinition);
				
				$guiStructureDeclarations[(string) $rGuiPropPath] = $this->createGuiStructureDeclaration(
						$guiPropPath,
						$guiPropWrapper->getForkedGuiPropWrapper($rGuiPropPath), 
						$displayDefinition, $n2nLocale);
				
			}
		}
		
		$this->initEiGui($eiGui, $guiPropPaths);
		
		return $guiStructureDeclarations;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @param GuiPropWrapper $guiPropWrapper
	 * @param DisplayDefinition $displayDefinition
	 */
	private function createGuiStructureDeclaration($guiPropPath, $guiPropWrapper, $displayDefinition, $n2nLocale) {
		$eiProp = $guiPropWrapper->getEiProp();
		$label = $eiProp->getLabelLstr()->t($n2nLocale);
		$helpText = null;
		if (null !== ($helpTextLstr = $eiProp->getHelpTextLstr())) {
			$helpText = $helpTextLstr->t($n2nLocale);
		}
		return GuiStructureDeclaration::createField($guiPropPath,
				$displayDefinition->getSiStructureType() ?? SiStructureType::ITEM,
				$displayDefinition->getOverwriteLabel() ?? $label,
				$displayDefinition->getOverwriteHelpText() ?? $helpText);
	}
	
	
	
	
// 	/**
// 	 * @param GuiPropPath $guiPropPath
// 	 * @param DisplayItem|null $displayItem
// 	 * @return GuiStructureDeclaration
// 	 */
// 	private function createFieldDeclaration($guiPropPath, $eiProp, $displayItem) {
// 		$guiProp = $this->getGuiPropWrapper($eiPropPath);
// 	}
	
// 	private $guiDefinitionListeners = array();
	
// 	function registerGuiDefinitionListener(GuiDefinitionListener $guiDefinitionListener) {
// 		$this->guiDefinitionListeners[spl_object_hash($guiDefinitionListener)] = $guiDefinitionListener;
// 	}
	
// 	function unregisterGuiDefinitionListener(GuiDefinitionListener $guiDefinitionListener) {
// 		unset($this->guiDefinitionListeners[spl_object_hash($guiDefinitionListener)]);
// 	}
	
// 	/**
// 	 * @return GuiDefinitionListener[]
// 	 */
// 	function getGuiDefinitionListeners() {
// 		return $this->guiDefinitionListeners;
// 	}
}


class EiFieldAssemblerCache {
	private $eiGui;
	private $displayStructure;
	/**
	 * @var GuiPropPath[]
	 */
	private $possibleGuiPropPaths = [];
	/**
	 * @var GuiDefinition
	 */
	private $guiDefinition;
	/**
	 * @var GuiPropPath[]
	 */
	private $guiPropPaths = [];
	/**
	 * @var GuiPropSetup[]
	 */
	private $guiPropSetups = [];
	
	/**
	 * @param EiGui $eiGui
	 * @param array $possibleGuiPropPaths
	 */
	function __construct(EiGui $eiGui, array $possibleGuiPropPaths) {
		$this->eiGui = $eiGui;
		$this->possibleGuiPropPaths = $possibleGuiPropPaths;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return GuiFieldAssembler
	 */
	private function assemble(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		
		if (isset($this->guiPropSetups[$eiPropPathStr])) {
			return $this->guiPropSetups[$eiPropPathStr];
		}
		
		$guiPropWrapper = $this->guiDefinition->getGuiPropWrapper($eiPropPath);
		$guiPropSetup = $guiPropWrapper->buildGuiPropSetup($this->eiGui, 
				$this->filterForkedGuiPropPaths($eiPropPath));
		$this->eiGui->putGuiFieldAssembler($eiPropPath, $guiPropSetup->getGuiFieldAssembler());
		$this->guiPropSetups[$eiPropPathStr] = $guiPropSetup;
		
		return $guiPropSetup;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return DisplayDefinition|null
	 */
	function assignGuiPropPath(GuiPropPath $guiPropPath) {
		$guiPropSetup = $this->assemble($guiPropPath->getFirstEiPropPath());
		
		$displayDefinition = null;
		if (!$guiPropPath->hasMultipleEiPropPaths()) {
			$displayDefinition = $guiPropSetup->getDisplayDefinition();
		} else {
			$displayDefinition = $guiPropSetup->getForkedDisplayDefinition($guiPropPath->getShifted());
		}
		
		if ($displayDefinition !== null) {
			$this->eiGui->putDisplayDefintion($guiPropPath, $displayDefinition);
		}
		
		return $displayDefinition;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return GuiPropPath[]
	 */
	private function filterForkedGuiPropPaths($eiPropPath) {
		$forkedGuiPropPaths = [];
		foreach ($this->possibleGuiPropPaths as $possibleGuiPropPath) {
			if ($possibleGuiPropPath->getFirstEiPropPath()->equals($eiPropPath)) {
				$forkedGuiPropPaths[] = $possibleGuiPropPath->getShifted();
			}
		}
		return $forkedGuiPropPaths;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\field\GuiPropPath[]
	 */
	function getGuiPropPaths() {
		return $this->guiPropPaths;
	}
}
