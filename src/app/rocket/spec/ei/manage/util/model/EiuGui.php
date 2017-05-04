<?php
namespace rocket\spec\ei\manage\util\model;

class EiuGui {
	private $eiGui;
	private $eiuFrame;
	private $singleEiuEntryGui;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		$this->eiGui = $eiuFactory->getEiGui(true);
		$this->eiuFrame = $eiuFactory->getEiuFrame(true);
	}
	
	public function getEiuFrame() {
		return $this->eiuFrame;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\gui\EiGui
	 */
	public function getEiGui() {
		return $this->eiGui;
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
		return 1 == count($this->eiGui->getEiEntryGuis());
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

