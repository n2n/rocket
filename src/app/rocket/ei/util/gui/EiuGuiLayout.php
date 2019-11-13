<?php
namespace rocket\ei\util\gui;

use rocket\ei\manage\gui\EiGui;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\EiGuiLayout;
use rocket\ei\util\entry\EiuObject;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\util\EiuPerimeterException;

class EiuGuiLayout {
	private $eiGuiLayout;
	private $eiuGui;
	private $eiuAnalyst;
	
	/**
	 * @param EiGui $eiGui
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGuiLayout $eiGuiLayout, ?EiuGui $eiuGui, EiuAnalyst $eiuAnalyst) {
		$this->eiGuiLayout = $eiGuiLayout;
		$this->eiuGui = $eiuGui;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	public function getEiuFrame() {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		if ($this->eiuAnalyst !== null) {
			$this->eiuFrame = $this->eiuAnalyst->getEiuFrame(false);
		}
		
		if ($this->eiuFrame === null) {
			$this->eiuFrame = new EiuFrame($this->eiGui->getEiFrame(), $this->eiuAnalyst);
		}
		
		return $this->eiuFrame;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiLayout
	 */
	public function getEiGuiLayout() {
		return $this->eiGuiLayout;
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
			return new EiuEntryGui(current($eiEntryGuis), $this, $this->eiuAnalyst);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No single EiuEntryGui is available.');
	}
	
	public function entryGuis() {
		$eiuEntryGuis = array();
		
		foreach ($this->eiGui->getEiEntryGuis() as $eiEntryGui) {
			$eiuEntryGuis[] = new EiuEntryGui($eiEntryGui, $this, $this->eiuAnalyst);
		}
		
		return $eiuEntryGuis;
	}
	
	/**
	 *
	 * @param mixed $eiEntryArg
	 * @param bool $makeEditable
	 * @param int $treeLevel
	 * @return EiuEntryGui
	 */
	public function appendNewEntryGui($eiEntryArg, int $treeLevel = null) {
		$eiEntry = null;
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiEntryArg, 'eiEntryArg', $this->eiuFrame->getContextEiType(), true,
				$eiEntry);
		
		if ($eiEntry === null) {
			$eiEntry = (new EiuEntry(null, new EiuObject($eiObject, $this->eiuAnalyst),
					null, $this->eiuAnalyst))->getEiEntry(true);
		}
		
		return new EiuEntryGui($this->eiuAnalyst->getEiGui(true)
				->createEiEntryGui($eiEntry, $treeLevel, true), $this, $this->eiuAnalyst);
	}
}