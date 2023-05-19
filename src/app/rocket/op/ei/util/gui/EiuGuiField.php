<?php
namespace rocket\op\ei\util\gui;

use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\api\ApiFieldCallId;

class EiuGuiField {
	private $defPropPath;
	private $eiuGuiEntryTypeDef;
	private $eiuAnalyst;
	
	/**
	 * @param DefPropPath $defPropPath
	 * @param EiuGuiEntry $eiuGuiEntryTypeDef
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(DefPropPath $defPropPath, EiuGuiEntryTypeDef $eiuGuiEntryTypeDef, EiuAnalyst $eiuAnalyst) {
		$this->defPropPath = $defPropPath;
		$this->eiuGuiEntryTypeDef = $eiuGuiEntryTypeDef;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return DefPropPath
	 */
	function getPath() {
		return $this->defPropPath;
	}
	
	function createCallId() {
		$eiGuiEntry = $this->eiuGuiEntryTypeDef->getEiGuiEntry();
		
		return new ApiFieldCallId($this->defPropPath, 
				$eiGuiEntry->getEiEntry()->getEiMask()->getEiTypePath(),
				$this->eiuAnalyst->getEiuGuiMaskDeclaration(true)->getViewMode(),
				$eiGuiEntry->getEiEntry()->getPid());
	}
}