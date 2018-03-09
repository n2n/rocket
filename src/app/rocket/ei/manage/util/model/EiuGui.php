<?php
namespace rocket\ei\manage\util\model;

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

class EiuGui {
	private $eiGui;
	private $eiuFrame;
	private $eiuFactory;
	
	public function __construct(EiGui $eiGui, EiuFrame $eiuFrame = null, EiuFactory $eiuFactory = null) {
		$this->eiGui = $eiGui;
		$this->eiuFrame = $eiuFrame;
		$this->eiuFactory = $eiuFactory;
	}
	
	/**
	 * @return \rocket\ei\manage\util\model\EiuFrame
	 */
	public function getEiuFrame() {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		if ($this->eiuFactory !== null) {
			$this->eiuFrame = $this->eiuFactory->getEiuFrame(false);
		}
		
		if ($this->eiuFrame === null) {
			$this->eiuFrame = new EiuFrame($this->eiGui->getEiFrame(), $this->eiuFactory);
		}
		
		return $this->eiuFrame;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	public function getEiGui() {
		return $this->eiGui;
	}
	
	public function getViewMode() {
		return $this->eiGui->getViewMode();
	}
	
	/**
	 * @param GuiIdPath|string $guiIdPath
	 * @param bool $required
	 * @return string|null
	 */
	public function getPropLabel($guiIdPath, bool $required = false) {
		$guiIdPath = GuiIdPath::create($guiIdPath);
		
		if (null !== ($displayItem = $this->getDisplayItemByGuiIdPath($guiIdPath))) {
			return $displayItem->getDisplayLabel();
		}
		
		if (null !== ($guiProp = $this->getGuiPropByGuiIdPath($guiIdPath, $required))) {
			return $guiProp->getDisplayLabel();
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
			return new EiuEntryGui(current($eiEntryGuis), $this, $this->eiuFactory);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No single EiuEntryGui is available.');
	}
	
	public function entryGuis() {
		$eiuEntryGuis = array();
		
		foreach ($this->eiGui->getEiEntryGuis() as $eiEntryGui) {
			$eiuEntryGuis[] = new EiuEntryGui($eiEntryGui, $this, $this->eiuFactory);
		}
		
		return $eiuEntryGuis;
	}
	
	public function initWithUiCallback(\Closure $viewFactory, array $guiIdPaths) {
		$guiIdPaths = GuiIdPath::createArray($guiIdPaths);
		$guiDefinition = $this->eiGui->getEiFrame()->getContextEiEngine()->getGuiDefinition();
		
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
		$eiObject = EiuFactory::buildEiObjectFromEiArg($eiEntryArg, 'eiEntryArg', $this->eiuFrame->getContextEiType(), true, 
				$eiEntry);
		
		if ($eiEntry === null) {
			$eiEntry = (new EiuEntry($eiObject, null, $this->eiuFrame, $this->eiuFactory))->getEiEntry();
		}
		
		return new EiuEntryGui($this->eiGui->createEiEntryGui($eiEntry, $treeLevel, true), $this, $this->eiuFactory);
	}
	
	/**
	 * @return \rocket\ei\manage\util\model\EiuGui
	 */
	public function removeGroups() {
		$this->eiGui->getEiGuiViewFactory()->applyMode(EiGuiViewFactory::MODE_NO_GROUPS);
		return $this;
	}
	
	/**
	 * @return \rocket\ei\manage\util\model\EiuGui
	 */
	public function forceRootGroups() {
		$this->eiGui->getEiGuiViewFactory()->applyMode(EiGuiViewFactory::MODE_ROOT_GROUPED);
		return $this;
	}
	/**
	 * @return \rocket\ei\manage\util\model\EiuGui
	 */
	public function allowControls() {
		$this->eiGui->getEiGuiViewFactory()->applyMode(EiGuiViewFactory::MODE_CONTROLS_ALLOWED);
		return $this;
	}
	
	/**
	 * 
	 * @return \n2n\impl\web\ui\view\html\HtmlView
	 */
	public function createView(HtmlView $contextView = null) {
		return $this->eiGui->createView($contextView);
	}
}


class CustomGuiViewFactory implements EiGuiViewFactory {
	private $guiDefinition;
	private $guiIdPaths;
	private $factory;
	
	public function __construct(GuiDefinition $guiDefinition, array $guiIdPaths, \Closure $factory) {
		$this->guiIdPaths = $guiIdPaths;
		$this->guiDefinition = $guiDefinition;
		$this->factory = $factory;
	}
	
	public function applyMode(int $rule) {
	}
	
	public function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}
	
	public function getGuiIdPaths(): array {
		return $this->guiIdPaths;
	}
	
	public function getDisplayStructure(): ?DisplayStructure {
		return null;
	}
	
	public function createView(array $eiEntryGuis, HtmlView $contextView = null): UiComponent {
		$uiComponent = $this->factory->call(null, $eiEntryGuis, $contextView);
		ArgUtils::valTypeReturn($uiComponent, [UiComponent::class, 'scalar'], null, $this->factory);
		
		if (is_scalar($uiComponent)) {
			$uiComponent = new HtmlSnippet($uiComponent);
		}
		
		return $uiComponent;
	}

}