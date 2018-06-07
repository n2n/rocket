<?php
namespace rocket\ei\mask\model;

use rocket\ei\manage\gui\ui\DisplayStructure;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\EiGuiViewFactory;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\ViewFactory;
use n2n\reflection\CastUtils;
use rocket\ei\util\model\Eiu;
use n2n\web\ui\UiComponent;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\ViewMode;

class CommonEiGuiViewFactory implements EiGuiViewFactory {
	private $eiGui;
	private $guiDefinition;
	private $displayStructure;
	private $controlsAllowed = false;
	
	public function __construct(EiGui $eiGui, GuiDefinition $guiDefinition, DisplayStructure $displayStructure) {
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
	 * @see \rocket\ei\manage\gui\EiGuiViewFactory::getGuiIdPaths()
	 */
	public function getGuiIdPaths(): array {
		return $this->displayStructure->getAllGuiIdPaths();
	}
	
	public function applyMode(int $rule) {
		switch ($rule) {
			case self::MODE_NO_GROUPS:
				$this->displayStructure = $this->displayStructure->withoutGroups();
				break;
			case self::MODE_ROOT_GROUPED:
				$this->displayStructure = $this->displayStructure->groupedItems();
				break;
			case self::MODE_CONTROLS_ALLOWED: 
				$this->controlsAllowed = true;
				break;
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiGuiViewFactory::getDisplayStructure()
	 */
	public function getDisplayStructure(): ?DisplayStructure {
		return $this->displayStructure;
	}
	
// 	private function determineDisplayStructure($viewMode): DisplayStructure {
// 		$displayStructure = null;
	
// 		if ($viewMode & DisplaySettings::COMPACT_VIEW_MODES) {
// 			if (null !== ($overviewDisplayStructure = $this->displayScheme->getOverviewDisplayStructure())) {
// 				return $overviewDisplayStructure;
// 			}
// 			return $this->createDefaultDisplayStructure($viewMode);
// 		} 
		
// 		switch ($viewMode) {
// 			case DisplaySettings::VIEW_MODE_BULKY_READ:
// 				if (null !== ($detailDisplayStructure = $this->displayScheme->getDetailDisplayStructure())) {
// 					return $detailDisplayStructure;
// 				}
// 				break;
// 			case DisplaySettings::VIEW_MODE_BULKY_EDIT:
// 				if (null !== $editDisplayStructure = $this->displayScheme->getEditDisplayStructure()) {
// 					return $editDisplayStructure;
// 				}
// 				break;
// 			case DisplaySettings::VIEW_MODE_BULKY_ADD:
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
			$viewName = 'rocket\ei\mask\view\bulky.html';
		} else {
			$viewName = 'rocket\ei\mask\view\compact.html';
			$displayStructure = $displayStructure->withoutGroups();
		}
		
		$params = array('displayStructure' => $displayStructure, 'eiu' => new Eiu($this->eiGui),
				'controlsAllowed' => $this->controlsAllowed);
		
		if ($contextView !== null) {
			return $contextView->getImport('\\' .$viewName, $params);
		} 
		
		return $viewFactory->create($viewName, $params);
	}

}