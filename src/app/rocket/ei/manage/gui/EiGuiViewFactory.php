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
	private $entryControlsRendered = true;
	private $forkControlsRendered = true;
	
	/**
	 * @param int $mode
	 */
	function __construct(bool $entryControlsRendered) {
		$this->entryControlsRendered = $entryControlsRendered;
	}
	
	/**
	 * @return bool
	 */
	function areEntryControlsRendered() {
		return $this->entryControlsRendered;
	}
	
	/**
	 * @param bool $entryControlsRendered
	 * @return \rocket\ei\manage\gui\EiGuiConfig
	 */
	function setEntryControlsRendered(bool $entryControlsRendered) {
		$this->entryControlsRendered = $entryControlsRendered;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function areForkControlsRendered() {
		return $this->forkControlsRendered;
	}
	
	/**
	 * @param bool $forkControlsRedenered
	 * @return \rocket\ei\manage\gui\EiGuiConfig
	 */
	function setForkControlsRendered(bool $forkControlsRedenered) {
		$this->forkControlsRendered = $forkControlsRedenered;
		return $this;
	}
}