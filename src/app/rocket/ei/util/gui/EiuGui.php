<?php
namespace rocket\ei\util\gui;

use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\util\entry\EiuObject;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\manage\gui\EiGuiUtil;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\EiGuiFactory;
use rocket\ei\manage\gui\field\GuiPropPath;

class EiuGui {
	private $eiGui;
	private $eiuGuiFrames;
	private $eiuAnalyst;
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGui $eiGui, EiuAnalyst $eiuAnalyst) {
		$this->eiGui = $eiGui;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
// 	/**
// 	 * @return \rocket\ei\util\frame\EiuFrame
// 	 */
// 	public function getEiuFrame() {
// 		if ($this->eiuFrame !== null) {
// 			return $this->eiuFrame;
// 		}
		
// 		if ($this->eiuAnalyst !== null) {
// 			$this->eiuFrame = $this->eiuAnalyst->getEiuFrame(false);
// 		}
		
// 		if ($this->eiuFrame === null) {
// 			$this->eiuFrame = new EiuFrame($this->eiGuiFrame->getEiFrame(), $this->eiuAnalyst);
// 		}
		
// 		return $this->eiuFrame;
// 	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	public function getEiGui() {
		return $this->eiGui;
	}
	
	/**
	 * @return bool
	 */
	public function isSingle() {
		return 1 == count($this->eiGuiFrame->getEiEntryGuis());
	}
	
	function createSiDeclaration() {
		return $this->eiGui->createSiDeclaration();
	}
	
	/**
	 *
	 * @param bool $required
	 * @return EiuEntryGui|null
	 */
	public function entryGui(bool $required = true) {
		$eiEntryGuis = $this->eiGui->getEiEntryGuis();
		
		if (count($eiEntryGuis) == 1) {
			return new EiuEntryGui(current($eiEntryGuis), $this, null, $this->eiuAnalyst);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No single EiuEntryGui is available.');
	}
	
	public function entryGuis() {
		$eiuEntryGuis = array();
		
		foreach ($this->eiGuiFrame->getEiEntryGuis() as $eiEntryGui) {
			$eiuEntryGuis[] = new EiuEntryGui($eiEntryGui, $this, null, $this->eiuAnalyst);
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
		
		return new EiuEntryGui($this->eiuAnalyst->getEiGuiFrame(true)
				->createEiEntryGui($eiEntry, $treeLevel, true), $this, null, $this->eiuAnalyst);
	}
	
	/**
	 * @param bool $generalSiControlsIncluded
	 * @param bool $entrySiControlsIncluded
	 * @return \rocket\si\content\impl\basic\CompactEntrySiComp
	 */
	function createCompactEntrySiComp(bool $generalSiControlsIncluded = true, bool $entrySiControlsIncluded = true) {
		if (!ViewMode::isCompact($this->eiGui->getViewMode())) {
			throw new EiuPerimeterException('EiEntryGuiMulti is not compact.');
		}
		
		return (new EiGuiUtil($this->getEiGui(), $this->eiuAnalyst->getEiFrame(true)))
				->createCompactEntrySiComp($generalSiControlsIncluded, $entrySiControlsIncluded);
	}
	
	/**
	 * @param bool $generalSiControlsIncluded
	 * @param bool $entrySiControlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiComp
	 */
	function createBulkyEntrySiComp(bool $generalSiControlsIncluded = true, bool $entrySiControlsIncluded = true) {
		if (!ViewMode::isBulky($this->eiGui->getViewMode())) {
			throw new EiuPerimeterException('EiEntryGuiMulti is not bulky.');
		}
		
		return (new EiGuiUtil($this->getEiGui(), $this->eiuAnalyst->getEiFrame(true)))
				->createBulkyEntrySiComp($generalSiControlsIncluded, $entrySiControlsIncluded);
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	function copy(bool $bulky, bool $readOnly, array $guiPropPathsArg = null, bool $guiStructureDeclarationsRequired = true) {
		$viewMode = ViewMode::determine($bulky, $readOnly, ViewMode::isAdd($this->eiGui->getViewMode()));
		$factory = new EiGuiFactory($this->eiuAnalyst->getN2nContext(true));
		$guiPropPaths = GuiPropPath::buildArray($guiPropPathsArg);
		
		$factory = new EiGuiFactory($this->eiuAnalyst->getN2nContext(true));
		$newEiGui = $factory->createMultiEiGui($this->eiGui->getContextEiMask(), $viewMode, $this->eiGui->getEiTypes(), $guiPropPaths, 
				$guiStructureDeclarationsRequired);
		
		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
		foreach ($this->eiGui->getEiEntryGuis() as $eiEntryGui) {
			$newEiEntryGui = $newEiGui->appendEiEntryGui($eiFrame, $eiEntryGui->getEiEntries(), $eiEntryGui->getTreeLevel());
			if ($eiEntryGui->isTypeDefSelected()) {
				$newEiEntryGui->selectTypeDefByEiTypeId($eiEntryGui->getSelectedTypeDef()->getEiType()->getId());
			}
		}
		
		return new EiuGui($newEiGui, $this->eiuAnalyst);
	}
	
}