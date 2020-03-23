<?php
namespace rocket\ei\util\gui;

use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\util\entry\EiuObject;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\manage\gui\EiGuiUtil;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\EiGuiModelFactory;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\manage\gui\EiGui;

class EiuGui {
	private $eiGui;
	private $eiuGuiModel;
	private $eiuAnalyst;
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGui $eiGui, ?EiuGuiModel $eiuGuiModel, EiuAnalyst $eiuAnalyst) {
		$this->eiGui = $eiGui;
		$this->eiuGuiModel = $eiuGuiModel;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	public function getEiGui() {
		return $this->eiGui;
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuGuiModel
	 */
	function model() {
		if ($this->eiuGuiModel === null) {
			$this->eiuGuiModel = new EiuGuiModel($this->eiGuiModel, $this->eiuAnalyst);
		}
		
		return $this->eiuGuiModel;
	}
	
	/**
	 *
	 * @param bool $required
	 * @return EiuEntryGui|null
	 */
	public function entryGui(bool $required = true) {
		$eiEntryGuis = $this->eiGui->getEiEntryGuis();
		
		if (count($eiEntryGuis) == 1) {
			return new EiuEntryGui(current($eiEntryGuis), $this, $this->eiuAnalyst);
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
		
// 		return new EiuEntryGui($this->eiuAnalyst->getEiGuiFrame(true)
// 				->createEiEntryGui($eiEntry, $treeLevel, true), $this, null, $this->eiuAnalyst);
// 	}
	
	/**
	 * @param bool $entrySiControlsIncluded
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry(bool $entrySiControlsIncluded = true) {
		return $this->eiGui->createSiEntry($this->eiuAnalyst->getEiFrame(true), $entrySiControlsIncluded);
	}
	
	/**
	 * @param bool $generalSiControlsIncluded
	 * @param bool $entrySiControlsIncluded
	 * @return \rocket\si\content\impl\basic\CompactEntrySiComp
	 */
	function createCompactEntrySiComp(/*bool $generalSiControlsIncluded = true,*/ bool $entrySiControlsIncluded = true) {
		if (!ViewMode::isCompact($this->eiGui->getViewMode())) {
			throw new EiuPerimeterException('EiEntryGuiMulti is not compact.');
		}
		
		return (new EiGuiUtil($this->getEiGuiModel(), $this->eiuAnalyst->getEiFrame(true)))
				->createCompactEntrySiComp(/*$generalSiControlsIncluded, */$entrySiControlsIncluded);
	}
	
	/**
	 * @param bool $generalSiControlsIncluded
	 * @param bool $entrySiControlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiComp
	 */
	function createBulkyEntrySiComp(/*bool $generalSiControlsIncluded = true,*/ bool $entrySiControlsIncluded = true) {
		if (!ViewMode::isBulky($this->eiGui->getViewMode())) {
			throw new EiuPerimeterException('EiEntryGuiMulti is not bulky.');
		}
		
		return (new EiGuiUtil($this->getEiGuiModel(), $this->eiuAnalyst->getEiFrame(true)))
				->createBulkyEntrySiComp(/*$generalSiControlsIncluded,*/ $entrySiControlsIncluded);
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	function copy(bool $bulky, bool $readOnly, array $guiPropPathsArg = null, bool $guiStructureDeclarationsRequired = true) {
		$viewMode = ViewMode::determine($bulky, $readOnly, ViewMode::isAdd($this->eiGui->getViewMode()));
		$factory = new EiGuiModelFactory($this->eiuAnalyst->getN2nContext(true));
		$guiPropPaths = GuiPropPath::buildArray($guiPropPathsArg);
		
		$factory = new EiGuiModelFactory($this->eiuAnalyst->getN2nContext(true));
		$newEiGuiModel = $factory->createMultiEiGuiModel($this->eiGui->getContextEiMask(), $viewMode, $this->eiGui->getEiTypes(), $guiPropPaths, 
				$guiStructureDeclarationsRequired);
		
		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
		foreach ($this->eiGui->getEiEntryGuis() as $eiEntryGui) {
			$newEiEntryGui = $newEiGuiModel->appendEiEntryGui($eiFrame, $eiEntryGui->getEiEntries(), $eiEntryGui->getTreeLevel());
			if ($eiEntryGui->isTypeDefSelected()) {
				$newEiEntryGui->selectTypeDefByEiTypeId($eiEntryGui->getSelectedTypeDef()->getEiType()->getId());
			}
		}
		
		return new EiuGui($newEiGuiModel, $this->eiuAnalyst);
	}
	
}