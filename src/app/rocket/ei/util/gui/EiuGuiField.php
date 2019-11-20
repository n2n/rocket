<?php
namespace rocket\ei\util\gui;

use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\manage\api\ApiFieldCallId;

class EiuGuiFrameField {
	private $guiPropPath;
	private $eiuEntryGui;
	private $eiuAnalyst;
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @param EiuEntryGui $eiuEntryGui
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(GuiPropPath $guiPropPath, EiuEntryGui $eiuEntryGui, EiuAnalyst $eiuAnalyst) {
		$this->guiPropPath = $guiPropPath;
		$this->eiuEntryGui = $eiuEntryGui;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return GuiPropPath
	 */
	function getPath() {
		return $this->guiPropPath;
	}
	
	function createCallId() {
		$eiEntryGui = $this->eiuEntryGui->getEiEntryGui();
		
		return new ApiFieldCallId($this->guiPropPath, 
				$eiEntryGui->getEiEntry()->getEiMask()->getEiTypePath(),
				$eiEntryGui->getEiGuiFrame()->getViewMode(),
				$eiEntryGui->getEiEntry()->getPid());
	}
}