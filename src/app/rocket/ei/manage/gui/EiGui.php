<?php
namespace rocket\ei\manage\gui;

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\entry\EiEntry;
use n2n\util\type\ArgUtils;
use rocket\ei\component\GuiFactory;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use rocket\ei\manage\gui\control\GeneralGuiControl;
use rocket\ei\manage\api\ApiControlCallId;

/**
 * @author andreas
 *
 */
class EiGui {
	/**
	 * @var EiFrame
	 */
	private $eiFrame;
	/**
	 * @var GuiDefinition
	 */
	private $guiDefinition;
// 	/**
// 	 * @var GuiDefinition
// 	 */
// 	private $guiDefinition;
	/**
	 * @var int
	 */
	private $viewMode;
	/**
	 * @var GuiStructureDeclaration[]|null
	 */
	private $guiStructureDeclarations = null;
	/**
	 * @var GuiFieldPath[]|null
	 */
	private $guiFieldPaths = null;
	/**
	 * @var EiGuiListener[]
	 */
	private $eiGuiListeners = array();
	/**
	 * @var EiEntryGui[]
	 */
	private $eiEntryGuis = array();
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiDefinition $guiDefinition
	 * @param int $viewMode Use constants from {@see ViewMode}
	 */
	public function __construct(EiFrame $eiFrame, GuiDefinition $guiDefinition, int $viewMode, array $guiFieldPaths) {
		$this->eiFrame = $eiFrame;
		$this->guiDefinition = $guiDefinition;
// 		$this->guiDefinition = $guiDefinition;
		ArgUtils::valEnum($viewMode, ViewMode::getAll());
		$this->viewMode = $viewMode;
		$this->guiFieldPaths = $guiFieldPaths;
// 		$this->eiGuiNature = new EiGuiNature();
	}
	
	/**
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	public function getEiFrame() {
		return $this->eiFrame;
	}
	
	/**
	 * @return \rocket\ei\manage\gui$\GuiDefinition
	 */
	public function getGuiDefinition() {
		return $this->guiDefinition;
	}
	
	/**
	 * @return int
	 */
	public function getViewMode() {
		return $this->viewMode;
	}
	
	private $rootEiPropPaths = [];
	
	function getRootEiPropPaths() {
		if ($this->rootEiPropPaths !== null) {
			return $this->rootEiPropPaths;
		}
		
		$this->rootEiPropPaths = [];
		foreach ($this->guiFieldPaths as $guiFieldPath) {
			$eiPropPath = $guiFieldPath->getFirstEiPropPath();
			$this->rootEiPropPaths[(string) $eiPropPath] = $eiPropPath;
		}
		return $this->rootEiPropPaths;
	}
	
	
	/**
	 * @return boolean
	 */
	function hasMultipleEiEntryGuis() {
		return count($this->eiEntryGuis) > 1;
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param bool $makeEditable
	 * @param int $treeLevel
	 * @param bool $append
	 * @return EiEntryGui
	 */
	function createEiEntryGui(EiEntry $eiEntry, int $treeLevel = null, bool $append = true): EiEntryGui {
		$this->ensureInit();
		
		$eiEntryGui = GuiFactory::createEiEntryGui($this, $eiEntry, $this->getGuiFieldPaths(), $treeLevel);
		if ($append) {
			$this->eiEntryGuis[] = $eiEntryGui;
		}
		
		foreach ($this->eiGuiListeners as $eiGuiListener) {
			$eiGuiListener->onNewEiEntryGui($eiEntryGui);
		}
		
		return $eiEntryGui;
	}
	
	/**
	 * @return EiEntryGui[]
	 */
	function getEiEntryGuis() {
		return $this->eiEntryGuis;
	}
	
	/**
	 * @return \rocket\si\control\SiControl[]
	 */
	function createSelectionSiControls() {
		$siControls = [];
		foreach ($this->guiDefinition->createSelectionGuiControls($this)
				as $guiControlPathStr => $selectionGuiControl) {
			$siControls[$guiControlPathStr] = $selectionGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr), $this->eiMask->getEiTypePath(),
							$this->viewMode, null));
		}
		return $siControls;
	}
	
	/**
	 * @return \rocket\si\control\SiControl[]
	 */
	function createGeneralSiControls() {
		$siControls = [];
		foreach ($this->guiDefinition->createGeneralGuiControls($this)
				as $guiControlPathStr => $generalGuiControl) {
			$siControls[$guiControlPathStr] = $generalGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr), 
							$this->guiDefinition->getEiMask()->getEiTypePath(),
							$this->viewMode, null));
		}
		return $siControls;
	}
	
	/**
	 * @param GuiControlPath $guiControlPath
	 * @return GeneralGuiControl
	 * @throws UnknownGuiControlException
	 */
	public function createGeneralGuiControl(GuiControlPath $guiControlPath) {
		return $this->guiDefinition->createGeneralGuiControl($this, $guiControlPath);
	}
	
	
	/**
	 * @param EiGuiListener $eiGuiListener
	 */
	public function registerEiGuiListener(EiGuiListener $eiGuiListener) {
		$this->eiGuiListeners[spl_object_hash($eiGuiListener)] = $eiGuiListener;
	}
	
	/**
	 * @param EiGuiListener $eiGuiListener
	 */
	public function unregisterEiGuiListener(EiGuiListener $eiGuiListener) {
		unset($this->eiGuiListeners[spl_object_hash($eiGuiListener)]);
	}
	
// 	/**
// 	 * @return \rocket\ei\manage\gui\EiGuiNature
// 	 */
// 	public function getEiGuiNature()  {
// 		return $this->eiGuiNature;
// 	}
	
	/**
	 * @return \rocket\si\content\SiEntry[]
	 */
	public function createSiEntries() {
		$siEntries = [];
		foreach ($this->eiEntryGuis as $eiEntryGui) {
			$siEntries[] = $eiEntryGui->createSiEntry();
		}
		return $siEntries;
	}
}

// class GuiPropAssembly {
// 	private $guiFieldPath;
// 	private $displayDefinition;
	
// 	/**
// 	 * @param GuiProp $guiProp
// 	 * @param GuiFieldPath $guiFieldPath
// 	 * @param DisplayDefinition $displayDefinition
// 	 */
// 	public function __construct(GuiFieldPath $guiFieldPath, DisplayDefinition $displayDefinition) {
// 		$this->guiFieldPath = $guiFieldPath;
// 		$this->displayDefinition = $displayDefinition;
// 	}
	
// // 	/**
// // 	 * @return \rocket\ei\manage\gui\GuiProp
// // 	 */
// // 	public function getGuiProp() {
// // 		return $this->guiProp;
// // 	}
	
// 	/**
// 	 * @return \rocket\ei\manage\gui\field\GuiFieldPath
// 	 */
// 	public function getGuiFieldPath() {
// 		return $this->guiFieldPath;
// 	}
	
// 	/**
// 	 * @return \rocket\ei\manage\gui\DisplayDefinition
// 	 */
// 	public function getDisplayDefinition() {
// 		return $this->displayDefinition;
// 	}
// }


// class EiGuiNature {
// 	private $entryControlsRendered = false;
// 	private $collectionForced = false;
// // 	private $forkControlsRendered = true;
	
// 	/**
// 	 * @return bool
// 	 */
// 	function areEntryGuiControlsRendered() {
// 		return $this->entryControlsRendered;
// 	}
	
// 	/**
// 	 * @param bool $entryControlsRendered
// 	 * @return \rocket\ei\manage\gui\EiGuiNature
// 	 */
// 	function setEntryGuiControlsRendered(bool $entryControlsRendered) {
// 		$this->entryControlsRendered = $entryControlsRendered;
// 		return $this;
// 	}
// 	/**
//  	 * @return bool
//  	 */
// 	function isCollectionForced() {
// 		return $this->collectionForced;
// 	}

// 	/**
// 	 * @param bool $forkControlsRedenered
// 	 * @return \rocket\ei\manage\gui\EiGuiNature
// 	 */
// 	function setCollectionForced(bool $collectionForced) {
// 		$this->collectionForced = $collectionForced;
// 		return $this;
// 	}
// // 	/**
// // 	 * @return bool
// // 	 */
// // 	function areForkControlsRendered() {
// // 		return $this->forkControlsRendered;
// // 	}
	
// // 	/**
// // 	 * @param bool $forkControlsRedenered
// // 	 * @return \rocket\ei\manage\gui\EiGuiNature
// // 	 */
// // 	function setForkControlsRendered(bool $forkControlsRedenered) {
// // 		$this->forkControlsRendered = $forkControlsRedenered;
// // 		return $this;
// // 	}
// }