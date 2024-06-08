<?php
namespace rocket\op\ei\util\factory;

use rocket\impl\ei\component\prop\adapter\gui\GuiPropProxy;
use rocket\ui\gui\GuiProp;

class EifGuiProp {
	private $guiPropCallback;
	
	/**
	 * @param \Closure $guiPropSetupCallback
	 */
	function __construct(\Closure $guiPropCallback) {
		$this->guiPropCallback = $guiPropCallback;
	}
	
	/**
	 * @return GuiProp
	 */
	function toGuiProp() {
		return new GuiPropProxy($this->guiPropCallback);
	}
}