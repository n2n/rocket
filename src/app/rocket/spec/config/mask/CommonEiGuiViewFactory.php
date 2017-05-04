<?php
namespace rocket\spec\config\mask;

use rocket\spec\ei\manage\gui\ui\DisplayStructure;
use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\manage\gui\EiGui;
use rocket\spec\ei\manage\gui\EiGuiViewFactory;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\ViewFactory;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\manage\mapping\EiEntry;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\component\GuiFactory;
use rocket\spec\config\mask\model\DisplayScheme;
use rocket\spec\ei\manage\util\model\Eiu;

class CommonEiGuiViewFactory implements EiGuiViewFactory {
	private $displayScheme;
	private $displayStructure;
	
	private $eiGui;
	private $eiEntryGuis = array();
	private $displayStructures = array();
	
	public function __construct(DisplayScheme $displayScheme) {
		$this->displayScheme = $displayScheme;
	}
	
	public function setEiGui(EiGui $eiGui) {
		$this->eiGui = $eiGui;	
		
		if ($eiGui->isBulky()) return;
		
		$this->displayStructure = $this->determineDisplayStructure(DisplayDefinition::COMPACT_VIEW_MODES);
	}
	
	public function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}
	
	public function getDisplayStructure(): DisplayStructure {
		return $this->displayStructure;
	}
	
	public function createEiEntryGui(EiEntry $eiEntry, bool $makeEditable, int $treeLevel = null, 
			bool $append = true): EiEntryGui {
		$bulky = $this->eiGui->isBulky();
		
		$viewMode = null;
		if (!$makeEditable) {
			$viewMode = $bulky ? DisplayDefinition::VIEW_MODE_BULKY_READ : DisplayDefinition::VIEW_MODE_COMPACT_READ;
		} else if ($eiEntry->isNew()) {
			$viewMode = $bulky ? DisplayDefinition::VIEW_MODE_BULKY_ADD : DisplayDefinition::VIEW_MODE_COMPACT_ADD;
		} else {
			$viewMode = $bulky ? DisplayDefinition::VIEW_MODE_BULKY_EDIT : DisplayDefinition::VIEW_MODE_COMPACT_EDIT;
		}
		
		$guiIdsPaths = null;
		if ($this->displayStructure !== null) {
			$guiIdsPaths = $this->displayStructure->getAllGuiIdPaths();
		} else {
			$displayStructure = $this->determineDisplayStructure($viewMode); 
			$guiIdsPaths = $displayStructure->getAllGuiIdPaths();
			
			if ($append) {
				$this->displayStructures[] = $displayStructure;
			}
		}
		
		$eiEntryGui = GuiFactory::createEiEntryGui($this->eiGui, $eiEntry, $viewMode, $guiIdsPaths, $treeLevel);
		if ($append) {
			$this->eiEntryGuis[] = $eiEntryGui;
		}
		
		return $eiEntryGui;
	}
		
	public function appendEiEntryGui(EiEntryGui $eiEntryGui) {
		$this->eiEntryGuis[] = $eiEntryGui;
		
		if ($this->displayStructure === null) {
			$this->displayStructures[] = $this->determineDisplayStructure($eiEntryGui->getViewMode());
		}
	}
	
	private function determineDisplayStructure($viewMode): DisplayStructure {
		$displayStructure = null;
	
		if ($viewMode & DisplayDefinition::COMPACT_VIEW_MODES) {
			if (null !== ($overviewDisplayStructure = $this->displayScheme->getOverviewDisplayStructure())) {
				return $overviewDisplayStructure;
			}
			return $this->createDefaultDisplayStructure($viewMode);
		} 
		
		switch ($viewMode) {
			case DisplayDefinition::VIEW_MODE_BULKY_READ:
				if (null !== ($detailDisplayStructure = $this->displayScheme->getDetailDisplayStructure())) {
					return $detailDisplayStructure;
				}
				break;
			case DisplayDefinition::VIEW_MODE_BULKY_EDIT:
				if (null !== $editDisplayStructure = $this->displayScheme->getEditDisplayStructure()) {
					return $editDisplayStructure;
				}
				break;
			case DisplayDefinition::VIEW_MODE_BULKY_ADD:
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
	
	
	public function createView(): HtmlView {
		$viewFactory = $this->eiGui->getEiFrame()->getN2nContext()->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		$displayStructure = $this->displayStructure;
		$viewName = null;
		
		if ($this->eiGui->isBulky()) {
			$viewName = 'rocket\spec\config\mask\view\bulky.html';
			$displayStructure = current($this->displayStructures);
		} else {
			$viewName = 'rocket\spec\config\mask\view\overview.html';
		}
		
		return $viewFactory->create($viewName, 
				array('displayStructure' => $displayStructure, 'eiu' => new Eiu($this->eiGui)));
	}
}