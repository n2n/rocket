<?php
namespace rocket\op\ei\util\gui;

use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\manage\gui\EiGuiDeclaration;
use rocket\op\ei\util\entry\EiuObject;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\util\EiuPerimeterException;
use rocket\op\ei\manage\gui\EiGuiUtil;
use rocket\op\ei\manage\gui\ViewMode;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\EiGui;

class EiuGuiModel {
	private $eiGuiDeclaration;
	private $eiuGuiFrames;
	private $eiuAnalyst;
	
	/**
	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGuiDeclaration $eiGuiDeclaration, EiuAnalyst $eiuAnalyst) {
		$this->eiGuiDeclaration = $eiGuiDeclaration;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
// 	/**
// 	 * @return \rocket\op\ei\util\frame\EiuFrame
// 	 */
// 	public function getEiuFrame() {
// 		if ($this->eiuFrame !== null) {
// 			return $this->eiuFrame;
// 		}
		
// 		if ($this->eiuAnalyst !== null) {
// 			$this->eiuFrame = $this->eiuAnalyst->getEiuFrame(false);
// 		}
		
// 		if ($this->eiuFrame === null) {
// 			$this->eiuFrame = new EiuFrame($this->eiGuiMaskDeclaration->getEiFrame(), $this->eiuAnalyst);
// 		}
		
// 		return $this->eiuFrame;
// 	}
	
	/**
	 * @return \rocket\op\ei\manage\gui\EiGuiDeclaration
	 */
	function getEiGuiDeclaration() {
		return $this->eiGuiDeclaration;
	}
	
	function newGui() {
		return new EiuGui(new EiGui($this->eiGuiDeclaration), $this, $this->eiuAnalyst);
	}
	
	function createSiDeclaration() {
		return $this->eiGuiDeclaration->createSiDeclaration();
	}
	
	/**
	 * @return \rocket\si\control\SiControl[]
	 */
	function createGeneralSiControls() {
		return $this->eiGuiDeclaration->createGeneralSiControls($this->eiuAnalyst->getEiFrame(true));
	}
	
	function guiFrames() {
		if ($this->eiuGuiFrames !== null) {
			return $this->eiuGuiFrames;
		}
		
		$this->eiuGuiFrames = [];
		foreach ($this->eiGuiDeclaration->getEiGuiMaskDeclarations() as $key => $eiGuiMaskDeclaration) {
			$this->eiuGuiFrames[$key] = new EiuGuiFrame($eiGuiMaskDeclaration, $this, $this->eiuAnalyst);
		}
		return $this->eiuGuiFrames;
	}
	
//	/**
//	 * @return \rocket\op\ei\util\gui\EiuGui
//	 */
//	function copy(bool $bulky, bool $readOnly, array $defPropPathsArg = null, bool $guiStructureDeclarationsRequired = true) {
//		$viewMode = ViewMode::determine($bulky, $readOnly, ViewMode::isAdd($this->eiGuiDeclaration->getViewMode()));
//		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);
//
//		$newEiGuiDeclaration = $cache->createMultiEiGuiDeclaration($this->eiGuiDeclaration->getContextEiMask(), $viewMode,
//				$this->eiGuiDeclaration->getEiTypes(), $defPropPaths);
//
//		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
//		foreach ($this->eiGuiDeclaration->getEiGuiValueBoundaries() as $eiGuiValueBoundary) {
//			$newEiGuiValueBoundary = $newEiGuiDeclaration->appendEiGuiValueBoundary($eiFrame, $eiGuiValueBoundary->getEiEntries(), $eiGuiValueBoundary->getTreeLevel());
//			if ($eiGuiValueBoundary->isTypeDefSelected()) {
//				$newEiGuiValueBoundary->selectTypeDefByEiTypeId($eiGuiValueBoundary->getSelectedTypeDef()->getEiType()->getId());
//			}
//		}
//
//		return new EiuGuiModel($newEiGuiDeclaration, $this->eiuAnalyst);
//	}
	
	function newEntryGui($eiEntryArg = null) {
		$eiGui = new EiGui($this->eiGuiDeclaration);
		
		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
		if ($eiEntryArg === null) {
			$eiGui->appendNewEiGuiValueBoundary($eiFrame);
		} else {
			$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg, 'eiEntryArg', true);
			$eiGui->appendEiGuiValueBoundary($eiFrame, [$eiEntry]);
		}
		
		return (new EiuGui($eiGui, $this, $this->eiuAnalyst))->entryGui();
	}
}