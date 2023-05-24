<?php
namespace rocket\op\ei\util\gui;

use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\api\ApiFieldCallId;

class EiuGuiField {
	private $defPropPath;
	private $eiuGuiEntry;
	private $eiuAnalyst;
	
	/**
	 * @param DefPropPath $defPropPath
	 * @param EiuGuiEntry $eiuGuiEntry
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(DefPropPath $defPropPath, EiuGuiEntry $eiuGuiEntry, EiuAnalyst $eiuAnalyst) {
		$this->defPropPath = $defPropPath;
		$this->eiuGuiEntry = $eiuGuiEntry;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return DefPropPath
	 */
	function getPath(): DefPropPath {
		return $this->defPropPath;
	}
	
	function createCallId() {
		$eiGuiEntry = $this->eiuGuiEntry->getEiGuiEntry();
		
		return new ApiFieldCallId($this->defPropPath, 
				$eiGuiEntry->getEiEntry()->getEiMask()->getEiTypePath(),
				$this->eiuAnalyst->getEiuGuiMaskDeclaration(true)->getViewMode(),
				$eiGuiEntry->getEiEntry()->getPid());
	}
}