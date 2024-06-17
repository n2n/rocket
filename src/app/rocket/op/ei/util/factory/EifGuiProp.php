<?php
namespace rocket\op\ei\util\factory;

use rocket\impl\ei\component\prop\adapter\gui\EiGuiPropProxy;
use rocket\op\ei\manage\gui\EiGuiProp;

class EifGuiProp {
	private $guiPropCallback;
	
	/**
	 * @param \Closure $guiPropSetupCallback
	 */
	function __construct(\Closure $guiPropCallback) {
		$this->guiPropCallback = $guiPropCallback;
	}
	
	/**
	 * @return EiGuiProp
	 */
	function toEiGuiProp(): EiGuiProp {
		return new EiGuiPropProxy($this->guiPropCallback);
	}
}