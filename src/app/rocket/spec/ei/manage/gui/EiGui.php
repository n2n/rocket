<?php
namespace rocket\spec\ei\manage\gui;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\mapping\EiEntry;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\component\command\control\OverallControlComponent;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\config\mask\model\ControlOrder;
use rocket\spec\ei\manage\control\Control;
use n2n\persistence\meta\structure\View;
use n2n\web\ui\UiComponent;

class EiGui {
	private $eiFrame;
	private $guiDefinition;
	private $bulky;
	private $eiGuiViewFactory;
	private $eiGuiListeners = array();
	
	private $eiEntryGuis = array();
	
	public function __construct(EiFrame $eiFrame, GuiDefinition $guiDefinition, bool $bulky, 
			EiGuiViewFactory $eiGuiViewFactory) {
		$this->eiFrame = $eiFrame;
		$this->guiDefinition = $guiDefinition;
		$this->bulky = $bulky;
		$this->eiGuiViewFactory = $eiGuiViewFactory;
		
		$eiGuiViewFactory->setEiGui($this);
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiFrame
	 */
	public function getEiFrame() {
		return $this->eiFrame;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\gui\GuiDefinition
	 */
	public function getGuiDefinition() {
		return $this->guiDefinition;
	}
	
	/**
	 */
	public function hasMultipleEiEntryGuis() {
		return count($this->eiEntryGuis) > 1;
	}
	
	/**
	 * @return int
	 */
	public function isBulky() {
		return $this->bulky;
	}
	
	/**
	 * @return EiEntryGui
	 */
	public function createEiEntryGui(EiEntry $eiEntry, bool $makeEditable, int $treeLevel = null, bool $append = true) {
		$eiEntryGui = $this->eiGuiViewFactory->createEiEntryGui($eiEntry, $makeEditable, $treeLevel, $append);
		ArgUtils::valTypeReturn($eiEntryGui, EiEntryGui::class, $this->eiGuiViewFactory, 'createEiEntryGui');
		
		foreach ($this->eiGuiListeners as $eiGuiListener) {
			$eiGuiListener->onNewEiEntryGui($eiEntryGui);
		}
		
		if ($append) {
			$this->eiEntryGuis[] = $eiEntryGui;
		}
		
		$eiEntryGui->markInitialized();
		
		return $eiEntryGui;
	}
	
// 	public function appendEiEntryGui(EiEntryGui $eiEntryGui) {
// 		$this->eiGuiViewFactory->appendEiEntryGui($eiEntryGui);
// 		$this->eiEntryGuis[] = $eiEntryGui;
// 	}
	
	/**
	 * @return EiEntryGui[]
	 */
	public function getEiEntryGuis() {
		return $this->eiEntryGuis;
	}
	
	public function createView(HtmlView $contextView = null) {
		$view = $this->eiGuiViewFactory->createView($contextView);
		
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
	
	public function createOverallControls(HtmlView $view) {
		$eiMask = $this->eiFrame->getContextEiMask();
		
		$eiu = new Eiu($this);
		
		$controls = array();
		
		foreach ($eiMask->getEiEngine()->getEiCommandCollection() as $eiCommandId => $eiCommand) {
			if (!($eiCommand instanceof OverallControlComponent)
					|| !$this->eiFrame->getManageState()->getEiPermissionManager()->isEiCommandAccessible($eiCommand)) {
				continue;
			}

			$entryControls = $eiCommand->createOverallControls($eiu, $view);
			ArgUtils::valArrayReturn($entryControls, $eiCommand, 'createEntryControls', Control::class);
			foreach ($entryControls as $controlId => $control) {
				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
			}
		}
		
		$controls = $eiMask->sortOverallControls($controls, $this, $view);
		ArgUtils::valArrayReturn($controls, $eiMask, 'sortControls', Control::class);
		
		return $controls;
	}
}



interface EiGuiViewFactory {

	/**
	 * @param \rocket\spec\ei\manage\gui\EiGui $eiGui
	 */
	public function setEiGui(\rocket\spec\ei\manage\gui\EiGui $eiGui);
	
	/**
	 * @param EiEntry $eiEntry
	 * @param bool $makeEditable
	 * @param int $treeLevel
	 * @param bool $append
	 * @return EiEntryGui
	 */
	public function createEiEntryGui(EiEntry $eiEntry, bool $makeEditable, int $treeLevel = null, 
			bool $append = true): EiEntryGui;
	
	/**
	 * @param \rocket\spec\ei\manage\gui\EiEntryGui $eiEntryGui
	 */
	public function appendEiEntryGui(\rocket\spec\ei\manage\gui\EiEntryGui $eiEntryGui);
	
	/**
	 * @return HtmlView
	 */
	public function createView(HtmlView $contextView = null): UiComponent;
}