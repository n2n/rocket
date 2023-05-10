<?php
namespace rocket\op\ei\util\entry;

use rocket\op\ei\manage\entry\EiFieldMap;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\manage\entry\EiEntryValidationResult;

class EiuFieldMap {
	private $eiFieldMap;
	private $eiuAnalyst;
	
	public function __construct(EiFieldMap $eiFieldMap, EiuAnalyst $eiuAnalyst) {
		$this->eiFieldMap = $eiFieldMap;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\op\ei\manage\entry\EiFieldMap
	 */
	public function getEiFieldMap() {
		return $this->eiFieldMap;
	}
	
	/**
	 * @return object
	 */
	public function getObject() {
		return $this->eiFieldMap->getObject();
	}
	
	public function validate(EiEntryValidationResult $eiEntryValidationResult) {
		$this->eiFieldMap->validate($eiEntryValidationResult);
	}
	
	public function write() {
		$this->eiFieldMap->write();
	}
}