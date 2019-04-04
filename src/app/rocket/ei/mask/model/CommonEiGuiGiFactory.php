<?php
namespace rocket\ei\mask\model;

use rocket\ei\manage\gui\ui\DisplayStructure;
use rocket\ei\manage\gui\EiGuiGiFactory;
use rocket\ei\manage\gui\EiGui;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\GuiPropAssembly;
use rocket\si\content\SiFieldDeclaration;
use rocket\si\content\SiCompactContent;
use rocket\si\content\SiFieldStructureDeclaration;
use rocket\si\content\SiBulkyContent;
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
	 * @return SiFieldDeclaration
	 */
	private function createSiFieldDeclaration(GuiPropAssembly $guiPropAssembly) {
		$n2nLocale = $this->eiGui->getEiFrame()->getN2nContext()->getN2nLocale();
		
		$guiProp = $guiPropAssembly->getGuiProp();
		$label = $guiProp->getDisplayLabelLstr()->t($n2nLocale);
		$helpText = null;
		if (null !== ($helpTextLstr = $guiProp->getDisplayHelpTextLstr())) {
			$helpText = $helpTextLstr->t($n2nLocale);
		}
		
		return new SiFieldDeclaration($guiPropAssembly->getGuiFieldPath(),
				$label, $helpText);
	}
	
	/**
	 * @return SiFieldDeclaration[]
	 */
	private function createDefaultSiFieldDeclarations() {
		$siFieldDeclarations = [];
		foreach ($this->eiGui->getGuiPropAssemblies() as $guiPropAssembly) {
			$siFieldDeclarations[] = $this->createSiFieldDeclaration($guiPropAssembly); 
		}
		return $siFieldDeclarations;
	}
	
	/**
	 * @param DisplayStructure $displayStructure
	 * @return SiFieldStructureDeclaration[]
	 */
	private function createSiFieldStructureDeclarations(DisplayStructure $displayStructure) {
		$fieldDeclarationStructures = [];
		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			$guiPropAssembly = $this->eiGui->getGuiPropAssemblyByGuiFieldPath($displayItem->getGuiFieldPath());
			$fieldDeclaration = $this->createSiFieldDeclaration($guiPropAssembly); 
			
			$children = [];
			if ($displayItem->hasDisplayStructure()) {
				$children = $this->createFieldDeclarationStructures($displayItem->getDisplayStructure());
			}
			
			$fieldDeclarationStructures[] = new SiFieldStructureDeclaration(
					$displayItem->getType() ?? $guiPropAssembly->getDisplayDefinition()->getDisplayItemType(),
					$fieldDeclaration, $children);
		}
		return $fieldDeclarationStructures;
	}
	
	/**
	 * @param array $eiEntryGuis
	 * @return SiCompactContent
	 */
	public function createSiCompactContent(): SiCompactContent {
		return new SiCompactContent($this->createDefaultSiFieldDeclarations(),
				$this->eiGui->createGiEntries());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiGuiGiFactory::createBulkyContent()
	 */
	public function createSiBulkyContent(): SiBulkyContent {
		IllegalStateException::assertTrue($this->displayStructure !== null);
		
		$siBulkyContent = new SiBulkyContent($this->createFieldDeclarationStructures($this->displayStructure));
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