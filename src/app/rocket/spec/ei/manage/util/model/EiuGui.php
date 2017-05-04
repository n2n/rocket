<?php
namespace rocket\spec\ei\manage\util\model;

class EiuGui {
	private $eiGui;
	private $singleEiuEntryGui;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		$this->eiGui = $eiuFactory->getEiGui(true);
		$this->eiuEntryGui = $eiuFactory->getEiuEntryGui(false);
	}
	
	/**
	 * @return bool
	 */
	public function isBulky() {
		return $this->eiGui->isBulky();	
	}
	
	/**
	 * @return bool
	 */
	public function isSingle() {
		return $this->singleEiuEntryGui !== null;
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiuEntryGui|null
	 */
	public function entryGui(bool $required = true) {
		if ($this->singleEiuEntryGui !== null || !$required) return $this->singleEiuEntryGui;
		
		throw new EiuPerimeterException('EiuEntryGui is unavailable.');
	}
}

