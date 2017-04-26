<?php
namespace rocket\spec\config\mask;

use rocket\spec\ei\manage\gui\DisplayStructure;
use rocket\spec\ei\manage\EiFrame;
use n2n\web\ui\view\View;
use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\manage\gui\EiGui;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\mask\EiMask;

class CommonEiGui implements EiGui {
	private $eiFrame;
	private $guiDefinition;
	private $displayStructure;
	private $viewMode;
	
	public function __construct(EiMask $eiMask, DisplayStructure $displayStructure, int $viewMode, EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
		$this->eiMask = $eiMask;
		$this->displayStructure = $displayStructure;
		$this->viewMode = $viewMode;
		$this->eiFrame = $eiFrame;
	}
	
	public function getEiFrame(): EiFrame {
		return $this->eiFrame;
	}
	
	public function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}
	
	public function getDisplayStructure(): DisplayStructure {
		return $this->displayStructure;
	}
	
	public function getViewMode(): int {
		return $this->viewMode;
	}
	
	public function createEiEntryGui(EiEntry $eiEntry, int $level = null): EiEntryGui {
		
	}
	
	public function createView(): View {
		
	}
}