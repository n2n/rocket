<?php
namespace rocket\op\ei\util\gui;

use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\util\EiuPerimeterException;
use rocket\op\ei\manage\gui\EiGuiUtil;
use rocket\op\ei\manage\gui\ViewMode;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\EiGui;

class EiuGui {
	private $eiGui;
	private $eiuGuiModel;
	private $eiuAnalyst;
	
	/**
	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGui $eiGui, ?EiuGuiModel $eiuGuiModel, EiuAnalyst $eiuAnalyst) {
		$this->eiGui = $eiGui;
		$this->eiuGuiModel = $eiuGuiModel;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\op\ei\manage\gui\EiGui
	 */
	public function getEiGui() {
		return $this->eiGui;
	}
	
	/**
	 * @return boolean
	 */
	function isCompact() {
		return ViewMode::isCompact($this->eiGui->getEiGuiDeclaration()->getViewMode());
	}
	
	/**
	 * @return boolean
	 */
	function isBulky() {
		return ViewMode::isBulky($this->eiGui->getEiGuiDeclaration()->getViewMode());
	}
	
	/**
	 * @return \rocket\op\ei\util\gui\EiuGuiModel
	 */
	function guiModel() {
		if ($this->eiuGuiModel === null) {
			$this->eiuGuiModel = new EiuGuiModel($this->eiGui->getEiGuiDeclaration(), $this->eiuAnalyst);
		}
		
		return $this->eiuGuiModel;
	}
	
	/**
	 *
	 * @param bool $required
	 * @return EiuEntryGui|null
	 */
	public function entryGui(bool $required = true) {
		$eiGuiValueBoundaries = $this->eiGui->getEiGuiValueBoundaries();
		
		if (count($eiGuiValueBoundaries) == 1) {
			return new EiuEntryGui(current($eiGuiValueBoundaries), $this, $this->eiuAnalyst);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No single EiuEntryGui is available.');
	}
	
	public function entryGuis() {
		$eiuEntryGuis = array();
		
		foreach ($this->eiGuiMaskDeclaration->getEiGuiValueBoundaries() as $eiGuiValueBoundary) {
			$eiuEntryGuis[] = new EiuEntryGui($eiGuiValueBoundary, $this, null, $this->eiuAnalyst);
		}
		
		return $eiuEntryGuis;
	}
	
// 	/**
// 	 *
// 	 * @param mixed $eiEntryArg
// 	 * @param bool $makeEditable
// 	 * @param int $treeLevel
// 	 * @return EiuEntryGui
// 	 */
// 	public function appendNewEntryGui($eiEntryArg, int $treeLevel = null) {
// 		$eiEntry = null;
// 		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiEntryArg, 'eiEntryArg', $this->eiuFrame->getContextEiType(), true,
// 				$eiEntry);
		
// 		if ($eiEntry === null) {
// 			$eiEntry = (new EiuEntry(null, new EiuObject($eiObject, $this->eiuAnalyst),
// 					null, $this->eiuAnalyst))->getEiEntry(true);
// 		}
		
// 		return new EiuEntryGui($this->eiuAnalyst->getEiGuiMaskDeclaration(true)
// 				->createEiGuiValueBoundary($eiEntry, $treeLevel, true), $this, null, $this->eiuAnalyst);
// 	}
	
	/**
	 * @param bool $entrySiControlsIncluded
	 * @return \rocket\si\content\SiValueBoundary
	 */
	function createSiEntry(bool $entrySiControlsIncluded = true) {
		return $this->eiGui->createSiEntry($this->eiuAnalyst->getEiFrame(true), $entrySiControlsIncluded);
	}
	
	/**
	 * @param bool $generalSiControlsIncluded
	 * @param bool $entrySiControlsIncluded
	 * @return \rocket\si\content\impl\basic\CompactEntrySiGui
	 */
	function createCompactEntrySiGui(/*bool $generalSiControlsIncluded = true,*/ bool $entrySiControlsIncluded = true) {
		if (!ViewMode::isCompact($this->eiGui->getEiGuiDeclaration()->getViewMode())) {
			throw new EiuPerimeterException('EiGuiValueBoundaryMulti is not compact.');
		}
		
		return (new EiGuiUtil($this->eiGui, $this->eiuAnalyst->getEiFrame(true)))
				->createCompactEntrySiGui(/*$generalSiControlsIncluded, */$entrySiControlsIncluded);
	}
	
	/**
	 * @param bool $generalSiControlsIncluded
	 * @param bool $entrySiControlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiGui
	 */
	function createBulkyEntrySiGui(bool $generalSiControlsIncluded = true, bool $entrySiControlsIncluded = true,
			array $generalGuiControls = []) {
		if (!ViewMode::isBulky($this->eiGui->getEiGuiDeclaration()->getViewMode())) {
			throw new EiuPerimeterException('EiGuiValueBoundaryMulti is not bulky.');
		}
		
		return (new EiGuiUtil($this->eiGui, $this->eiuAnalyst->getEiFrame(true)))
				->createBulkyEntrySiGui($generalSiControlsIncluded, $entrySiControlsIncluded, $generalGuiControls);
	}
	
	/**
	 * @return \rocket\op\ei\util\gui\EiuGui
	 */
	function copy(bool $bulky, bool $readOnly, array $defPropPathsArg = null, bool $guiStructureDeclarationsRequired = true) {
		$eiGuiDeclaration = $this->eiGui->getEiGuiDeclaration();
		$viewMode = ViewMode::determine($bulky, $readOnly, ViewMode::isAdd($eiGuiDeclaration->getViewMode()));

		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);
		
		$newEiGui = new EiGui($eiGuiDeclaration->getContextEiMask()->getEiEngine()->obtainMultiEiGuiDeclaration($viewMode,
				$eiGuiDeclaration->getEiTypes(), $defPropPaths, $guiStructureDeclarationsRequired));
		
		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
		foreach ($this->eiGui->getEiGuiValueBoundaries() as $eiGuiValueBoundary) {
			$newEiGuiValueBoundary = $newEiGui->appendEiGuiValueBoundary($eiFrame, $eiGuiValueBoundary->getEiEntries(), $eiGuiValueBoundary->getTreeLevel());
			if ($eiGuiValueBoundary->isEiGuiEntrySelected()) {
				$newEiGuiValueBoundary->selectEiGuiEntryByEiMaskId($eiGuiValueBoundary->getSelectedEiGuiEntry()->getEiMask()->getEiType()->getId());
			}
		}
		
		return new EiuGui($newEiGui, null, $this->eiuAnalyst);
	}
	
}