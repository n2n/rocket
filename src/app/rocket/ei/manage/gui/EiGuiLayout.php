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

class EiGuiLayout {
	
	/**
	 * @var GuiStructureDeclaration[]
	 */
	private $guiStructureDeclarations;
	/**
	 * @var EiGui
	 */
	private $eiGui;
	/**
	 * @var EiEntryGui[]
	 */
	private $eiEntryGuis = [];
	
	/**
	 * @param GuiStructureDeclaration[]|null $guiStructureDeclarations
	 * @param EiGui $eiGui
	 */
	function __construct(?array $guiStructureDeclarations, EiGui $eiGui) {
		ArgUtils::assertTrue($guiStructureDeclarations !== null);
		
		$this->guiStructureDeclarations = $guiStructureDeclarations;
		$this->eiGui = $eiGui;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	function getEiGui() {
		return $this->eiGui;
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
	public function getGuiPropPaths() {
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
				'EiGui is forked.');
		
		$siProps = [];
		foreach ($this->filterFieldGuiStructureDeclarations($this->guiStructureDeclarations)
				as $guiStructureDeclaration) {
			$siProps[] = $this->createSiProp($guiStructureDeclaration);
		}
		return $siProps;
	}
	
	/**
	 * @return \rocket\si\meta\SiTypeDeclaration
	 */
	function createSiTypDeclaration() {
		$siTypeQualifier = $this->eiGui->getGuiDefinition()->getEiMask()
				->createSiTypeQualifier($this->eiGui->getEiFrame()->getN2nContext()->getN2nLocale());
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
	
	function createSiEntry(bool $siControlsIncluded = true) {
		if ($this->hasSingleEiEntryGui()) {
			return $this->eiGui->createSiEntry(current($this->eiEntryGuis), $siControlsIncluded);
		}
		
		throw new IllegalStateException('EiGuiLayout has none or multiple EiEntryGuis');
	}
		
	/**
	 * @return \rocket\si\content\SiEntry[]
	 */
	function createSiEntries(bool $siControlsIncluded = true) {
		$siEntries = [];
		foreach ($this->eiEntryGuis as $eiEntryGui) {
			$siEntries[] = $this->eiGui->createSiEntry($eiEntryGui, $siControlsIncluded);
		}
		return $siEntries;
	}
}