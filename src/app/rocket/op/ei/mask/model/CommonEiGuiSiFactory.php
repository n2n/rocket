<?php
namespace rocket\op\ei\mask\model;

use rocket\op\ei\manage\gui\EiGuiSiFactory;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use n2n\util\ex\IllegalStateException;
use rocket\si\meta\SiProp;
use rocket\si\meta\SiStructureDeclaration;
use rocket\op\ei\manage\gui\ViewMode;

class CommonEiGuiSiFactory implements EiGuiSiFactory {
	private $eiGuiMaskDeclaration;
	private $guiDefinition;
	private $displayStructure;
	
	public function __construct(EiGuiMaskDeclaration $eiGuiMaskDeclaration, DisplayStructure $displayStructure = null) {
		$this->eiGuiMaskDeclaration = $eiGuiMaskDeclaration;
		$this->displayStructure = $displayStructure;
	}
	
	public function getDisplayStructure() {
		IllegalStateException::assertTrue($this->displayStructure !== null);
		return $this->displayStructure;
	}
	
	public function setDisplayStructure(DisplayStructure $displayStructure) {
		$this->displayStructure = $displayStructure;
	}
	
	
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\op\ei\manage\gui\EiGuiSiFactory::getSiStructureDeclarations()
// 	 */
// 	private function createFieldStructureDeclarations(DisplayStructure $displayStructure) {
// 		$fieldStructureDeclarations = [];
// 		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			
// 			$propId = null;
// 			$label = null;
// 			$children = [];
// 			if (!$displayItem->hasDisplayStructure()) {
// 				$guiPropAssembly = $this->eiGuiMaskDeclaration->getGuiPropAssemblyByDefPropPath($displayItem->getDefPropPath());
// 				$propId = (string) $guiPropAssembly->getDefPropPath();
// 			} else {
// 				if (null !== ($labelLstr = $displayItem->getLabelLstr())) {
// 					$label = $labelLstr->t($this->eiGuiMaskDeclaration->getEiFrame()->getN2nContext()->getN2nLocale());
// 				}
// 				$children = $this->createFieldStructureDeclarations($displayItem->getDisplayStructure());
// 			}
			
// 			$fieldStructureDeclarations[] = new SiStructureDeclaration(
// 					$displayItem->getSiStructureType() ?? $guiPropAssembly->getDisplayDefinition()->getSiStructureType(),
// 					$propId, $label, $children);
// 		}
// 		return $fieldStructureDeclarations;
// 	}
		
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\op\ei\manage\gui\EiGuiSiFactory::getFieldDeclarationStrutures()
// 	 */
// 	function getSiStructureDeclarations(): array {
// 		if (ViewMode::isCompact($this->eiGuiMaskDeclaration->getViewMode())) {
// 			return [];
// 		}
		
// 		IllegalStateException::assertTrue($this->displayStructure !== null);
// 		return $this->createFieldStructureDeclarations($this->displayStructure);
// 	}

	
	
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
	
// 	private function createDefaultDisplayStructure($viewMode) {
// 		$displayStructure = new DisplayStructure();
// 		foreach ($this->eiGuiMaskDeclaration->getGuiDefinition()->filterDefPropPaths($viewMode) as $eiPropPath) {
// 			$displayStructure->addDefPropPath($eiPropPath);
// 		}
// 		return $displayStructure;
// 	}

	
	
// 	public function createUiComponent(array $eiGuiValueBoundaries, ?HtmlView $contextView): UiComponent {
// 		$viewFactory = $this->eiGuiMaskDeclaration->getEiFrame()->getN2nContext()->lookup(ViewFactory::class);
// 		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
// 		$displayStructure = $this->displayStructure;
// 		$viewName = null;
		
// 		if ($this->eiGuiMaskDeclaration->getViewMode() & ViewMode::bulky()) {
// 			$viewName = 'rocket\op\ei\mask\view\bulky.html';
// 		} else {
// 			$viewName = 'rocket\op\ei\mask\view\compact.html';
// 			$displayStructure = $displayStructure->withoutSubStructures();
// 		}
		
// 		$params = array('displayStructure' => $displayStructure, 'eiu' => new Eiu($this->eiGuiMaskDeclaration));
		
// 		if ($contextView !== null) {
// 			return $contextView->getImport('\\' . $viewName, $params);
// 		} 
		
// 		return $viewFactory->create($viewName, $params);
// 	}
}