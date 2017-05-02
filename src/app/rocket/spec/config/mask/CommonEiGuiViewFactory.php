<?php
namespace rocket\spec\config\mask;

use rocket\spec\ei\manage\gui\ui\DisplayStructure;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\manage\gui\EiGui;
use rocket\spec\ei\manage\gui\EiGuiViewFactory;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\ViewFactory;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\gui\DisplayDefinition;

class CommonEiGuiViewFactory implements EiGuiViewFactory {
	private $eiGui;
	private $displayStructure;
	
	public function __construct(EiGui $eiGui, DisplayStructure $displayStructure) {
		$this->eiGui = $eiGui;
		$this->displayStructure = $displayStructure;
	}
	
	public function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}
	
	public function getDisplayStructure(): DisplayStructure {
		return $this->displayStructure;
	}
	
	public function createView(): HtmlView {
		$viewFactory = $this->eiGui->getEiFrame()->getN2nContext()->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		$viewName = null;
		
		if ($viewMode & DisplayDefinition::BULKY_VIEW_MODES) {
			$viewName = 'rocket\spec\config\mask\view\bulky.html';
		} else {
			$viewName = 'rocket\spec\config\mask\view\overview.html';
		}
		
		return $viewFactory->create($viewName, 
				array('displayStructure' => $displayStructure, 'eiu' => new Eiu($eiuEntryGui)));
	}
}