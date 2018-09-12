<?php
namespace rocket\ei\manage\gui;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\entry\EiEntry;
use n2n\reflection\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\GuiFactory;

class EiGui {
	/**
	 * @var EiFrame
	 */
	private $eiFrame;
	/**
	 * @var int
	 */
	private $viewMode;
	/**
	 * @var EiGuiViewFactory
	 */
	private $eiGuiViewFactory;
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
	 * @param int $viewMode Use constants from {@see ViewMode}
	 */
	public function __construct(EiFrame $eiFrame, int $viewMode) {
		$this->eiFrame = $eiFrame;
		ArgUtils::valEnum($viewMode, ViewMode::getAll());
		$this->viewMode = $viewMode;
	}
	
	/**
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	public function getEiFrame() {
		return $this->eiFrame;
	}
	
	/**
	 * @return int
	 */
	public function getViewMode() {
		return $this->viewMode;
	}
	
	/**
	 * @param EiGuiViewFactory $eiGuiViewFactory
	 */
	public function init(EiGuiViewFactory $eiGuiViewFactory) {
		if ($this->eiGuiViewFactory !== null) {
			throw new IllegalStateException('EiGui already initialized.');
		}
		
		$this->eiGuiViewFactory = $eiGuiViewFactory;
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
		
		$guiIdsPaths = $this->eiGuiViewFactory->getGuiIdPaths();
		ArgUtils::valArrayReturn($guiIdsPaths, $this->eiGuiViewFactory, 'getGuiIdPaths', GuiIdPath::class);
		
		$eiEntryGui = GuiFactory::createEiEntryGui($this, $eiEntry, $guiIdsPaths, $treeLevel);
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
	public function createUiComponent(?HtmlView $contextView, EiGuiConfig $eiGuiConfig) {
		$this->ensureInit();
		
		$view = $this->eiGuiViewFactory->createUiComponent($this->eiEntryGuis, $contextView, $eiGuiConfig);
		
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
}