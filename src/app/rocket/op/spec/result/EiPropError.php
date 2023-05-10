<?php
namespace rocket\op\spec\result;

use rocket\op\ei\component\prop\EiPropNature;
use rocket\op\ei\EiPropPath;
use rocket\op\spec\TypePath;
use rocket\impl\ei\component\prop\adapter\EiPropNatureAdapter;

class EiPropError {
	private $eiTypePath;
	private $eiPropPath;
	private $eiProp;
	private $t;
	
	public function __construct(TypePath $eiTypePath, EiPropPath $eiPropPath, \Throwable $t, EiPropNature $eiProp = null) {
		$this->eiTypePath = $eiTypePath;
		$this->eiPropPath = $eiPropPath;
		$this->eiProp = $eiProp;
		$this->t = $t;
	}
	
	public function getEiPropPath() {
		return $this->eiPropPath;
	}
	
	public function getEiProp() {
		return $this->eiProp;
	}
	
	public function getEiTypePath() {
		return $this->eiTypePath;
	}
	
	/**
	 * @return \Throwable
	 */
	public function getThrowable() {
		return $this->t;
	}
	
	public static function fromEiProp(EiPropNatureAdapter $eiProp, \Throwable $t) {
		$wrapper = $eiProp->getWrapper();
		return new EiPropError($wrapper->getEiPropCollection()->getEiMask()->getEiTypePath(), 
				$wrapper->getEiPropPath(), $t, $eiProp);
	}
}