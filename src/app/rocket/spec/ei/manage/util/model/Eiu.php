<?php
namespace rocket\spec\ei\manage\util\model;

use n2n\context\Lookupable;
use rocket\spec\ei\manage\util\model\EiuFrame;

class Eiu implements Lookupable {
	private $eiuFactory;
	private $eiuCtrl;
	private $eiuFrame;
	private $eiuGui;
	private $eiuEntry;
	private $eiuField;
	
	
	public function __construct(...$eiArgs) {
		$this->eiuFactory = new EiuFactory();
		$this->eiuFactory->applyEiArgs(...$eiArgs);
		$this->eiuFrame = $this->eiuFactory->getEiuFrame(false);
		$this->eiuEntry = $this->eiuFactory->getEiuEntry(false);
		$this->eiuGui = $this->eiuFactory->getEiuEntryGui(false);
		$this->eiuField = $this->eiuFactory->getEiuField(false);
	}
	
	public function ctrl(bool $required = true) {
		if ($this->eiuCtrl !== null || !$required) return $this->eiuCtrl;
		
		throw new EiuPerimeterException('EiuCtrl is unavailable.');
	}

	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function frame(bool $required = true)  {
		if ($this->eiuFrame !== null || !$required) return $this->eiuFrame;
		
		throw new EiuPerimeterException('EiuFrame is unavailable.');
	}
	
	/**
	 * @param unknown $eiEntryObj
	 * @param bool $assignToEiu
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function entry(bool $required = true) {
		if ($this->eiuEntry !== null || !$required) return $this->eiuEntry;
	
		throw new EiuPerimeterException('EiuEntry is unavailable.');
	}
	
	public function gui(bool $required = true) {
		if ($this->eiuGui !== null || !$required) return $this->eiuGui;
	
		throw new EiuPerimeterException('EiuEntryGui is unavailable.');
	}
	
	public function field(bool $required = true) {
		if ($this->eiuField !== null || !$required) return $this->eiuField;
		
		throw new EiuPerimeterException('EiuField is unavailable.');
	}
	
	/**
	 * @param string|\ReflectionClass $lookupId
	 * @return mixed
	 */
	public function lookup($lookupId, bool $required = true) {
		return $this->frame()->getN2nContext()->lookup($lookupId, $required);
	}
}