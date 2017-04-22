<?php
namespace rocket\spec\ei\manage\util\model;

class EiuField {
	private $eiPropPath;
	private $eiuEntry;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		
		$this->eiPropPath = $eiuFactory->getEiPropPath(true);
		$this->eiuEntry = $eiuFactory->getEiuEntry(false);
	}
	
	public function getEiPropPath() {
		return $this->eiPropPath;
	}
	
	public function getEiuEntry(bool $required = true) {
		if (!$required || $this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		throw new EiuPerimeterException('EiuEntry unavailable.');
	}
	
	public function getValue() {
		return $this->getEiuEntry()->getValue($this->eiPropPath);
	}
	
	public function setValue($value) {
		return $this->getEiuEntry()->setValue($this->eiPropPath, $value);
	}
	
	public function setScalarValue($scalarValue) {
		return $this->getEiuEntry()->setScalarValue($this->eiPropPath, $scalarValue);
	}
}