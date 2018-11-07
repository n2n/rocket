<?php
namespace rocket\ei\util\entry;

use rocket\ei\manage\entry\EiFieldMap;
use rocket\ei\util\EiuAnalyst;

class EiuFieldMap {
	private $eiFieldMap;
	private $eiuAnalyst;
	
	public function __construct(EiFieldMap $eiFieldMap, EiuAnalyst $eiuAnalyst) {
		$this->eiFieldMap = $eiFieldMap;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiFieldMap
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
}