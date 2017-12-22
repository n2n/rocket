<?php
namespace rocket\spec\config\mask;

use rocket\spec\ei\manage\gui\ui\DisplayStructure;
use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\manage\gui\EiGuiViewFactory;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\ViewFactory;
use n2n\reflection\CastUtils;
use rocket\spec\ei\component\field\impl\adapter\DisplaySettings;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\web\ui\UiComponent;
use rocket\spec\ei\manage\gui\EiGui;
use rocket\spec\ei\manage\gui\ViewMode;

class CommonEiGuiViewFactory implements EiGuiViewFactory {
	private $eiGui;
	private $guiDefinition;
	private $displayStructure;
	
	public function __construct(EiGui $eiGui, GuiDefinition $guiDefinition, DisplayStructure $displayStructure) {
		$this->eiGui = $eiGui;
		$this->guiDefinition = $guiDefinition;
		$this->displayStructure = $displayStructure;
	}
	
	public function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}
	
	public function getDisplayStructure(): DisplayStructure {
		return $this->displayStructure;
	}
	
	private function determineDisplayStructure($viewMode): DisplayStructure {
		$displayStructure = null;
	
		if ($viewMode & DisplaySettings::COMPACT_VIEW_MODES) {
			if (null !== ($overviewDisplayStructure = $this->displayScheme->getOverviewDisplayStructure())) {
				return $overviewDisplayStructure;
			}
			return $this->createDefaultDisplayStructure($viewMode);
		} 
		
		switch ($viewMode) {
			case DisplaySettings::VIEW_MODE_BULKY_READ:
				if (null !== ($detailDisplayStructure = $this->displayScheme->getDetailDisplayStructure())) {
					return $detailDisplayStructure;
				}
				break;
			case DisplaySettings::VIEW_MODE_BULKY_EDIT:
				if (null !== $editDisplayStructure = $this->displayScheme->getEditDisplayStructure()) {
					return $editDisplayStructure;
				}
				break;
			case DisplaySettings::VIEW_MODE_BULKY_ADD:
				if (null !== ($addDisplayStructure = $this->displayScheme->getAddDisplayStructure())) {
					return $addDisplayStructure;
				}
				break;
		}
	
		if (null !== ($bulkyDisplayStructure = $this->displayScheme->getBulkyDisplayStructure())) {
			return $bulkyDisplayStructure;
		}
	
		return $this->createDefaultDisplayStructure($viewMode);
	}
	
	private function createDefaultDisplayStructure($viewMode) {
		$displayStructure = new DisplayStructure();
		foreach ($this->eiGui->getGuiDefinition()->filterGuiIdPaths($viewMode) as $guiIdPath) {
			$displayStructure->addGuiIdPath($guiIdPath);
		}
		return $displayStructure;
	}
	
	
	public function createView(array $eiEntryGuis, HtmlView $contextView = null): UiComponent {
		$viewFactory = $this->eiGui->getEiFrame()->getN2nContext()->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		$displayStructure = $this->displayStructure;
		$viewName = null;
		
		if ($this->eiGui->getViewMode() & ViewMode::bulky()) {
			$viewName = 'rocket\spec\config\mask\view\bulky.html';
			$displayStructure = $displayStructure->grouped();
		} else {
			$viewName = 'rocket\spec\config\mask\view\compact.html';
			$displayStructure = $displayStructure->withoutGroups();
		}
		
		$params = array('displayStructure' => $displayStructure, 'eiu' => new Eiu($this->eiGui));
		
		if ($contextView !== null) {
			return $contextView->getImport('\\' .$viewName, $params);
		} 
		
		return $viewFactory->create($viewName, $params);
	}
}