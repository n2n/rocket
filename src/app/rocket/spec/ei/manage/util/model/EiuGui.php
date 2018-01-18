<?php
namespace rocket\spec\ei\manage\util\model;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\gui\ViewMode;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\EiGuiViewFactory;
use rocket\spec\ei\manage\gui\GuiDefinition;
use n2n\web\ui\UiComponent;
use n2n\reflection\ArgUtils;
use n2n\impl\web\ui\view\html\HtmlSnippet;
use rocket\spec\ei\manage\gui\ui\DisplayStructure;
use rocket\spec\ei\manage\gui\GuiException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

class EiuGui {
	private $eiGui;
	private $eiuFrame;
	private $singleEiuEntryGui;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		$this->eiGui = $eiuFactory->getEiGui(true);
		$this->eiuFrame = $eiuFactory->getEiuFrame(true);
	}
	
	public function getEiuFrame() {
		return $this->eiuFrame;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\gui\EiGui
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
		$guiIdPath = GuiIdPath::createFromExpression($guiIdPath);
		
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
	 * @return \rocket\spec\ei\manage\gui\GuiProp|null
	 */
	public function getGuiPropByGuiIdPath($guiIdPath, bool $required = false) {
		$guiIdPath = GuiIdPath::createFromExpression($guiIdPath);
		
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
	 * @return \rocket\spec\ei\manage\gui\ui\DisplayItem
	 */
	public function getDisplayItemByGuiIdPath($guiIdPath) {
		$guiIdPath = GuiIdPath::createFromExpression($guiIdPath);
		
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
			return new EiuEntryGui(current($eiEntryGuis), $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No single EiuEntryGui is available.');
	}
	
	public function entryGuis() {
		$eiuEntryGuis = array();
		
		foreach ($this->eiGui->getEiEntryGuis() as $eiEntryGui) {
			$eiuEntryGuis[] = new EiuEntryGui($eiEntryGui, $this);
		}
		
		return $eiuEntryGuis;
	}
	
	public function initWithUiCallback(\Closure $viewFactory, array $guiIdPaths) {
		$guiIdPaths = GuiIdPath::createArrayFromExpressions($guiIdPaths);
		$guiDefinition = $this->eiGui->getEiFrame()->getContextEiMask()->getEiEngine()->getGuiDefinition();
		
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
		$eiObject = EiuFactory::buildEiObjectFromEiArg($eiEntryArg, 'eiEntryArg', $this->eiuFrame->getEiType(), true, 
				$eiEntry);
		
		if ($eiEntry === null) {
			$eiEntry = (new EiuEntry($eiObject, $this->eiuFrame))->getEiEntry();
		}
		
		return new EiuEntryGui($this->eiGui->createEiEntryGui($eiEntry, $treeLevel, true), $this);
	}
	
	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuGui
	 */
	public function removeGroups() {
		$this->eiGui->getEiGuiViewFactory()->applyMode(EiGuiViewFactory::MODE_NO_GROUPS);
		return $this;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuGui
	 */
	public function forceRootGroups() {
		$this->eiGui->getEiGuiViewFactory()->applyMode(EiGuiViewFactory::MODE_ROOT_GROUPED);
		return $this;
	}
	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuGui
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