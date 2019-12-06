<?php
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\si\meta\SiProp;
use rocket\si\meta\SiStructureDeclaration;
use rocket\si\meta\SiType;
use rocket\si\meta\SiTypeDeclaration;
use rocket\ei\manage\gui\field\GuiPropPath;
use n2n\util\ex\IllegalStateException;
use rocket\si\meta\SiDeclaration;

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
	public function createSiDeclaration() {
		return new SiDeclaration([$this->createSiTypDeclaration()]);
	}
	
	/**
	 * @return SiProp[]
	 */
	private function getSiProps() {
		IllegalStateException::assertTrue($this->guiStructureDeclarations !== null,
				'EiGuiFrame is forked.');
		
		
		$deter = new ContextSiFieldDeterminer();
		
		$siProps = [];
		foreach ($this->filterFieldGuiStructureDeclarations($this->guiStructureDeclarations) 
				as $guiStructureDeclaration) {
			$guiPropPath = $guiStructureDeclaration->getGuiPropPath();
			
			$siProps[] = $this->createSiProp($guiStructureDeclaration);
			
			$deter->reportGuiPropPath($guiPropPath);
		}
		
		return array_merge($deter->createContextSiProps($this->eiGuiFrame), $siProps);
	}
	
	/**
	 * @return \rocket\si\meta\SiTypeDeclaration
	 */
	function createSiTypDeclaration() {
		$siTypeQualifier = $this->eiGuiFrame->getGuiDefinition()->getEiMask()
				->createSiTypeQualifier($this->eiGuiFrame->getEiFrame()->getN2nContext()->getN2nLocale());
		$siType = new SiType($siTypeQualifier, $this->getSiProps());
		
		return new SiTypeDeclaration($siType, $this->createSiStructureDeclarations($this->guiStructureDeclarations));
	}
	
	/**
	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
	 * @return SiStructureDeclaration[]
	 */
	private function createSiStructureDeclarations($guiStructureDeclarations) {
		$siStructureDeclarations = [];
		
		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
			if ($guiStructureDeclaration->hasGuiPropPath()) {
				$siStructureDeclarations[] = new SiStructureDeclaration($guiStructureDeclaration->getSiStructureType(),
						$guiStructureDeclaration->getGuiPropPath(), $guiStructureDeclaration->getLabel(),
						$guiStructureDeclaration->getHelpText());
				continue;
			}
			
			$siStructureDeclarations[] = new SiStructureDeclaration($guiStructureDeclaration->getSiStructureType(),
					null, $guiStructureDeclaration->getLabel(), $guiStructureDeclaration->getHelpText(),
					$this->createSiStructureDeclarations($guiStructureDeclaration->getChildren()));
		}
		
		return $siStructureDeclarations;
	}
	
	/**
	 * @param GuiStructureDeclaration $guiStructureDeclaration
	 * @return SiProp
	 */
	private function createSiProp(GuiStructureDeclaration $guiStructureDeclaration) {
		return new SiProp($guiStructureDeclaration->getGuiPropPath(),
				$guiStructureDeclaration->getLabel(), $guiStructureDeclaration->getHelpText());
	}
	
	
	
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
	function createSiEntry(bool $siControlsIncluded = true) {
		if ($this->hasSingleEiEntryGui()) {
			return $this->eiGuiFrame->createSiEntry(current($this->eiEntryGuis), $siControlsIncluded);
		}
		
		throw new IllegalStateException('EiGui has none or multiple EiEntryGuis');
	}
		
	/**
	 * @return \rocket\si\content\SiEntry[]
	 */
	function createSiEntries(bool $siControlsIncluded = true) {
		$siEntries = [];
		foreach ($this->eiEntryGuis as $eiEntryGui) {
			$siEntries[] = $this->eiGuiFrame->createSiEntry($eiEntryGui, $siControlsIncluded);
		}
		return $siEntries;
	}
}

class ContextSiFieldDeterminer {
	private $guiPropPaths = [];
	private $forkGuiPropPaths = [];
	private $forkedGuiPropPaths = []; 
	
	/**
	 * @param GuiPropPath $guiPropPath
	 */
	function reportGuiPropPath(GuiPropPath $guiPropPath) {
		$guiPropPathStr = (string) $guiPropPath;
		
		$this->guiPropPaths[$guiPropPathStr] = $guiPropPath;
		unset($this->forkGuiPropPaths[$guiPropPathStr]);
		unset($this->forkedGuiPropPaths[$guiPropPathStr]);
		
		$forkGuiPropPath = $guiPropPath;
		while ($forkGuiPropPath->hasMultipleEiPropPaths()) {
			$forkGuiPropPath = $forkGuiPropPath->getPoped();
			$this->reportFork($forkGuiPropPath, $guiPropPath);
		}
	}
	
	/**
	 * @param GuiPropPath $forkGuiPropPath
	 * @param GuiPropPath $guiPropPath
	 */
	private function reportFork(GuiPropPath $forkGuiPropPath, GuiPropPath $guiPropPath) {
		$forkGuiPropPathStr = (string) $forkGuiPropPath;
		
		if (isset($this->guiPropPaths[$forkGuiPropPathStr])) {
			return;
		}
		
		if (!isset($this->forkGuiPropPaths[$forkGuiPropPathStr])) {
			$this->forkGuiPropPaths[$forkGuiPropPathStr] = [];
		}
		$this->forkedGuiPropPaths[$forkGuiPropPathStr][] = $guiPropPath;
		$this->forkGuiPropPaths[$forkGuiPropPathStr] = $forkGuiPropPath;
		
		if ($forkGuiPropPath->hasMultipleEiPropPaths()) {
			$this->reportFork($forkGuiPropPath->getPoped(), $forkGuiPropPath);
		}
	}
	
	/**
	 * @return SiProp[]
	 */
	function createContextSiProps(EiGuiFrame $eiGuiFrame) {
		$n2nLocale = $eiGuiFrame->getEiFrame()->getN2nContext()->getN2nLocale();
		
		$siProps = [];
		
		foreach ($this->forkGuiPropPaths as $forkGuiPropPath) {
			$eiProp = $eiGuiFrame->getGuiDefinition()->getGuiPropWrapperByGuiPropPath($forkGuiPropPath)->getEiProp();
			
			$siProp = (new SiProp((string) $forkGuiPropPath, $eiProp->getLabelLstr()->t($n2nLocale)))
					->setDescendantPropIds(array_map(
							function ($guiPropPath) { return (string) $guiPropPath; }, 
							$this->forkedGuiPropPaths[(string) $forkGuiPropPath]));
					
			if (null !== ($helpTextLstr = $eiProp->getHelpTextLstr())) {
				$siProp->setHelpText($helpTextLstr);	
			}
			
			$siProps[] = $siProp;
		}
		
		return $siProps;
	}
}
