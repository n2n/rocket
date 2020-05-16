<?php
namespace rocket\ei\util\gui;

use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\manage\api\ApiFieldCallId;

class EiuGuiField {
	private $guiPropPath;
	private $eiuEntryGuiTypeDef;
	private $eiuAnalyst;
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @param EiuEntryGui $eiuEntryGuiTypeDef
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(GuiPropPath $guiPropPath, EiuEntryGuiTypeDef $eiuEntryGuiTypeDef, EiuAnalyst $eiuAnalyst) {
		$this->guiPropPath = $guiPropPath;
		$this->eiuEntryGuiTypeDef = $eiuEntryGuiTypeDef;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return GuiPropPath
	 */
	function getPath() {
		return $this->guiPropPath;
	}
	
	function createCallId() {
		$eiEntryGuiTypeDef = $this->eiuEntryGuiTypeDef->getEiEntryGuiTypeDef();
		
		return new ApiFieldCallId($this->guiPropPath, 
				$eiEntryGuiTypeDef->getEiEntry()->getEiMask()->getEiTypePath(),
				$this->eiuAnalyst->getEiuGuiFrame(true)->getViewMode(),
				$eiEntryGuiTypeDef->getEiEntry()->getPid());
	}
}