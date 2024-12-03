<?php
namespace rocket\op\ei\util\gui;

use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use rocket\op\ei\util\EiuAnalyst;
use rocket\ui\gui\EiGuiDeclaration;
use rocket\ui\si\meta\SiDeclaration;

class EiuGuiDeclaration  {
	private $eiuGuiMaskDeclarations;
	private $eiuAnalyst;

	/**
	 * @param EiGuiDeclaration $eiGuiDeclaration
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(private EiGuiDeclaration $eiGuiDeclaration, EiuAnalyst $eiuAnalyst) {
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
	

	function getEiGuiDeclaration(): EiGuiDeclaration {
		return $this->eiGuiDeclaration;
	}
	
//	function createSiDeclaration() {
//		return $this->eiGuiDeclaration->createSiDeclaration();
//	}
//
//	/**
//	 * @return \rocket\si\control\SiControl[]
//	 */
//	function createGeneralSiControls() {
//		return $this->eiGuiDeclaration->createGeneralSiControls($this->eiuAnalyst->getEiFrame(true));
//	}

	/**
	 * @return EiuGuiMaskDeclaration[]
	 */
	function maskDeclarations(): array {
		if ($this->eiuGuiMaskDeclarations !== null) {
			return $this->eiuGuiMaskDeclarations;
		}
		
		$this->eiuGuiMaskDeclarations = [];
		foreach ($this->eiGuiDeclaration->getEiGuiMaskDeclarations() as $key => $eiGuiMaskDeclaration) {
			$this->eiuGuiMaskDeclarations[$key] = new EiuGuiMaskDeclaration($eiGuiMaskDeclaration, $this->eiuAnalyst);
		}
		return $this->eiuGuiMaskDeclarations;
	}

	function singleMaskDeclaration(): EiuGuiMaskDeclaration {
		return new EiuGuiMaskDeclaration($this->eiGuiDeclaration->getSingleEiGuiMaskDeclaration(), $this->eiuAnalyst);
	}
//	/**
//	 * @return \rocket\op\ei\util\gui\EiuGui
//	 */
//	function copy(bool $bulky, bool $readOnly, ?array $defPropPathsArg = null, bool $guiStructureDeclarationsRequired = true) {
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
//		return new EiuGuiDeclaration ($newEiGuiDeclaration, $this->eiuAnalyst);
//	}
	
	function newGuiValueBoundary(?array $eiEntryArgs = null, bool $entryGuiControlsIncluded = false): EiuGuiValueBoundary {
		$eiEntries = null;
		if ($eiEntryArgs !== null) {
			$eiEntries = array_map(fn($a) => EiuAnalyst::buildEiEntryFromEiArg($a, 'eiEntryArg', true),
					$eiEntryArgs);
		}
		
		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
		if ($eiEntries === null) {
			$eiValueBoundary = $this->eiGuiDeclaration->createNewEiGuiValueBoundary($eiFrame, $entryGuiControlsIncluded);
		} else {
			$eiValueBoundary = $this->eiGuiDeclaration->createEiGuiValueBoundary($eiFrame, $eiEntries, $entryGuiControlsIncluded);
		}

		return new EiuGuiValueBoundary($eiValueBoundary, $this, $this->eiuAnalyst);
	}

	function createSiDeclaration(): SiDeclaration {
		return $this->eiGuiDeclaration
				->createSiDeclaration($this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
}