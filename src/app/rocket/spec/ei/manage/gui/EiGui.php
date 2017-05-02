<?php
namespace rocket\spec\ei\manage\gui;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\mapping\EiEntry;
use rocket\spec\ei\component\GuiFactory;

class EiGui {
	private $eiFrame;
	private $guiDefinition;
	private $viewMode;
	private $eiGuiViewFactory;
	private $eiGuiListeners = array();
	
	public function __construct(EiFrame $eiFrame, GuiDefinition $guiDefinition, int $viewMode, 
			EiGuiViewFactory $eiGuiViewFactory) {
		$this->eiFrame = $eiFrame;
		$this->guiDefinition = $guiDefinition;
		$this->viewMode = $viewMode;
		$this->eiGuiViewFactory = $eiGuiViewFactory;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiFrame
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
	
	public function createEiEntryGui() {
		$eiEntryGui = $this->eiGuiViewFactory->createEiEntryGui();
		
		foreach ($this->eiGuiListeners as $eiGuiListener) {
			$eiGuiListener->onNewEiEntryGui($eiEntryGui);
		}
		
		return $eiEntryGui;
	}
	
	public function createEiEntryGui(EiEntry $eiEntry, int $level) {
		$eiEntryGui = GuiFactory::createEiEntryGui($eiGui, $eiEntry, $t);
		
		foreach ($this->eiGuiListeners as $eiGuiListener) {
			$eiGuiListener->onNewEiEntryGui($eiEntryGui);
		}
		
		return $eiEntryGui;
	}
	
	
	public function createView() {
		$view = $this->eiGuiViewFactory->createView($this);
		
		foreach ($this->eiGuiListeners as $eiGuiListener) {
			$eiGuiListener->onNewView($view);
		}
		
		return $view;
	}
	
	public function registerEiGuiListener(EiGuiListener $eiGuiListener) {
		$this->eiGuiListeners[spl_object_hash($eiGuiListener)] = $eiGuiListener;
	}
	
	public function unregisterEiGuiListener(EiGuiListener $eiGuiListener) {
		unset($this->eiGuiListeners[spl_object_hash($eiGuiListener)]);
	}
}



interface EiGuiViewFactory {
	
	/**
	 * 
	 * @return GuiIdPath[]
	 */
	public function getGuiIdPaths(): array;
	
	/**
	 * 
	 * @param EiGui $eiGui
	 * @return HtmlView
	 */
	public function createView(EiGui $eiGui): HtmlView;
}