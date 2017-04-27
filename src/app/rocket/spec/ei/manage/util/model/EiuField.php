<?php
namespace rocket\spec\ei\manage\util\model;

class EiuField {
	private $eiFieldPath;
	private $eiuEntry;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		
		$this->eiFieldPath = $eiuFactory->getEiFieldPath(true);
		$this->eiuEntry = $eiuFactory->getEiuEntry(false);
	}
	
	public function getEiFieldPath() {
		return $this->eiFieldPath;
	}
	
	public function getEiuEntry(bool $required = true) {
		if (!$required || $this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		throw new EiuPerimeterException('EiuEntry unavailable.');
	}
	
	public function getValue() {
		return $this->getEiuEntry()->getValue($this->eiFieldPath);
	}
	
	public function setValue($value) {
		return $this->getEiuEntry()->setValue($this->eiFieldPath, $value);
	}
	
	public function setScalarValue($scalarValue) {
		return $this->getEiuEntry()->setScalarValue($this->eiFieldPath, $scalarValue);
	}
}