<?php
namespace rocket\ei\mask\model;

use rocket\ei\manage\gui\ui\DisplayStructure;
use rocket\ei\manage\gui\EiGuiGiFactory;
use rocket\ei\manage\gui\EiGui;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\GuiPropAssembly;
use rocket\gi\content\GiFieldDeclaration;
use rocket\gi\content\GiCompactContent;
use rocket\gi\content\GiFieldStructureDeclaration;
use rocket\gi\content\GiBulkyContent;
use n2n\util\ex\NotYetImplementedException;

class CommonEiGuiGiFactory implements EiGuiGiFactory {
	private $eiGui;
	private $guiDefinition;
	private $displayStructure;
	
	public function __construct(EiGui $eiGui, DisplayStructure $displayStructure = null) {
		$this->eiGui = $eiGui;
		$this->displayStructure = $displayStructure;
	}
	
	public function getDisplayStructure() {
		IllegalStateException::assertTrue($this->displayStructure !== null);
		return $this->displayStructure;
	}
	
	public function setDisplayStructure(DisplayStructure $displayStructure) {
		$this->displayStructure = $displayStructure;
	}
	
	/**
	 * @param GuiPropAssembly $guiPropAssembly
	 * @return GiFieldDeclaration
	 */
	private function createGiFieldDeclaration(GuiPropAssembly $guiPropAssembly) {
		$n2nLocale = $this->eiGui->getEiFrame()->getN2nContext()->getN2nLocale();
		
		$guiProp = $guiPropAssembly->getGuiProp();
		$label = $guiProp->getDisplayLabelLstr()->t($n2nLocale);
		$helpText = null;
		if (null !== ($helpTextLstr = $guiProp->getDisplayHelpTextLstr())) {
			$helpText = $helpTextLstr->t($n2nLocale);
		}
		
		return new GiFieldDeclaration($guiPropAssembly->getGuiFieldPath(),
				$label, $helpText);
	}
	
	/**
	 * @return GiFieldDeclaration[]
	 */
	private function createDefaultGiFieldDeclarations() {
		$giFieldDeclarations = [];
		foreach ($this->eiGui->getGuiPropAssemblies() as $guiPropAssembly) {
			$giFieldDeclarations[] = $this->createGiFieldDeclaration($guiPropAssembly); 
		}
		return $giFieldDeclarations;
	}
	
	/**
	 * @param DisplayStructure $displayStructure
	 * @return GiFieldStructureDeclaration[]
	 */
	private function createGiFieldStructureDeclarations(DisplayStructure $displayStructure) {
		$fieldDeclarationStructures = [];
		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			$guiPropAssembly = $this->eiGui->getGuiPropAssemblyByGuiFieldPath($displayItem->getGuiFieldPath());
			$fieldDeclaration = $this->createGiFieldDeclaration($guiPropAssembly); 
			
			$children = [];
			if ($displayItem->hasDisplayStructure()) {
				$children = $this->createFieldDeclarationStructures($displayItem->getDisplayStructure());
			}
			
			$fieldDeclarationStructures[] = new GiFieldStructureDeclaration(
					$displayItem->getType() ?? $guiPropAssembly->getDisplayDefinition()->getDisplayItemType(),
					$fieldDeclaration, $children);
		}
		return $fieldDeclarationStructures;
	}
	
	/**
	 * @param array $eiEntryGuis
	 * @return GiCompactContent
	 */
	public function createGiCompactContent(): GiCompactContent {
		return new GiCompactContent($this->createDefaultGiFieldDeclarations(),
				$this->eiGui->createGiEntries());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiGuiGiFactory::createBulkyContent()
	 */
	public function createGiBulkyContent(): GiBulkyContent {
		IllegalStateException::assertTrue($this->displayStructure !== null);
		
		$giBulkyContent = new GiBulkyContent($this->createFieldDeclarationStructures($this->displayStructure));
		throw new NotYetImplementedException();
		
	}
	
	
// 	private function determineDisplayStructure($viewMode): DisplayStructure {
// 		$displayStructure = null;
	
// 		if ($viewMode & DisplayConfig::COMPACT_VIEW_MODES) {
// 			if (null !== ($overviewDisplayStructure = $this->displayScheme->getOverviewDisplayStructure())) {
// 				return $overviewDisplayStructure;
// 			}
// 			return $this->createDefaultDisplayStructure($viewMode);
// 		} 
		
// 		switch ($viewMode) {
// 			case DisplayConfig::VIEW_MODE_BULKY_READ:
// 				if (null !== ($detailDisplayStructure = $this->displayScheme->getDetailDisplayStructure())) {
// 					return $detailDisplayStructure;
// 				}
// 				break;
// 			case DisplayConfig::VIEW_MODE_BULKY_EDIT:
// 				if (null !== $editDisplayStructure = $this->displayScheme->getEditDisplayStructure()) {
// 					return $editDisplayStructure;
// 				}
// 				break;
// 			case DisplayConfig::VIEW_MODE_BULKY_ADD:
// 				if (null !== ($addDisplayStructure = $this->displayScheme->getAddDisplayStructure())) {
// 					return $addDisplayStructure;
// 				}
// 				break;
// 		}
	
// 		if (null !== ($bulkyDisplayStructure = $this->displayScheme->getBulkyDisplayStructure())) {
// 			return $bulkyDisplayStructure;
// 		}
	
// 		return $this->createDefaultDisplayStructure($viewMode);
// 	}
	
	private function createDefaultDisplayStructure($viewMode) {
		$displayStructure = new DisplayStructure();
		foreach ($this->eiGui->getGuiDefinition()->filterGuiFieldPaths($viewMode) as $eiPropPath) {
			$displayStructure->addGuiFieldPath($eiPropPath);
		}
		return $displayStructure;
	}

	
	
// 	public function createUiComponent(array $eiEntryGuis, ?HtmlView $contextView): UiComponent {
// 		$viewFactory = $this->eiGui->getEiFrame()->getN2nContext()->lookup(ViewFactory::class);
// 		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
// 		$displayStructure = $this->displayStructure;
// 		$viewName = null;
		
// 		if ($this->eiGui->getViewMode() & ViewMode::bulky()) {
// 			$viewName = 'rocket\ei\mask\view\bulky.html';
// 		} else {
// 			$viewName = 'rocket\ei\mask\view\compact.html';
// 			$displayStructure = $displayStructure->withoutSubStructures();
// 		}
		
// 		$params = array('displayStructure' => $displayStructure, 'eiu' => new Eiu($this->eiGui));
		
// 		if ($contextView !== null) {
// 			return $contextView->getImport('\\' . $viewName, $params);
// 		} 
		
// 		return $viewFactory->create($viewName, $params);
// 	}
}