<?php
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\si\meta\SiProp;
use rocket\si\meta\SiStructureDeclaration;
use rocket\si\meta\SiType;
use rocket\si\meta\SiTypeDeclaration;
use rocket\ei\manage\gui\field\GuiFieldPath;
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
	 * @return GuiFieldPath[]
	 */
	public function getGuiFieldPaths() {
		$this->ensureInit();
		
		$guiFieldPaths = [];
		foreach ($this->guiStructureDeclarations as $guiStructureDeclaration) {
			$guiFieldPaths = array_merge($guiFieldPaths, $guiStructureDeclaration->getAllGuiFieldPaths());
		}
		return $guiFieldPaths;
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
}