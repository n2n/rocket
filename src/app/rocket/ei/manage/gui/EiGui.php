<?php
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\security\InaccessibleEiEntryException;
use rocket\ei\manage\entry\EiEntry;
use n2n\util\ex\IllegalStateException;
use rocket\si\content\SiEntry;

class EiGui {
	/**
	 * @var EiGuiModel
	 */
	private $eiGuiModel;
	/**
	 * @var EiEntryGui[]
	 */
	private $eiEntryGuis = [];
	
	/**
	 * @param EiMask $eiMask
	 * @param EiGuiFrame $eiGuiFrame
	 */
	function __construct(EiGuiModel $eiGuiModel) {
		$this->eiGuiModel = $eiGuiModel;
	}
	
	/**
	 * @return EiGuiModel
	 */
	function getEiGuiModel() {
		return $this->eiGuiModel;
	}
	
	/**
	 * @return boolean
	 */
	function hasMultipleEiEntryGuis() {
		return count($this->eiEntryGuis) > 1;
	}
	
	/**
	 * @return boolean
	 */
	function hasSingleEiEntryGui() {
		return count($this->eiEntryGuis) === 1;
	}
	
	function isEmpty() {
		return empty($this->eiEntryGuis);
	}
	
	function getEiEntryGuis() {
		return $this->eiEntryGuis;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry[] $eiEntries
	 * @param int $treeLevel
	 * @throws \InvalidArgumentException
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	function appendEiEntryGui(EiFrame $eiFrame, array $eiEntries, int $treeLevel = null) {
		return $this->eiEntryGuis[] = $this->eiGuiModel->createEiEntryGui($eiFrame, $eiEntries, $treeLevel);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param int $treeLevel
	 * @throws InaccessibleEiEntryException
	 */
	function appendNewEiEntryGui(EiFrame $eiFrame, int $treeLevel = null) {
		return $this->eiEntryGuis[] = $this->eiGuiModel->createNewEiEntryGui($eiFrame, $treeLevel);
	}
	
	/**
	 * @param bool $siControlsIncluded
	 * @throws IllegalStateException
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry(EiFrame $eiFrame, bool $siControlsIncluded = true) {
		if ($this->hasSingleEiEntryGui()) {
			return $this->eiGuiModel->createSiEntry($eiFrame, current($this->eiEntryGuis), $siControlsIncluded);
		}
		
		throw new IllegalStateException('EiGuiModel has none or multiple EiEntryGuis');
	}
	
	/**
	 * @return \rocket\si\content\SiEntry[]
	 */
	function createSiEntries(EiFrame $eiFrame, bool $siControlsIncluded = true) {
		$siEntries = [];
		foreach ($this->eiEntryGuis as $eiEntryGui) {
			$siEntries[] = $this->eiGuiModel->createSiEntry($eiFrame, $eiEntryGui, $siControlsIncluded);
		}
		return $siEntries;
	}
	
	
}
