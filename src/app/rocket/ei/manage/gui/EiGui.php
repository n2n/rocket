<?php
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\si\meta\SiStructureDeclaration;
use rocket\ei\manage\gui\field\GuiPropPath;
use n2n\util\ex\IllegalStateException;
use rocket\si\meta\SiDeclaration;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\security\InaccessibleEiEntryException;
use rocket\ei\manage\entry\EiEntry;

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
	
	
	
	/**
	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
	 * @return SiStructureDeclaration[]
	 */
	private function createSiStructureDeclarations($guiStructureDeclarations) {
		$siStructureDeclarations = [];
		
		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
			if ($guiStructureDeclaration->hasGuiPropPath()) {
				$siStructureDeclarations[] = SiStructureDeclaration::createProp(
						$guiStructureDeclaration->getSiStructureType(),
						$guiStructureDeclaration->getGuiPropPath());
				continue;
			}
			
			$siStructureDeclarations[] = SiStructureDeclaration
					::createGroup($guiStructureDeclaration->getSiStructureType(), $guiStructureDeclaration->getLabel(), 
							$guiStructureDeclaration->getHelpText())
					->setChildren($this->createSiStructureDeclarations($guiStructureDeclaration->getChildren()));
		}
		
		return $siStructureDeclarations;
	}
	
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
	
	function appendEiEntryGui(EiFrame $eiFrame, EiEntry $eiEntry, int $treeLevel = null) {
		$eiEntryGui = new EiEntryGui($this->contextEiMask->getEiType(), $treeLevel);
		
		foreach ($this->eiGuiFrames as $eiGuiFrame) {
			$eiGuiFrame->applyEiEntryGuiTypeDef($eiFrame, $eiEntryGui, $eiEntry);
		}
	
		$this->eiEntryGuis[] = $eiEntryGui;
		
		return $eiEntryGui;
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
		
		$this->eiEntryGuis[] = $eiEntryGui;
		
		return $eiEntryGui;
	}
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 */
	function addEiEntryGui(EiEntryGui $eiEntryGui) {
		$this->eiEntryGuis[] = $eiEntryGui;
	}
	
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
			return $this->eiGuiFrame->createSiEntry($eiFrame, current($this->eiEntryGuis), $siControlsIncluded);
		}
		
		throw new IllegalStateException('EiGui has none or multiple EiEntryGuis');
	}
		
	/**
	 * @return \rocket\si\content\SiEntry[]
	 */
	function createSiEntries(EiFrame $eiFrame, bool $siControlsIncluded = true) {
		$siEntries = [];
		foreach ($this->eiEntryGuis as $eiEntryGui) {
			$siEntries[] = $this->eiGuiFrame->createSiEntry($eiFrame, $eiEntryGui, $siControlsIncluded);
		}
		return $siEntries;
	}
}


