<?php
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\si\meta\SiStructureDeclaration;
use rocket\si\meta\SiTypeDeclaration;
use rocket\ei\manage\gui\field\GuiPropPath;
use n2n\util\ex\IllegalStateException;
use rocket\si\meta\SiDeclaration;
use rocket\ei\manage\frame\EiFrame;
use rocket\si\meta\SiType;

class EiGui {
	
	/**
	 * @var GuiStructureDeclaration[]
	 */
	private $guiStructureDeclarations;
	/**
	 * @var EiGuiFrame
	 */
	private $eiGuiFrame;
	/**
	 * @var EiEntryGui[]
	 */
	private $eiEntryGuis = [];
	
	/**
	 * @param GuiStructureDeclaration[]|null $guiStructureDeclarations
	 * @param EiGuiFrame $eiGuiFrame
	 */
	function __construct(?array $guiStructureDeclarations, EiGuiFrame $eiGuiFrame) {
		ArgUtils::assertTrue($guiStructureDeclarations !== null);
		
		$this->guiStructureDeclarations = $guiStructureDeclarations;
		$this->eiGuiFrame = $eiGuiFrame;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	function getEiGuiFrame() {
		return $this->eiGuiFrame;
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
		$siDeclaration = new SiDeclaration([$this->createSiTypeDeclaration($eiFrame)], 
				$this->createSiStructureDeclarations($this->guiStructureDeclarations));
		$n2nLocale = $eiFrame->getN2nContext()->getN2nLocale();
		
		$contextEiMask = $eiFrame->getContextEiEngine()->getEiMask();
		foreach ($contextEiMask->getEiType()->getAllSubEiTypes() as $subEiType) {
			$siTypeIdentifier = $contextEiMask->determineEiMask($subEiType)->createSiTypeQualifier($n2nLocale);
			$siDeclaration->addTypeDeclaration(new SiTypeDeclaration(new SiType($siTypeIdentifier, null)));
		}
		
		return $siDeclaration;
	}
	
	/**
	 * @return \rocket\si\meta\SiTypeDeclaration
	 */
	function createSiTypeDeclaration(EiFrame $eiFrame) {
		return new SiTypeDeclaration(
				$this->eiGuiFrame->createSiType($eiFrame), 
				$this->createSiStructureDeclarations($this->guiStructureDeclarations));
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


