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
	public function isCompact() {
		return !$this->eiGui->isBulky();
	}
	
	/**
	 * @return bool
	 */
	public function isSingle() {
		return 1 == count($this->eiGui->getEiEntryGuis());
	}
	
	/**
	 * 
	 * @param bool $required
	 * @return EiuEntryGui|null
	 */
	public function entryGui(bool $required = true) {
		$eiEntryGuis = $this->eiGui->getEiEntryGuis();
		$eiEntryGui = null;
		if (count($eiEntryGuis) == 1) {
			return new EiuEntryGui(current($eiEntryGuis), $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No single EiuEntryGui is available.');
	}
	
	public function entryGuis() {
		$eiuEntryGuis = array();
		
		foreach ($this->eiGui->getEiEntryGuis() as $eiEntryGui) {
			$eiuEntryGuis[] = new EiuEntryGui($eiEntryGui, $this);
		}
		
		return $eiuEntryGuis;
	}
	
// 	/**
// 	 * @param bool $required
// 	 * @throws EiuPerimeterException
// 	 * @return EiuEntryGui|null
// 	 */
// 	public function entryGui(bool $required = true) {
// 		if ($this->singleEiuEntryGui !== null || !$required) return $this->singleEiuEntryGui;
		
// 		throw new EiuPerimeterException('EiuEntryGui is unavailable.');
// 	}
	
	/**
	 * 
	 * @param unknown $eiEntryArg
	 * @param bool $makeEditable
	 * @param int $treeLevel
	 * @return EiuEntryGui
	 */
	public function appendNewEntryGui($eiEntryArg, bool $makeEditable = false, int $treeLevel = null) {
		$eiEntry = null;
		$eiObject = EiuFactory::buildEiObjectFromEiArg($eiEntryArg, 'eiEntryArg', $this->eiuFrame->getEiType(), true, 
				$eiEntry);
		
		if ($eiEntry === null) {
			$eiEntry = (new EiuEntry($eiObject, $this->eiuFrame))->getEiEntry();
		}
		
		return new EiuEntryGui($this->eiGui->createEiEntryGui($eiEntry, $makeEditable, $treeLevel, true), $this);
	}
	
	/**
	 * 
	 * @return \n2n\impl\web\ui\view\html\HtmlView
	 */
	public function createView() {
		return $this->eiGui->createView();
	}
}

