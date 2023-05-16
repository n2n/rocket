<?php
namespace rocket\op\ei\util\gui;

use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\api\ApiFieldCallId;

class EiuGuiField {
	private $defPropPath;
	private $eiuEntryGuiTypeDef;
	private $eiuAnalyst;
	
	/**
	 * @param DefPropPath $defPropPath
	 * @param EiuEntryGui $eiuEntryGuiTypeDef
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(DefPropPath $defPropPath, EiuEntryGuiTypeDef $eiuEntryGuiTypeDef, EiuAnalyst $eiuAnalyst) {
		$this->defPropPath = $defPropPath;
		$this->eiuEntryGuiTypeDef = $eiuEntryGuiTypeDef;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return DefPropPath
	 */
	function getPath() {
		return $this->defPropPath;
	}
	
	function createCallId() {
		$eiGuiEntry = $this->eiuEntryGuiTypeDef->getEiGuiEntry();
		
		return new ApiFieldCallId($this->defPropPath, 
				$eiGuiEntry->getEiEntry()->getEiMask()->getEiTypePath(),
				$this->eiuAnalyst->getEiuGuiFrame(true)->getViewMode(),
				$eiGuiEntry->getEiEntry()->getPid());
	}
}