<?php
namespace rocket\ei\manage\gui;

use rocket\ei\manage\gui\ui\DisplayStructure;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlView;

interface EiGuiViewFactory {
	/**
	 * @return GuiDefinition
	 */
	public function getGuiDefinition(): GuiDefinition;
	
	/**
	 * @return GuiIdPath[];
	 */
	public function getGuiIdPaths(): array;
	
	/**
	 * @return DisplayStructure
	 */
	public function getDisplayStructure(): DisplayStructure;
	
	/**
	 * @param DisplayStructure $displayStructure
	 */
	public function setDisplayStructure(DisplayStructure $displayStructure);
	
	/**
	 * @return UiComponent
	 */
	public function createUiComponent(array $eiEntryGuis, ?HtmlView $contextView, 
			EiGuiConfig $eiGuiConfig): UiComponent;
}

class EiGuiConfig {
	private $controlsAllowed = true;
	
	/**
	 * @param int $mode
	 */
	function __construct(bool $controlsAllowed) {
		$this->controlsAllowed = $controlsAllowed;
	}
	
	/**
	 * @return bool
	 */
	function areControlsAllowed() {
		return $this->controlsAllowed;
	}
	
	/**
	 * @param bool $controlsAllowed
	 * @return \rocket\ei\manage\gui\EiGuiConfig
	 */
	function setControlsAllowed(bool $controlsAllowed) {
		$this->controlsAllowed = $controlsAllowed;
		return $this;
	}
}