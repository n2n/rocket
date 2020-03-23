<?php
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\field\GuiPropPath;
use n2n\util\ex\IllegalStateException;
use rocket\si\meta\SiDeclaration;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\security\InaccessibleEiEntryException;
use rocket\ei\manage\entry\EiEntry;
use rocket\si\content\SiEntry;
use rocket\ei\EiType;

class EiGui {
	/**
	 * @var EiMask
	 */
	private $contextEiMask;
	/**
	 * @var int
	 */
	private $viewMode;
	/**
	 * @var EiGuiFrame
	 */
	private $eiGuiFrames = [];
	/**
	 * @var EiEntryGui[]
	 */
	private $eiEntryGuis = [];
	
	/**
	 * @param EiMask $eiMask
	 * @param EiGuiFrame $eiGuiFrame
	 */
	function __construct(EiMask $contextEiMask, int $viewMode) {
		$this->contextEiMask = $contextEiMask;
		ArgUtils::valEnum($viewMode, ViewMode::getAll());
		$this->viewMode = $viewMode;
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	function getContextEiMask() {
		return $this->contextEiMask;
	}
	
	/**
	 * @return int
	 */
	function getViewMode() {
		return $this->viewMode;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	function getEiGuiFrames() {
		return $this->eiGuiFrames;
	}
	
	/**
	 * @return boolean
	 */
	function hasEiGuiFrames() {
		return !empty($this->eiGuiFrames);
	}
	
	/**
	 * @return EiType[]
	 */
	function getEiTypes() {
		return array_map(
				function ($arg) { return $arg->getGuiDefinition()->getEiMask()->getEiType(); }, 
				$this->eiGuiFrames);
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	function putEiGuiFrame(EiGuiFrame $eiGuiFrame) {
		$eiType = $eiGuiFrame->getGuiDefinition()->getEiMask()->getEiType();
		
		ArgUtils::assertTrue($eiType->isA($eiGuiFrame->getGuiDefinition()->getEiMask()->getEiType()));
		
		$this->eiGuiFrames[$eiType->getId()] = $eiGuiFrame;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	function hasMultipleEiEntryGuis() {
		return count($this->eiEntryGuis) > 1;
	}
	
	/**
	 * @return boolean
	 */
	function hasSingleEiEntryGui() {
		return count($this->eiEntryGuis) === 1;
	}
	
	function isEmpty() {
		return empty($this->eiEntryGuis);
	}
	
	/**
	 * @return GuiPropPath[]
	 */
	function getGuiPropPaths() {
		$this->ensureInit();
		
		$guiPropPaths = [];
		foreach ($this->guiStructureDeclarations as $guiStructureDeclaration) {
			$guiPropPaths = array_merge($guiPropPaths, $guiStructureDeclaration->getAllGuiPropPaths());
		}
		return $guiPropPaths;
	}
	
	/**
	 * @return \rocket\si\meta\SiDeclaration
	 */
	function createSiDeclaration(EiFrame $eiFrame) {
		$n2nLocale = $eiFrame->getN2nContext()->getN2nLocale();
		$siDeclaration = new SiDeclaration();
		
		foreach ($this->eiGuiFrames as $eiGuiFrame) {
			$siDeclaration->addTypeDeclaration($eiGuiFrame->createSiTypeDeclaration($n2nLocale));
		}
		
		return $siDeclaration;
	}
	
	
// 	/**
// 	 * @return SiProp[]
// 	 */
// 	private function getSiProps() {
// 		IllegalStateException::assertTrue($this->guiStructureDeclarations !== null,
// 				'EiGuiFrame is forked.');
		
		
// 		$deter = new ContextSiFieldDeterminer();
		
// 		$siProps = [];
// 		foreach ($this->filterFieldGuiStructureDeclarations($this->guiStructureDeclarations) 
// 				as $guiStructureDeclaration) {
// 			$guiPropPath = $guiStructureDeclaration->getGuiPropPath();
			
// 			$siProps[] = $this->createSiProp($guiStructureDeclaration);
			
// 			$deter->reportGuiPropPath($guiPropPath);
// 		}
		
// 		return array_merge($deter->createContextSiProps($this->eiGuiFrame), $siProps);
// 	}
	
	
	
	
	
// 	/**
// 	 * @param GuiStructureDeclaration $guiStructureDeclaration
// 	 * @return SiProp
// 	 */
// 	private function createSiProp(GuiStructureDeclaration $guiStructureDeclaration) {
// 		return new SiProp($guiStructureDeclaration->getGuiPropPath(),
// 				$guiStructureDeclaration->getLabel(), $guiStructureDeclaration->getHelpText());
// 	}
	
	
	
	/**
	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
	 * @return GuiStructureDeclaration[]
	 */
	private function filterFieldGuiStructureDeclarations($guiStructureDeclarations) {
		$filtereds = [];
		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
			if ($guiStructureDeclaration->hasGuiPropPath()) {
				$filtereds[] = $guiStructureDeclaration;
				continue;
			}
			
			array_push($filtereds, ...$this->filterFieldGuiStructureDeclarations(
					$guiStructureDeclaration->getChildren()));
		}
		return $filtereds;
	}
	
	function appendEiEntryGui(EiFrame $eiFrame, array $eiEntries, int $treeLevel = null) {
		ArgUtils::valArray($eiEntries, EiEntry::class);
		
		$eiEntryGui = new EiEntryGui($this->contextEiMask->getEiType(), $treeLevel);
		
		$eiEntries = $this->catEiEntries($eiEntries);
		
		foreach ($this->eiGuiFrames as $eiGuiFrame) {
			$eiType = $eiGuiFrame->getEiType();
			
			if (isset($eiEntries[$eiType->getId()])) {
				$eiGuiFrame->applyEiEntryGuiTypeDef($eiFrame, $eiEntryGui, $eiEntries[$eiType->getId()]);
				continue;
			}
			
			throw new \InvalidArgumentException('No EiEntry provieded for ' . $eiType);
		}
	
		$this->finalizeEiEntryGui($eiEntryGui);
		
		return $eiEntryGui;
	}
	
	/**
	 * @param EiEntry[] $eiEntries
	 * @return EiEntry[]
	 */
	private function catEiEntries($eiEntries) {
		$catEiEntries = [];
		foreach ($eiEntries as $eiEntry) {
			$catEiEntries[$eiEntry->getEiMask()->getEiType()->getId()] = $eiEntry;
		}
		return $catEiEntries;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param int $treeLevel
	 * @throws InaccessibleEiEntryException
	 */
	function appendNewEiEntryGui(EiFrame $eiFrame, int $treeLevel = null) {
		$eiEntryGui = new EiEntryGui($this->contextEiMask->getEiType(), $treeLevel);
		
		foreach ($this->eiGuiFrames as $eiGuiFrame) {
			$eiGuiFrame->applyNewEiEntryGuiTypeDef($eiFrame, $eiEntryGui);
		}
		
		$this->finalizeEiEntryGui($eiEntryGui);
		
		return $eiEntryGui;
	}
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 */
	private function finalizeEiEntryGui($eiEntryGui) {
		if ($this->hasSingleEiGuiFrame()) {
			$eiEntryGui->selectedTypeDef(key($this->eiGuiFrames));
		}
		
		$this->eiEntryGuis[] = $eiEntryGui;
	}
	
	/**
	 * @return boolean
	 */
	function hasSingleEiGuiFrame() {
		return count($this->eiGuiFrames) > 1;
	}
	
	/**
	 * @return boolean
	 */
	function hasMulitpleEiGuiFrames() {
		return count($this->eiGuiFrames) > 1;
	}
	
// 	/**
// 	 * @param EiEntryGui $eiEntryGui
// 	 */
// 	function addEiEntryGui(EiEntryGui $eiEntryGui) {
// 		$this->eiEntryGuis[] = $eiEntryGui;
// 	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiEntryGui[]
	 */
	function getEiEntryGuis() {
		return $this->eiEntryGuis;
	}
	
	/**
	 * @param bool $siControlsIncluded
	 * @throws IllegalStateException
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry(EiFrame $eiFrame, bool $siControlsIncluded = true) {
		if ($this->hasSingleEiEntryGui()) {
			return $this->assemblySiEntry($eiFrame, current($this->eiEntryGuis), $siControlsIncluded);
		}
		
		throw new IllegalStateException('EiGui has none or multiple EiEntryGuis');
	}
		
	/**
	 * @return \rocket\si\content\SiEntry[]
	 */
	function createSiEntries(EiFrame $eiFrame, bool $siControlsIncluded = true) {
		$siEntries = [];
		foreach ($this->eiEntryGuis as $eiEntryGui) {
			$siEntries[] = $this->assemblySiEntry($eiFrame, $eiEntryGui, $siControlsIncluded);
		}
		return $siEntries;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntryGui $eiEntryGui
	 * @param bool $siControlsIncluded
	 * @return \rocket\si\content\SiEntry
	 */
	private function assemblySiEntry($eiFrame, $eiEntryGui, $siControlsIncluded = true) {
		$siEntry = new SiEntry($eiEntryGui->createSiEntryIdentifier(), ViewMode::isReadOnly($this->viewMode), 
				ViewMode::isBulky($this->viewMode));
		
		$typeDefs = $eiEntryGui->getTypeDefs();
		
		foreach ($this->eiGuiFrames as $key => $eiGuiFrame) {
			IllegalStateException::assertTrue(isset($typeDefs[$key]));
			$eiEntryGuiTypeDef = $typeDefs[$key];	
			
			$siEntry->putBuildup($eiEntryGuiTypeDef->getEiType()->getId(),
					$eiGuiFrame->createSiEntryBuildup($eiFrame, $eiEntryGuiTypeDef, $siControlsIncluded));
		}
		
		if ($eiEntryGui->isTypeDefSelected()) {
			$siEntry->setSelectedTypeId($eiEntryGui->getSelectedTypeDef()->getEiType()->getId());
		}
		
		return $siEntry;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @return \rocket\si\control\SiControl[]
	 */
	function createGeneralSiControls(EiFrame $eiFrame) {
		$contextEiTypeId = $this->contextEiMask->getEiType()->getId();
		
		if (isset($this->eiGuiFrames[$contextEiTypeId])) {
			return $this->eiGuiFrames[$contextEiTypeId]->createGeneralSiControls($eiFrame);
		}
		
		return [];
	}
}


