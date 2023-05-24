<?php
//namespace rocket\op\ei\util\gui;
//
//use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
//use rocket\op\ei\util\frame\EiuFrame;
//use rocket\op\ei\util\EiuAnalyst;
//use rocket\op\ei\util\EiuPerimeterException;
//use rocket\op\ei\manage\gui\EiGuiUtil;
//use rocket\op\ei\manage\gui\ViewMode;
//use rocket\op\ei\manage\DefPropPath;
//use rocket\op\ei\manage\gui\EiGui;
//
//class EiuGui {
//	private $eiGui;
//	private $eiuGuiDeclaration ;
//	private $eiuAnalyst;
//
//	/**
//	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
//	 * @param EiuFrame $eiuFrame
//	 * @param EiuAnalyst $eiuAnalyst
//	 */
//	public function __construct(EiGui $eiGui, ?EiuGuiDeclaration  $eiuGuiDeclaration , EiuAnalyst $eiuAnalyst) {
//		$this->eiGui = $eiGui;
//		$this->eiuGuiDeclaration  = $eiuGuiDeclaration ;
//		$this->eiuAnalyst = $eiuAnalyst;
//	}
//
//	/**
//	 * @return \rocket\op\ei\manage\gui\EiGui
//	 */
//	public function getEiGui() {
//		return $this->eiGui;
//	}
//
//	/**
//	 * @return boolean
//	 */
//	function isCompact() {
//		return ViewMode::isCompact($this->eiGui->getEiGuiDeclaration()->getViewMode());
//	}
//
//	/**
//	 * @return boolean
//	 */
//	function isBulky() {
//		return ViewMode::isBulky($this->eiGui->getEiGuiDeclaration()->getViewMode());
//	}
//
//	/**
//	 * @return \rocket\op\ei\util\gui\EiuGuiDeclaration
//	 */
//	function guiModel() {
//		if ($this->eiuGuiDeclaration  === null) {
//			$this->eiuGuiDeclaration  = new EiuGuiDeclaration ($this->eiGui->getEiGuiDeclaration(), $this->eiuAnalyst);
//		}
//
//		return $this->eiuGuiDeclaration ;
//	}
//
//	/**
//	 *
//	 * @param bool $required
//	 * @return EiuGuiEntry|null
//	 */
//	public function entryGui(bool $required = true) {
//		$eiGuiValueBoundaries = $this->eiGui->getEiGuiValueBoundaries();
//
//		if (count($eiGuiValueBoundaries) == 1) {
//			return new EiuGuiEntry(current($eiGuiValueBoundaries), $this, $this->eiuAnalyst);
//		}
//
//		if (!$required) return null;
//
//		throw new EiuPerimeterException('No single EiuGuiEntry is available.');
//	}
//
//	public function entryGuis() {
//		$eiuGuiEntrys = array();
//
//		foreach ($this->eiGuiMaskDeclaration->getEiGuiValueBoundaries() as $eiGuiValueBoundary) {
//			$eiuGuiEntrys[] = new EiuGuiEntry($eiGuiValueBoundary, $this, null, $this->eiuAnalyst);
//		}
//
//		return $eiuGuiEntrys;
//	}
//
//// 	/**
//// 	 *
//// 	 * @param mixed $eiEntryArg
//// 	 * @param bool $makeEditable
//// 	 * @param int $treeLevel
//// 	 * @return EiuGuiEntry
//// 	 */
//// 	public function appendNewEntryGui($eiEntryArg, int $treeLevel = null) {
//// 		$eiEntry = null;
//// 		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiEntryArg, 'eiEntryArg', $this->eiuFrame->getContextEiType(), true,
//// 				$eiEntry);
//
//// 		if ($eiEntry === null) {
//// 			$eiEntry = (new EiuEntry(null, new EiuObject($eiObject, $this->eiuAnalyst),
//// 					null, $this->eiuAnalyst))->getEiEntry(true);
//// 		}
//
//// 		return new EiuGuiEntry($this->eiuAnalyst->getEiGuiMaskDeclaration(true)
//// 				->createEiGuiValueBoundary($eiEntry, $treeLevel, true), $this, null, $this->eiuAnalyst);
//// 	}
//
//	/**
//	 * @param bool $entrySiControlsIncluded
//	 * @return \rocket\si\content\SiValueBoundary
//	 */
//	function createSiEntry(bool $entrySiControlsIncluded = true) {
//		return $this->eiGui->createSiEntry($this->eiuAnalyst->getEiFrame(true), $entrySiControlsIncluded);
//	}
//
//	/**
//	 * @param bool $generalSiControlsIncluded
//	 * @param bool $entrySiControlsIncluded
//	 * @return \rocket\si\content\impl\basic\CompactEntrySiGui
//	 */
//	function createCompactEntrySiGui(/*bool $generalSiControlsIncluded = true,*/ bool $entrySiControlsIncluded = true) {
//		if (!ViewMode::isCompact($this->eiGui->getEiGuiDeclaration()->getViewMode())) {
//			throw new EiuPerimeterException('EiGuiValueBoundaryMulti is not compact.');
//		}
//
//		return (new EiGuiUtil($this->eiGui, $this->eiuAnalyst->getEiFrame(true)))
//				->createCompactEntrySiGui(/*$generalSiControlsIncluded, */$entrySiControlsIncluded);
//	}
//
//	/**
//	 * @param bool $generalSiControlsIncluded
//	 * @param bool $entrySiControlsIncluded
//	 * @return \rocket\si\content\impl\basic\BulkyEntrySiGui
//	 */
//	function createBulkyEntrySiGui(bool $generalSiControlsIncluded = true, bool $entrySiControlsIncluded = true,
//			array $generalGuiControls = []) {
//		if (!ViewMode::isBulky($this->eiGui->getEiGuiDeclaration()->getViewMode())) {
//			throw new EiuPerimeterException('EiGuiValueBoundaryMulti is not bulky.');
//		}
//
//		return (new EiGuiUtil($this->eiGui, $this->eiuAnalyst->getEiFrame(true)))
//				->createBulkyEntrySiGui($generalSiControlsIncluded, $entrySiControlsIncluded, $generalGuiControls);
//	}
//
//	/**
//	 * @return \rocket\op\ei\util\gui\EiuGui
//	 */
//	function copy(bool $bulky, bool $readOnly, array $defPropPathsArg = null, bool $guiStructureDeclarationsRequired = true) {
//		$eiGuiDeclaration = $this->eiGui->getEiGuiDeclaration();
//		$viewMode = ViewMode::determine($bulky, $readOnly, ViewMode::isAdd($eiGuiDeclaration->getViewMode()));
//
//		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);
//
//		$newEiGui = new EiGui($eiGuiDeclaration->getContextEiMask()->getEiEngine()->obtainMultiEiGuiDeclaration($viewMode,
//				$eiGuiDeclaration->getEiTypes(), $defPropPaths, $guiStructureDeclarationsRequired));
//
//		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
//		foreach ($this->eiGui->getEiGuiValueBoundaries() as $eiGuiValueBoundary) {
//			$newEiGuiValueBoundary = $newEiGui->appendEiGuiValueBoundary($eiFrame, $eiGuiValueBoundary->getEiEntries(), $eiGuiValueBoundary->getTreeLevel());
//			if ($eiGuiValueBoundary->isEiGuiEntrySelected()) {
//				$newEiGuiValueBoundary->selectEiGuiEntryByEiMaskId($eiGuiValueBoundary->getSelectedEiGuiEntry()->getEiMask()->getEiType()->getId());
//			}
//		}
//
//		return new EiuGui($newEiGui, null, $this->eiuAnalyst);
//	}
//
//}