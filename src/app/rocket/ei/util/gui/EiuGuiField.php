<?php
namespace rocket\ei\util\spec;

use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\util\gui\EiuEntryGui;

class EiuGuiField {
	private $guiPropPath;
	private $eiuEntryGui;
	private $eiuAnalyst;
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @param EiuEntryGui $eiuEntryGui
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(GuiFieldPath $guiFieldPath, EiuEntryGui $eiuEntryGui, EiuAnalyst $eiuAnalyst) {
		$this->guiPropPath = $guiPropPath;
		$this->eiuEntryGui = $eiuEntryGui;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return GuiFieldPath
	 */
	function getPath() {
		return $this->guiPropPath;
	}
}