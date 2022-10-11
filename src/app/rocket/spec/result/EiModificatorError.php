<?php
namespace rocket\spec\result;

use rocket\ei\component\modificator\EiModNature;
use rocket\ei\EiModificatorPath;
use rocket\spec\TypePath;

class EiModificatorError {
	private $eiTypePath;
	private $eiModificatorPath;
	private $eiModificator;
	private $t;
	
	public function __construct(TypePath $eiTypePath, EiModificatorPath $eiModificatorPath, \Throwable $t, 
			EiModNature $eiModificator = null) {
		$this->eiTypePath = $eiTypePath;
		$this->eiModificatorPath = $eiModificatorPath;
		$this->t = $t;
		$this->eiModificator = $eiModificator;
	}
	
	public function getEiTypePath() {
		return $this->eiTypePath;
	}
	
	public function getEiModificatorPath() {
		return $this->eiModificatorPath;
	}
	
	public function getEiModificator() {
		return $this->eiModificator;
	}
	
	/**
	 * @return \Throwable
	 */
	public function getThrowable() {
		return $this->t;
	}
	
	public static function fromEiModificator(EiModNature $eiModificator, \Throwable $t) {
		$wrapper = $eiModificator->getWrapper();
		return new EiModificatorError($wrapper->getEiModCollection()->getEiMask()->getEiTypePath(),
				$wrapper->getEiModificatorPath(), $t, $eiModificator);
	}
}