<?php
namespace rocket\ei\util\gui;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\manage\gui\EiGuiSiFactory;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\util\entry\EiuEntry;
use n2n\l10n\N2nLocale;
use rocket\ei\util\entry\EiuObject;
use n2n\util\ex\NotYetImplementedException;
use rocket\ei\component\command\EiCommand;
use rocket\ei\util\control\EiuControlFactory;

class EiuGui {
	private $eiGui;
	private $eiuFrame;
	private $eiuAnalyst;
	
	/**
	 * @param EiGui $eiGui
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGui $eiGui, ?EiuFrame $eiuFrame, EiuAnalyst $eiuAnalyst) {
		$this->eiGui = $eiGui;
		$this->eiuFrame = $eiuFrame;
		$this->eiuAnalyst = $eiuAnalyst;
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
	 * @param GuiFieldPath|string $eiPropPath
	 * @param bool $required
	 * @return string|null
	 */
	public function getPropLabel($guiFieldPath, N2nLocale $n2nLocale = null, bool $required = false) {
		$guiFieldPath = GuiFieldPath::create($guiFieldPath);
		if ($n2nLocale === null) {
			$n2nLocale = $this->eiGui->getEiFrame()->getN2nContext()->getN2nLocale();
		}
		
// 		if (null !== ($displayItem = $this->getDisplayItemByGuiFieldPath($eiPropPath))) {
// 			return $displayItem->translateLabel($n2nLocale);
// 		}
		
		if (null !== ($guiProp = $this->getGuiPropByGuiFieldPath($guiFieldPath, $required))) {
			return $guiProp->getDisplayLabel();
		}
		
		return null;
	}
	
	/**
	 * @param GuiFieldPath|string $eiPropPath
	 * @param bool $required
	 * @throws \InvalidArgumentException
	 * @throws GuiException
	 * @return \rocket\ei\manage\gui\GuiProp|null
	 */
	public function getGuiPropByGuiFieldPath($eiPropPath, bool $required = false) {
		$eiPropPath = GuiFieldPath::create($eiPropPath);
		
		try {
			return $this->eiGui->getEiGuiSiFactory()->getGuiDefinition()->getGuiPropByGuiFieldPath($eiPropPath);
		} catch (GuiException $e) {
			if (!$required) return null;
			throw $e;
		}
	}
		
// 	/**
// 	 * @param GuiFieldPath|string $eiPropPath
// 	 * @param bool $required
// 	 * @throws \InvalidArgumentException
// 	 * @return \rocket\ei\mask\model\DisplayItem
// 	 */
// 	public function getDisplayItemByGuiFieldPath($eiPropPath) {
// 		$eiPropPath = GuiFieldPath::create($eiPropPath);
		
// 		$displayStructure = $this->eiGui->getEiGuiSiFactory()->getDisplayStructure();
// 		if ($displayStructure !== null) {
// 			return $displayStructure->getDisplayItemByGuiFieldPath($eiPropPath);
// 		}
// 		return null;
// 	}
	
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
	 * @param EiCommand $eiCommand
	 * @return \rocket\ei\util\control\EiuControlFactory
	 */
	public function controlFactory(EiCommand $eiCommand) {
		return new EiuControlFactory($this, $eiCommand);
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
	
	public function initWithUiCallback(\Closure $viewFactory, array $guiFieldPaths) {
		$guiFieldPaths = GuiFieldPath::createArray($guiFieldPaths);
		
		$this->eiGui->init(new CustomGuiViewFactory($viewFactory), $guiFieldPaths);
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
			$eiEntry = (new EiuEntry(null, new EiuObject($eiObject, $this->eiuAnalyst), 
					null, $this->eiuAnalyst))->getEiEntry(true);
		}
		
		return new EiuEntryGui($this->eiGui->createEiEntryGui($eiEntry, $treeLevel, true), $this, $this->eiuAnalyst);
	}
	
// 	public function addDisplayContainer(string $type, string $label, array $attrs = null) {
// 		$egvf = $this->eiGui->getEiGuiSiFactory();
// 		$egvf->setDisplayStructure($egvf->getDisplayStructure()->withContainer($type, $label, $attrs));
// 		return $this;
// 	}
	
// 	/**
// 	 * @return \rocket\ei\util\gui\EiuGui
// 	 */
// 	public function removeSubStructures() {
// 		$egvf = $this->eiGui->getEiGuiSiFactory();
// 		$egvf->setDisplayStructure($egvf->getDisplayStructure()->withoutSubStructures());
// 		return $this;
// 	}
	
// 	/**
// 	 * @return \rocket\ei\util\gui\EiuGui
// 	 */
// 	public function forceRootGroups() {
// 		$egvf = $this->eiGui->getEiGuiSiFactory();
// 		$egvf->setDisplayStructure($egvf->getDisplayStructure()->groupedItems());
// 		return $this;
// 	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	public function renderEntryGuiControls(bool $renderEntryGuiControls = true) {
		$this->eiGui->getEiGuiNature()->setEntryGuiControlsRendered($renderEntryGuiControls);
		return $this;
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	public function forceCollection(bool $collectionForced = true) {
		$this->eiGui->getEiGuiNature()->setCollectionForced($collectionForced);
		return $this;
	}	
		
// 	/**
// 	 * @return \rocket\ei\util\gui\EiuGui
// 	 */
// 	public function renderForkControls(bool $renderForkControls = true) {
// 		$this->eiGuiConfig->setForkControlsRendered($renderForkControls);
// 		return $this;
// 	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiNature
	 */
	function getEiGuiNature() {
		return $this->eiGui->getEiGuiNature();
	}
	
	/**
	 * @return HtmlView|null $contextView
	 * @return \n2n\impl\web\ui\view\html\HtmlView
	 */
	public function createView(HtmlView $contextView = null) {
		return $this->eiGui->createUiComponent($contextView);
	}
}

class CustomGuiViewFactory implements EiGuiSiFactory {
	private $factory;
	
	public function __construct(\Closure $factory) {
		$this->factory = $factory;
	}
	
// 	public function createUiComponent(array $eiEntryGuis, ?HtmlView $contextView): UiComponent {
// 		$uiComponent = $this->factory->call(null, $eiEntryGuis, $contextView);
// 		ArgUtils::valTypeReturn($uiComponent, [UiComponent::class, 'scalar'], null, $this->factory);
		
// 		if (is_scalar($uiComponent)) {
// 			$uiComponent = new HtmlSnippet($uiComponent);
// 		}
		
// 		return $uiComponent;
// 	}
	
// 	public function createSiCompactDeclaration(): SiCompactDeclaration {
// 		throw new NotYetImplementedException();
// 	}
	
	public function getSiFieldStructureDeclarations(): array {
		throw new NotYetImplementedException();
	}

	public function getSiFieldDeclarations(): array {
		throw new NotYetImplementedException();
	}


}