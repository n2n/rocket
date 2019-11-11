<?php
namespace rocket\ei\util\gui;

use rocket\ei\manage\gui\EiGui;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\EiGuiLayout;

class EiuGuiLayout {
	private $eiGuiLayout;
	private $eiuAnalyst;
	
	/**
	 * @param EiGui $eiGui
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGuiLayout $eiGuiLayout, EiuAnalyst $eiuAnalyst) {
		$this->eiGuiLayout = $eiGuiLayout;
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
}