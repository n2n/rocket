<?php
namespace rocket\ei\mask\model;

use rocket\ei\manage\gui\ui\DisplayStructure;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\EiGuiViewFactory;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\ViewFactory;
use n2n\util\type\CastUtils;
use rocket\ei\util\Eiu;
use n2n\web\ui\UiComponent;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\ViewMode;
use n2n\util\ex\IllegalStateException;

class CommonEiGuiViewFactory implements EiGuiViewFactory {
	private $eiGui;
	private $guiDefinition;
	private $displayStructure;
	
	public function __construct(EiGui $eiGui, GuiDefinition $guiDefinition, DisplayStructure $displayStructure = null) {
		$this->eiGui = $eiGui;
		$this->guiDefinition = $guiDefinition;
		$this->displayStructure = $displayStructure;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiGuiViewFactory::getGuiDefinition()
	 */
	public function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiGuiViewFactory::getGuiFieldPaths()
	 */
	public function getGuiFieldPaths(): array {
		return $this->displayStructure->getAllGuiFieldPaths();
	}
	
	
	public function getDisplayStructure() {
		IllegalStateException::assertTrue($this->displayStructure !== null);
		return $this->displayStructure;
	}
	
	public function setDisplayStructure(DisplayStructure $displayStructure) {
		$this->displayStructure = $displayStructure;
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
	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiGuiViewFactory::createUiComponent()
	 */
	public function createUiComponent(array $eiEntryGuis, ?HtmlView $contextView): UiComponent {
		$viewFactory = $this->eiGui->getEiFrame()->getN2nContext()->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		$displayStructure = $this->displayStructure;
		$viewName = null;
		
		if ($this->eiGui->getViewMode() & ViewMode::bulky()) {
			$viewName = 'rocket\ei\mask\view\bulky.html';
		} else {
			$viewName = 'rocket\ei\mask\view\compact.html';
			$displayStructure = $displayStructure->withoutSubStructures();
		}
		
		$params = array('displayStructure' => $displayStructure, 'eiu' => new Eiu($this->eiGui));
		
		if ($contextView !== null) {
			return $contextView->getImport('\\' .$viewName, $params);
		} 
		
		return $viewFactory->create($viewName, $params);
	}
}