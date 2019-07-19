<?php
namespace rocket\ei\manage\gui;

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\entry\EiEntry;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\GuiFactory;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use rocket\ei\manage\gui\control\GeneralGuiControl;
use rocket\ei\manage\api\ApiControlCallId;
use rocket\ei\mask\EiMask;
use rocket\si\structure\SiBulkyDeclaration;
use rocket\ei\EiException;
use rocket\si\structure\SiCompactDeclaration;

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
	 * @var EiMask
	 */
	private $eiMask;
	/**
	 * @var GuiDefinition
	 */
	private $guiDefinition;
	/**
	 * @var int
	 */
	private $viewMode;
	/**
	 * @return GuiPropAssembly[]
	 */
	private $guiPropAssemblies;
	/**
	 * @var GuiFieldPath[]
	 */
	private $guiFieldPaths;
	/**
	 * @var EiGuiSiFactory
	 */
	private $eiGuiGiFactory;
	/**
	 * @var DisplayDefinition[]
	 */
	private $displayDefinitions = array();
	/**
	 * @var EiGuiListener[]
	 */
	private $eiGuiListeners = array();
	/**
	 * @var EiEntryGui[]
	 */
	private $eiEntryGuis = array();
// 	/**
// 	 * @var EiGuiNature
// 	 */
// 	private $eiGuiNature;
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiDefinition $guiDefinition
	 * @param int $viewMode Use constants from {@see ViewMode}
	 */
	public function __construct(EiFrame $eiFrame, EiMask $eiMask, GuiDefinition $guiDefinition, int $viewMode) {
		$this->eiFrame = $eiFrame;
		$this->eiMask = $eiMask;
		$this->guiDefinition = $guiDefinition;
		ArgUtils::valEnum($viewMode, ViewMode::getAll());
		$this->viewMode = $viewMode;
// 		$this->eiGuiNature = new EiGuiNature();
	}
	
	/**
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	public function getEiFrame() {
		return $this->eiFrame;
	}
	
	/**
	 * @return EiMask
	 */
	public function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\GuiDefinition
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
	
	/**
	 * @return GuiPropAssembly[]
	 */
	public function getGuiPropAssemblies() {
		$this->ensureInit();
		return $this->guiPropAssemblies;
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return bool
	 */
	public function containsGuiFieldPath(GuiFieldPath $guiFieldPath) {
		return isset($this->guiPropAssemblies[(string) $guiFieldPath]);
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @throws GuiException
	 * @return \rocket\ei\manage\gui\GuiPropAssembly
	 */
	public function getGuiPropAssemblyByGuiFieldPath(GuiFieldPath $guiFieldPath) {
		$guiFieldPathStr = (string) $guiFieldPath;
		
		if (isset($this->guiPropAssemblies[$guiFieldPathStr])) {
			return $this->guiPropAssemblies[$guiFieldPathStr];
		}
		
		throw new GuiException('No GuiPropAssembly for GuiFieldPath available: ' . $guiFieldPathStr);
	}
	
	/**
	 * @param EiGuiSiFactory $eiGuiGiFactory
	 * @param GuiFieldPath[]
	 */
	public function init(EiGuiSiFactory $eiGuiGiFactory, array $guiFieldPaths = null) {
		if ($this->eiGuiGiFactory !== null) {
			throw new IllegalStateException('EiGui already initialized.');
		}
		
		$this->eiGuiGiFactory = $eiGuiGiFactory;
		if ($guiFieldPaths === null) {
			$this->guiPropAssemblies = $this->guiDefinition->assembleDefaultGuiProps($this);
		} else {
			$this->guiPropAssemblies = $this->guiDefinition->assembleGuiProps($this, $guiFieldPaths);
		}
		
		$this->guiFieldPaths = array();
		foreach ($this->guiPropAssemblies as $key => $guiPropAssembly) {
			$this->guiFieldPaths[$key] = $guiPropAssembly->getGuiFieldPath();
		}
		
		foreach ($this->eiGuiListeners as $listener) {
			$listener->onInitialized($this);
		}
	}
	
	/**
	 * @return boolean
	 */
	public function isInit() {
		return $this->eiGuiGiFactory !== null;
	}
	
	/**
	 * @throws IllegalStateException
	 */
	private function ensureInit() {
		if ($this->eiGuiGiFactory !== null) return;
		
		throw new IllegalStateException('EiGui not yet initialized.');
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiSiFactory
	 * @throws IllegalStateException If EiGui {@see self::init()} hasn't been called yet.
	 */
	public function getEiGuiSiFactory() {
		$this->ensureInit();
		
		return $this->eiGuiGiFactory;
	}
	
	/**
	 * @return boolean
	 */
	public function hasMultipleEiEntryGuis() {
		return count($this->eiEntryGuis) > 1;
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param bool $makeEditable
	 * @param int $treeLevel
	 * @param bool $append
	 * @return EiEntryGui
	 */
	public function createEiEntryGui(EiEntry $eiEntry, int $treeLevel = null, bool $append = true): EiEntryGui {
		$this->ensureInit();
		
		$eiEntryGui = GuiFactory::createEiEntryGui($this, $eiEntry, $this->guiFieldPaths, $treeLevel);
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
	public function getEiEntryGuis() {
		return $this->eiEntryGuis;
	}
	
	public function createSelectionSiControls() {
		$siControls = [];
		foreach ($this->guiDefinition->createSelectionGuiControls($this)
				as $guiControlPathStr => $selectionGuiControl) {
			$siControls[$guiControlPathStr] = $selectionGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr), $this->eiMask->getEiTypePath(),
							$this->viewMode, null));
		}
		return $siControls;
	}
	
	public function createGeneralSiControls() {
		$siControls = [];
		foreach ($this->guiDefinition->createGeneralGuiControls($this)
				as $guiControlPathStr => $generalGuiControl) {
			$siControls[$guiControlPathStr] = $generalGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr), $this->eiMask->getEiTypePath(),
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
	 * @throws EiException
	 * @return \rocket\si\structure\SiBulkyDeclaration
	 */
	public function createSiBulkyDeclaration() {
		if (ViewMode::isBulky($this->viewMode)) {
			return new SiBulkyDeclaration($this->eiGuiGiFactory->getSiFieldStructureDeclarations());
		}
		
		throw new EiException('EiGui is not bulky.');
	}
	
	/**
	 * @throws EiException
	 * @return \rocket\si\structure\SiBulkyDeclaration
	 */
	public function createSiCompactDeclaration() {
		if (ViewMode::isCompact($this->viewMode)) {
			return new SiCompactDeclaration($this->eiGuiGiFactory->getSiFieldDeclarations());
		}
		
		throw new EiException('EiGui is not bulky.');
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
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiNature
	 */
	public function getEiGuiNature()  {
		return $this->eiGuiNature;
	}
	
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

class GuiPropAssembly {
	private $guiFieldPath;
	private $displayDefinition;
	
	/**
	 * @param GuiProp $guiProp
	 * @param GuiFieldPath $guiFieldPath
	 * @param DisplayDefinition $displayDefinition
	 */
	public function __construct(GuiFieldPath $guiFieldPath, DisplayDefinition $displayDefinition) {
		$this->guiFieldPath = $guiFieldPath;
		$this->displayDefinition = $displayDefinition;
	}
	
// 	/**
// 	 * @return \rocket\ei\manage\gui\GuiProp
// 	 */
// 	public function getGuiProp() {
// 		return $this->guiProp;
// 	}
	
	/**
	 * @return \rocket\ei\manage\gui\field\GuiFieldPath
	 */
	public function getGuiFieldPath() {
		return $this->guiFieldPath;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\DisplayDefinition
	 */
	public function getDisplayDefinition() {
		return $this->displayDefinition;
	}
}


class EiGuiNature {
	private $entryControlsRendered = false;
	private $collectionForced = false;
// 	private $forkControlsRendered = true;
	
	/**
	 * @return bool
	 */
	function areEntryGuiControlsRendered() {
		return $this->entryControlsRendered;
	}
	
	/**
	 * @param bool $entryControlsRendered
	 * @return \rocket\ei\manage\gui\EiGuiNature
	 */
	function setEntryGuiControlsRendered(bool $entryControlsRendered) {
		$this->entryControlsRendered = $entryControlsRendered;
		return $this;
	}
	/**
 	 * @return bool
 	 */
	function isCollectionForced() {
		return $this->collectionForced;
	}

	/**
	 * @param bool $forkControlsRedenered
	 * @return \rocket\ei\manage\gui\EiGuiNature
	 */
	function setCollectionForced(bool $collectionForced) {
		$this->collectionForced = $collectionForced;
		return $this;
	}
// 	/**
// 	 * @return bool
// 	 */
// 	function areForkControlsRendered() {
// 		return $this->forkControlsRendered;
// 	}
	
// 	/**
// 	 * @param bool $forkControlsRedenered
// 	 * @return \rocket\ei\manage\gui\EiGuiNature
// 	 */
// 	function setForkControlsRendered(bool $forkControlsRedenered) {
// 		$this->forkControlsRendered = $forkControlsRedenered;
// 		return $this;
// 	}
}