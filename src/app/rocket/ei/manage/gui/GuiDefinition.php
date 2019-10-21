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
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\IdPath;
use n2n\util\StringUtils;
use rocket\ei\EiCommandPath;
use n2n\l10n\Lstr;
use rocket\ei\mask\model\DisplayStructure;
use rocket\ei\mask\model\DisplayItem;

class GuiDefinition {	
	private $guiProps = array();
	private $guiPropForks = array();
	private $eiPropPaths = array();
	/**
	 * @var GuiCommand[]
	 */
	private $guiCommands;
	
	/**
	 * @param string $id
	 * @param GuiProp $guiProp
	 * @param EiPropPath $guiFieldPath
	 * @throws GuiException
	 */
	function putGuiProp(EiPropPath $eiPropPath, GuiProp $guiProp) {
		$eiPropPathStr = (string) $eiPropPath;
		
		if (isset($this->guiProps[$eiPropPathStr])) {
			throw new GuiException('GuiProp for EiPropPath \'' . $eiPropPathStr . '\' is already registered');
		}
		
		$this->guiProps[$eiPropPathStr] = $guiProp;
		$this->eiPropPaths[$eiPropPathStr] = $eiPropPath;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 */
	function removeGuiProp(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		
		unset($this->guiProps[$eiPropPathStr]);
		unset($this->eiPropPaths[$eiPropPathStr]);
	}
		
	/**
	 * @param GuiFieldPath $guiFieldPath
	 */
	function removeGuiPropByPath(GuiFieldPath $guiFieldPath) {
		$guiDefinition = $this;
		$eiPropPaths = $guiFieldPath->toArray();
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
	 * @param string $id
	 * @return bool
	 */
	function containsEiPropPath(EiPropPath $eiPropPath) {
		return isset($this->eiPropPaths[(string) $eiPropPath]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiProp
	 */
	function getGuiProp(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->guiProps[$eiPropPathStr])) {
			throw new GuiException('No GuiProp with id \'' . $eiPropPathStr . '\' registered');
		}
		
		return $this->guiProps[$eiPropPathStr];
	}

	/**
	 * @return GuiProp[]
	 */
	function getGuiProps() {
		return $this->guiProps;
	}
	
	/**
	 * @param string $eiPropPath
	 * @param GuiPropFork $guiPropFork
	 */
	function putGuiPropFork(EiPropPath $eiPropPath, GuiPropFork $guiPropFork) {
		$eiPropPathStr = (string) $eiPropPath;
		
		$this->guiPropForks[$eiPropPathStr] = $guiPropFork;
		$this->eiPropPaths[$eiPropPathStr] = $eiPropPath;
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	function containsLevelGuiPropForkId(string $id) {
		return isset($this->guiPropForks[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiPropFork
	 */
	function getGuiPropFork(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->guiPropForks[$eiPropPathStr])) {
			throw new GuiException('No GuiPropFork with id \'' . $eiPropPathStr . '\' registered.');
		}
		
		return $this->guiPropForks[$eiPropPathStr];
	}
	
	function getAllGuiProps() {
		return $this->buildGuiProps(array());
	}
	
	protected function buildGuiProps(array $baseEiPropPaths) {
		$guiProps = array();
		
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			
			if (isset($this->guiProps[$eiPropPathStr])) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$guiProps[(string) new GuiFieldPath($currentEiPropPaths)] = $this->guiProps[$eiPropPathStr];
			}
				
			if (isset($this->guiPropForks[$eiPropPathStr])) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
					
				$guiProps = array_merge($guiProps, $this->guiPropForks[$eiPropPathStr]->getForkedGuiDefinition()
						->buildGuiProps($currentEiPropPaths));
			}
		}
		
		return $guiProps;
	}
	
	/**
	 * @deprecated use {@see GuiDefinition::getGuiFieldPaths()}
	 * @return \rocket\ei\manage\gui\field\GuiFieldPath[]
	 */
	function getAllGuiFieldPaths() {
		return $this->getGuiFieldPaths();
	}
	
	/**
	 * @param GuiFieldPath[] $guiFieldPaths
	 * @return GuiFieldPath[]
	 */
	function filterGuiFieldPaths(array $guiFieldPaths) {
		return array_filter($guiFieldPaths, function (GuiFieldPath $guiFieldPath) {
			return $this->containsGuiProp($guiFieldPath);
		});
	}
	
	/**
	 * @return \rocket\ei\manage\gui\field\GuiFieldPath[]
	 */
	function getGuiFieldPaths() {
		return $this->buildGuiFieldPaths(array());
	}
	
	protected function buildGuiFieldPaths(array $baseEiPropPaths) {
		$guiFieldPaths = array();
		
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			
			if (isset($this->guiProps[$eiPropPathStr])) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$guiFieldPaths[] = new GuiFieldPath($currentEiPropPaths);
			}
			
			if (isset($this->guiPropForks[$eiPropPathStr])) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				
				$guiFieldPaths = array_merge($guiFieldPaths, $this->guiPropForks[$eiPropPathStr]->getForkedGuiDefinition()
						->buildGuiFieldPaths($currentEiPropPaths));
			}
		}
		
		return $guiFieldPaths;
	}
	
	function assembleDefaultGuiProps() {
		$guiPropAssemblies = [];
		$this->composeGuiPropAssemblies($guiPropAssemblies, []);
		return $guiPropAssemblies;
	}
	
	function assembleGuiProps(EiGui $eiGui, array $guiFieldPaths) {
		ArgUtils::valArray($guiFieldPaths, GuiFieldPath::class);
		
// 		$eiu = new Eiu($eiGui);
		
		$guiPropAssemblies = [];
		
		foreach ($guiFieldPaths as $guiFieldPath) {
			$guiProp = $this->getGuiPropByGuiFieldPath($guiFieldPath);
			
			$displayDefinition = $guiProp->getDisplayDefinition();
			if ($displayDefinition === null) {
				continue;
			}
			
			$guiPropAssemblies[(string) $guiFieldPath] = new GuiPropAssembly($guiFieldPath, $displayDefinition);
		}
		
		return $guiPropAssemblies;
	}
	
	
	/**
	 * @param array $baseEiPropPaths
	 * @param Eiu $eiu
	 * @param int $minTestLevel
	 */
	protected function composeGuiPropAssemblies(array &$guiPropAssemblies, array $baseEiPropPaths) {
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			
			$displayDefinition = null;
			if (isset($this->guiProps[$eiPropPathStr])
					&& null !== ($displayDefinition = $this->guiProps[$eiPropPathStr]->getDisplayDefinition())
					&& $displayDefinition->isDefaultDisplayed()) {
						
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				
				$guiFieldPath = new GuiFieldPath($currentEiPropPaths);
				$guiPropAssemblies[(string) $guiFieldPath] = new GuiPropAssembly($guiFieldPath, $displayDefinition);
			}
			
			if (isset($this->guiPropForks[$eiPropPathStr])
					&& null !== ($forkedGuiDefinition = $this->guiPropForks[$eiPropPathStr]->getForkedGuiDefinition())) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$forkedGuiDefinition->composeGuiPropAssemblies($guiPropAssemblies, $currentEiPropPaths);
			}
		}
	}
	
// 	function createDefaultDisplayStructure(EiGui $eiGui) {
// 		$displayStructure = new DisplayStructure();
// 		$this->composeDisplayStructure($displayStructure, array(), new Eiu($eiGui));
// 		return $displayStructure;
// 	}
	

	
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return \rocket\ei\manage\gui\GuiProp
	 * @throws GuiException
	 */
	function getGuiPropByGuiFieldPath(GuiFieldPath $guiFieldPath) {
		$ids = $guiFieldPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->getGuiProp($id);
			}
			
			$guiDefinition = $guiDefinition->getGuiPropFork($id)->getForkedGuiDefinition();
			if ($guiDefinition === null) {
				break;
			}
		}	
		
		throw new GuiException('GuiFieldPath could not be resolved: ' . $guiFieldPath);
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return boolean
	 */
	function containsGuiProp(GuiFieldPath $guiFieldPath) {
		$eiPropPaths = $guiFieldPath->toArray();
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
	 * @param EiEntry $eiEntry
	 * @param GuiFieldPath $guiFieldPath
	 * @throws UnknownEiFieldExcpetion
	 * @return \rocket\ei\manage\gui\EiFieldAbstraction|null
	 */
	function determineEiFieldAbstraction(N2nContext $n2nContext, EiEntry $eiEntry, GuiFieldPath $guiFieldPath) {
		$eiFieldPaths = $guiFieldPath->toArray();
		$id = array_shift($eiFieldPaths);
		if (empty($eiFieldPaths)) {
			return $eiEntry->getEiFieldWrapper($id);
		}
		
		$guiPropFork = $this->getGuiPropFork($id);
		return $guiPropFork->determineEiFieldAbstraction(new Eiu($n2nContext, $eiEntry), new GuiFieldPath($eiFieldPaths));
	}
	
	/**
	 * @return GuiPropFork[]
	 */
	function getGuiPropForks() {
		return $this->guiPropForks;
	}
		
	/**
	 * @param string $id
	 * @param GuiProp $guiProp
	 * @param EiPropPath $guiFieldPath
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
			
			if (isset($this->guiProps[$eiPropPathStr])) {
				$currentEiPropPaths = $contextEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$labelLstrs[(string) new GuiFieldPath($currentEiPropPaths)] = $this->guiProps[$eiPropPathStr]->getLabelLstr();
			}
			
			if (isset($this->guiPropForks[$eiPropPathStr])) {
				$currentEiPropPaths = $contextEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				
				$guiFieldPaths = array_merge($labelLstrs, $this->guiPropForks[$eiPropPathStr]
						->getForkedGuiDefinition()->buildLabelLstrs($currentEiPropPaths));
			}
		}
		
		return $guiFieldPaths;
	}
	
	function createEiGui(EiFrame $eiFrame) {
		ArgUtils::assertTrue($this->eiMask->isA($eiFrame->getContextEiEngine()->getEiMask()));
		
		$eiGui = new EiGui($eiFrame, $this->eiMask, $viewMode);
	}
	
	
	public function initEiGui(EiGui $eiGui, GuiDefinition $guiDefinition) {
		$displayStructure = null;
		switch ($eiGui->getViewMode()) {
			case ViewMode::BULKY_READ:
				$displayStructure = $this->getDetailDisplayStructure() ?? $this->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_EDIT:
				$displayStructure = $this->getEditDisplayStructure() ?? $this->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_ADD:
				$displayStructure = $this->getAddDisplayStructure() ?? $this->getBulkyDisplayStructure();
				break;
			case ViewMode::COMPACT_READ:
			case ViewMode::COMPACT_EDIT:
			case ViewMode::COMPACT_ADD:
				$displayStructure = $this->getOverviewDisplayStructure();
				break;
		}
		
		$commonEiGuiSiFactory = new CommonEiGuiSiFactory($eiGui);
		
		if ($displayStructure === null) {
			$eiGui->init($commonEiGuiSiFactory);
			
			$displayStructure = DisplayStructure::fromEiGui($eiGui);
		} else {
			$eiGui->init($commonEiGuiSiFactory,
					$guiDefinition->filterGuiFieldPaths($displayStructure->getAllGuiFieldPaths()));
			$displayStructure = $displayStructure->purified($eiGui);
		}
		
		$commonEiGuiSiFactory->setDisplayStructure($displayStructure->groupedItems());
	}
	
	/**
	 * @param string $siStructureType
	 */
	function dingselAutoGuiStructureDeclarations($siStructureType) {
		$guiStructureDeclarations = [];
		foreach ($this->getGuiFieldPaths() as $guiFieldPath) {
			
			
			$guiStructureDeclarations[] = GuiStructureDeclaration::createField($guiFieldPath, $siStructureType, $label)
		}
	}
	
	/**
	 * @param DisplayStructure $displayStructure 
	 */
	function dingselDisplayStructure($displayStructure) {
		$guiStructureDeclarations = [];
		
		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			if ($displayItem->hasDisplayStructure()) {
				$this->dingselDisplayStructure($displayItem->getDisplayStructure());
				continue;
			}
			
			if (!$this->containsGuiProp($displayItem->getGuiFieldPath())) {
				continue;
			}
			
			GuiStructureDeclaration::createField($displayItem->getGuiFieldPath(), $displayItem->getSiStructureType(), 
					$displayItem->getLabel());
		}
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @param DisplayItem|null $displayItem
	 * @return GuiStructureDeclaration
	 */
	private function createFieldDeclaration($guiFieldPath, $eiProp, $displayItem) {
		$guiProp = $this->getGuiProp($eiPropPath);
	}
	
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