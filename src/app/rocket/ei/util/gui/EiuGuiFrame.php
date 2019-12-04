<?php
namespace rocket\ei\util\gui;

use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\manage\gui\EiGuiSiFactory;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use n2n\l10n\N2nLocale;
use n2n\util\ex\NotYetImplementedException;
use rocket\ei\component\command\EiCommand;
use rocket\ei\util\control\EiuControlFactory;

class EiuGuiFrame {
	private $eiGuiFrame;
	private $eiuFrame;
	private $eiuAnalyst;
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGuiFrame $eiGuiFrame, ?EiuFrame $eiuFrame, EiuAnalyst $eiuAnalyst) {
		$this->eiGuiFrame = $eiGuiFrame;
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
			$this->eiuFrame = new EiuFrame($this->eiGuiFrame->getEiFrame(), $this->eiuAnalyst);
		}
		
		return $this->eiuFrame;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	public function getEiGuiFrame() {
		return $this->eiGuiFrame;
	}
	
	/**
	 * @return number
	 */
	public function getViewMode() {
		return $this->eiGuiFrame->getViewMode();
	}
	
	/**
	 * @param GuiPropPath|string $eiPropPath
	 * @param bool $required
	 * @return string|null
	 */
	public function getPropLabel($guiPropPath, N2nLocale $n2nLocale = null, bool $required = false) {
		$guiPropPath = GuiPropPath::create($guiPropPath);
		if ($n2nLocale === null) {
			$n2nLocale = $this->eiGuiFrame->getEiFrame()->getN2nContext()->getN2nLocale();
		}
		
// 		if (null !== ($displayItem = $this->getDisplayItemByGuiPropPath($eiPropPath))) {
// 			return $displayItem->translateLabel($n2nLocale);
// 		}
		
		if (null !== ($guiProp = $this->getGuiPropByGuiPropPath($guiPropPath, $required))) {
			return $guiProp->getDisplayLabel();
		}
		
		return null;
	}
	
	/**
	 * @return \rocket\ei\EiPropPath[]
	 */
	function getEiPropPaths() {
		return $this->eiGuiFrame->getEiPropPaths();
	}
	
	function getGuiPropPaths() {
		return $this->eiGuiFrame->getGuiPropPaths();
	}
	
	function newEntryGui($eiEntryArg) {
		$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg, 'eiEntryArg');
		
		$eiEntryGui = $this->eiGuiFrame->createEiEntryGui($eiEntry);
		
		return new EiuEntryGui($eiEntryGui, $this, $this->eiuAnalyst);
	}
	
	/**
	 * @param GuiPropPath|string $prefixGuiPropPath
	 * @return GuiPropPath[]
	 */
	function getForkedGuiPropPaths($prefixGuiPropPath) {
		$prefixGuiPropPath = GuiPropPath::create($prefixGuiPropPath);
		$size = $prefixGuiPropPath->size();
		
		$forkedGuiPropPaths = [];
		foreach ($this->eiGuiFrame->filterGuiPropPaths($prefixGuiPropPath) as $guiPropPath) {
			$forkedGuiPropPaths[] = $guiPropPath->subGuiPropPath($size);
		}
		return $forkedGuiPropPaths;
	}
	
	/**
	 * @param GuiPropPath|string $prefixGuiPropPath
	 * @return \rocket\ei\EiPropPath[]
	 */
	function getForkedEiPropPaths($prefixGuiPropPath) {
		$prefixGuiPropPath = GuiPropPath::create($prefixGuiPropPath);
		
		$forkedEiPropPaths = [];
		foreach ($this->getForkedGuiPropPaths($prefixGuiPropPath) as $guiPropPath) {
			$forkedEiPropPaths[] = $guiPropPath->getFirstEiPropPath();
		}
		return $forkedEiPropPaths;
	}
	
	/**
	 * @param GuiPropPath|string $eiPropPath
	 * @param bool $required
	 * @throws \InvalidArgumentException
	 * @throws GuiException
	 * @return \rocket\ei\manage\gui\GuiProp|null
	 */
	public function getGuiPropByGuiPropPath($guiPropPath, bool $required = false) {
		$guiPropPath = GuiPropPath::create($guiPropPath);
		
		try {
			return $this->eiGuiFrame->getEiGuiSiFactory()->getGuiDefinition()->getGuiPropByGuiPropPath($guiPropPath);
		} catch (GuiException $e) {
			if (!$required) return null;
			throw $e;
		}
	}
		
// 	/**
// 	 * @param GuiPropPath|string $eiPropPath
// 	 * @param bool $required
// 	 * @throws \InvalidArgumentException
// 	 * @return \rocket\ei\mask\model\DisplayItem
// 	 */
// 	public function getDisplayItemByGuiPropPath($eiPropPath) {
// 		$eiPropPath = GuiPropPath::create($eiPropPath);
		
// 		$displayStructure = $this->eiGuiFrame->getEiGuiSiFactory()->getDisplayStructure();
// 		if ($displayStructure !== null) {
// 			return $displayStructure->getDisplayItemByGuiPropPath($eiPropPath);
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
	 * @param EiCommand $eiCommand
	 * @return \rocket\ei\util\control\EiuControlFactory
	 */
	public function controlFactory(EiCommand $eiCommand) {
		return new EiuControlFactory($this, $eiCommand);
	}
	
// 	public function initWithUiCallback(\Closure $viewFactory, array $guiPropPaths) {
// 		$guiPropPaths = GuiPropPath::createArray($guiPropPaths);
		
// 		$this->eiGuiFrame->init(new CustomGuiViewFactory($viewFactory), $guiPropPaths);
// 	}
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
	
// 	public function createSiDeclaration(): SiDeclaration {
// 		throw new NotYetImplementedException();
// 	}
	
	public function getSiStructureDeclarations(): array {
		throw new NotYetImplementedException();
	}

	public function getSiProps(): array {
		throw new NotYetImplementedException();
	}


}