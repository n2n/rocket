<?php
namespace rocket\ei\util\gui;

use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\manage\api\ApiFieldCallId;

class EiuGuiField {
	private $guiFieldPath;
	private $eiuEntryGui;
	private $eiuAnalyst;
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @param EiuEntryGui $eiuEntryGui
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(GuiFieldPath $guiFieldPath, EiuEntryGui $eiuEntryGui, EiuAnalyst $eiuAnalyst) {
		$this->guiFieldPath = $guiFieldPath;
		$this->eiuEntryGui = $eiuEntryGui;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return GuiFieldPath
	 */
	function getPath() {
		return $this->guiFieldPath;
	}
	
	function createCallId() {
		$eiEntryGui = $this->eiuEntryGui->getEiEntryGui();
		
		return new ApiFieldCallId($this->guiFieldPath, 
				$eiEntryGui->getEiEntry()->getEiMask()->getEiTypePath(),
				$eiEntryGui->getEiGui()->getViewMode(),
				$eiEntryGui->getEiEntry()->getPid());
	}
}