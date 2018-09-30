<?php
namespace rocket\ei\util\gui;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\GuiIdPath;
use rocket\ei\manage\gui\EiGuiViewFactory;
use rocket\ei\manage\gui\GuiDefinition;
use n2n\web\ui\UiComponent;
use n2n\reflection\ArgUtils;
use n2n\impl\web\ui\view\html\HtmlSnippet;
use rocket\ei\manage\gui\ui\DisplayStructure;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\EiGuiConfig;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\util\entry\EiuEntry;
use n2n\l10n\N2nLocale;

class EiuGui {
	private $eiGui;
	private $eiuFrame;
	private $eiuAnalyst;
	private $eiGuiConfig;
	
	/**
	 * @param EiGui $eiGui
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGui $eiGui, ?EiuFrame $eiuFrame = null, EiuAnalyst $eiuAnalyst) {
		$this->eiGui = $eiGui;
		$this->eiuFrame = $eiuFrame;
		$this->eiuAnalyst = $eiuAnalyst;
		$this->eiGuiConfig = new EiGuiConfig(false);
	}
	
	/**
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	public function getEiuFrame() {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		if ($this->eiuAnalyst !== null) {
			$this->eiuFrame = $this->eiuAnalyst->getEiuFrame(false);
		}
		
		if ($this->eiuFrame === null) {
			$this->eiuFrame = new EiuFrame($this->eiGui->getEiFrame(), $this->eiuAnalyst);
		}
		
		return $this->eiuFrame;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	public function getEiGui() {
		return $this->eiGui;
	}
	
	/**
	 * @return number
	 */
	public function getViewMode() {
		return $this->eiGui->getViewMode();
	}
	
	/**
	 * @param GuiIdPath|string $guiIdPath
	 * @param bool $required
	 * @return string|null
	 */
	public function getPropLabel($guiIdPath, N2nLocale $n2nLocale = null, bool $required = false) {
		$guiIdPath = GuiIdPath::create($guiIdPath);
		if ($n2nLocale === null) {
			$n2nLocale = $this->eiGui->getEiFrame()->getN2nContext()->getN2nLocale();
		}
		
		if (null !== ($displayItem = $this->getDisplayItemByGuiIdPath($guiIdPath))) {
			return $displayItem->translateLabel($n2nLocale);
		}
		
		if (null !== ($guiProp = $this->getGuiPropByGuiIdPath($guiIdPath, $required))) {
			return $guiProp->getDisplayLabel($n2nLocale);
		}
		
		return null;
	}
	
	/**
	 * @param GuiIdPath|string $guiIdPath
	 * @param bool $required
	 * @throws \InvalidArgumentException
	 * @throws GuiException
	 * @return \rocket\ei\manage\gui\GuiProp|null
	 */
	public function getGuiPropByGuiIdPath($guiIdPath, bool $required = false) {
		$guiIdPath = GuiIdPath::create($guiIdPath);
		
		try {
			return $this->eiGui->getEiGuiViewFactory()->getGuiDefinition()->getGuiPropByGuiIdPath($guiIdPath);
		} catch (GuiException $e) {
			if (!$required) return null;
			throw $e;
		}
	}
		
	/**
	 * @param GuiIdPath|string $guiIdPath
	 * @param bool $required
	 * @throws \InvalidArgumentException
	 * @return \rocket\ei\manage\gui\ui\DisplayItem
	 */
	public function getDisplayItemByGuiIdPath($guiIdPath) {
		$guiIdPath = GuiIdPath::create($guiIdPath);
		
		$displayStructure = $this->eiGui->getEiGuiViewFactory()->getDisplayStructure();
		if ($displayStructure !== null) {
			return $displayStructure->getDisplayItemByGuiIdPath($guiIdPath);
		}
		return null;
	}
	
	/**
	 * @return bool
	 */
	public function isBulky() {
		return (bool) ($this->getViewMode() & ViewMode::bulky());	
	}
	
	/**
	 * @return bool
	 */
	public function isCompact() {
		return (bool) ($this->getViewMode() & ViewMode::compact());
	}
	
	/**
	 * @return boolean
	 */
	public function isReadOnly() {
		return (bool) ($this->getViewMode() & ViewMode::read());
	}
	
	/**
	 * @return bool
	 */
	public function isSingle() {
		return 1 == count($this->eiGui->getEiEntryGuis());
	}
	
	/**
	 * 
	 * @param bool $required
	 * @return EiuEntryGui|null
	 */
	public function entryGui(bool $required = true) {
		$eiEntryGuis = $this->eiGui->getEiEntryGuis();
		$eiEntryGui = null;
		if (count($eiEntryGuis) == 1) {
			return new EiuEntryGui(current($eiEntryGuis), $this, $this->eiuAnalyst);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No single EiuEntryGui is available.');
	}
	
	public function entryGuis() {
		$eiuEntryGuis = array();
		
		foreach ($this->eiGui->getEiEntryGuis() as $eiEntryGui) {
			$eiuEntryGuis[] = new EiuEntryGui($eiEntryGui, $this, $this->eiuAnalyst);
		}
		
		return $eiuEntryGuis;
	}
	
	public function initWithUiCallback(\Closure $viewFactory, array $guiIdPaths) {
		$guiIdPaths = GuiIdPath::createArray($guiIdPaths);
		$eiFrame = $this->eiGui->getEiFrame();
		$guiDefinition = $this->eiGui->getEiFrame()->getManageState()->getDef()->getGuiDefinition(
				$eiFrame->getContextEiEngine()->getEiMask());
		
		$this->eiGui->init(new CustomGuiViewFactory($guiDefinition, $guiIdPaths, $viewFactory));
	}
// 	/**
// 	 * @param bool $required
// 	 * @throws EiuPerimeterException
// 	 * @return EiuEntryGui|null
// 	 */
// 	public function entryGui(bool $required = true) {
// 		if ($this->singleEiuEntryGui !== null || !$required) return $this->singleEiuEntryGui;
		
// 		throw new EiuPerimeterException('EiuEntryGui is unavailable.');
// 	}
	
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
			$eiEntry = (new EiuEntry($eiObject, null, $this->eiuFrame, $this->eiuAnalyst))->getEiEntry();
		}
		
		return new EiuEntryGui($this->eiGui->createEiEntryGui($eiEntry, $treeLevel, true), $this, $this->eiuAnalyst);
	}
	
	public function addDisplayContainer(string $type, string $label, array $attrs = null) {
		$egvf = $this->eiGui->getEiGuiViewFactory();
		$egvf->setDisplayStructure($egvf->getDisplayStructure()->withContainer($type, $label, $attrs));
		return $this;
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	public function removeSubStructures() {
		$egvf = $this->eiGui->getEiGuiViewFactory();
		$egvf->setDisplayStructure($egvf->getDisplayStructure()->withoutSubStructures());
		return $this;
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	public function forceRootGroups() {
		$egvf = $this->eiGui->getEiGuiViewFactory();
		$egvf->setDisplayStructure($egvf->getDisplayStructure()->groupedItems());
		return $this;
	}
	/**
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	public function allowControls() {
		$this->eiGuiConfig->setControlsAllowed(true);
		return $this;
	}
	
	/**
	 * @return HtmlView|null $contextView
	 * @return \n2n\impl\web\ui\view\html\HtmlView
	 */
	public function createView(HtmlView $contextView = null) {
		return $this->eiGui->createUiComponent($contextView, $this->eiGuiConfig);
	}
}

class CustomGuiViewFactory implements EiGuiViewFactory {
	private $guiDefinition;
	private $guiIdPaths;
	private $factory;
	private $displayStructure;
	
	public function __construct(GuiDefinition $guiDefinition, array $guiIdPaths, \Closure $factory) {
		$this->guiIdPaths = $guiIdPaths;
		$this->guiDefinition = $guiDefinition;
		$this->factory = $factory;
		$this->displayStructure = new DisplayStructure();
	}
	
	public function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}
	
	public function getGuiIdPaths(): array {
		return $this->guiIdPaths;
	}
	
	public function getDisplayStructure(): DisplayStructure {
		return $this->displayStructure;
	}
	
	public function setDisplayStructure(DisplayStructure $displayStructure) {
		$this->displayStructure = $displayStructure;
	}
	
	public function createUiComponent(array $eiEntryGuis, ?HtmlView $contextView, EiGuiConfig $eiGuiConfig): UiComponent {
		$uiComponent = $this->factory->call(null, $eiEntryGuis, $contextView);
		ArgUtils::valTypeReturn($uiComponent, [UiComponent::class, 'scalar'], null, $this->factory);
		
		if (is_scalar($uiComponent)) {
			$uiComponent = new HtmlSnippet($uiComponent);
		}
		
		return $uiComponent;
	}


}