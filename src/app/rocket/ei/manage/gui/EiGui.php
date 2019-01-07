<?php
namespace rocket\ei\manage\gui;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\entry\EiEntry;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\GuiFactory;

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
	 * @var EiGuiViewFactory
	 */
	private $eiGuiViewFactory;
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
	/**
	 * @var EiGuiNature
	 */
	private $eiGuiNature;
	
	/**
	 * @param EiFrame $eiFrame
	 * @param int $viewMode Use constants from {@see ViewMode}
	 */
	public function __construct(EiFrame $eiFrame, GuiDefinition $guiDefinition, int $viewMode) {
		$this->eiFrame = $eiFrame;
		$this->guiDefinition = $guiDefinition;
		ArgUtils::valEnum($viewMode, ViewMode::getAll());
		$this->viewMode = $viewMode;
		$this->eiGuiNature = new EiGuiNature();
	}
	
	/**
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	public function getEiFrame() {
		return $this->eiFrame;
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
	 * @param EiGuiViewFactory $eiGuiViewFactory
	 * @param GuiFieldPath[]
	 */
	public function init(EiGuiViewFactory $eiGuiViewFactory, array $guiFieldPaths = null) {
		if ($this->eiGuiViewFactory !== null) {
			throw new IllegalStateException('EiGui already initialized.');
		}
		
		$this->eiGuiViewFactory = $eiGuiViewFactory;
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
		return $this->eiGuiViewFactory !== null;
	}
	
	/**
	 * @throws IllegalStateException
	 */
	private function ensureInit() {
		if ($this->eiGuiViewFactory !== null) return;
		
		throw new IllegalStateException('EiGui not yet initialized.');
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiViewFactory
	 * @throws IllegalStateException If EiGui {@see self::init()} hasn't been called yet.
	 */
	public function getEiGuiViewFactory() {
		$this->ensureInit();
		
		return $this->eiGuiViewFactory;
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
	
	/**
	 * @param HtmlView|null $contextView
	 * @return \n2n\web\ui\UiComponent
	 */
	public function createUiComponent(?HtmlView $contextView) {
		$this->ensureInit();
		
		$view = $this->eiGuiViewFactory->createUiComponent($this->eiEntryGuis, $contextView);
		
		foreach ($this->eiGuiListeners as $eiGuiListener) {
			$eiGuiListener->onNewView($view);
		}
		
		return $view;
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
	 * @param HtmlView $view
	 * @return \rocket\ei\manage\control\Control[]
	 */
	public function createOverallControls(HtmlView $view) {
		return $this->eiFrame->getContextEiEngine()->getEiMask()->getEiEngine()->createEiGuiOverallControls($this, $view);
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiNature
	 */
	public function getEiGuiNature()  {
		return $this->eiGuiNature;
	}
}

class GuiPropAssembly {
	private $guiProp;
	private $guiFieldPath;
	private $displayDefinition;
	
	/**
	 * @param GuiProp $guiProp
	 * @param GuiFieldPath $guiFieldPath
	 * @param DisplayDefinition $displayDefinition
	 */
	public function __construct(GuiProp $guiProp, GuiFieldPath $guiFieldPath, DisplayDefinition $displayDefinition) {
		$this->guiProp = $guiProp;
		$this->guiFieldPath = $guiFieldPath;
		$this->displayDefinition = $displayDefinition;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\GuiProp
	 */
	public function getGuiProp() {
		return $this->guiProp;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\GuiFieldPath
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
	function areEntryControlsRendered() {
		return $this->entryControlsRendered;
	}
	
	/**
	 * @param bool $entryControlsRendered
	 * @return \rocket\ei\manage\gui\EiGuiNature
	 */
	function setEntryControlsRendered(bool $entryControlsRendered) {
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