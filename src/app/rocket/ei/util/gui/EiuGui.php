<?php
namespace rocket\ei\util\gui;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\manage\gui\EiGuiSiFactory;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use n2n\l10n\N2nLocale;
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
	 * @param GuiPropPath|string $eiPropPath
	 * @param bool $required
	 * @return string|null
	 */
	public function getPropLabel($guiPropPath, N2nLocale $n2nLocale = null, bool $required = false) {
		$guiPropPath = GuiPropPath::create($guiPropPath);
		if ($n2nLocale === null) {
			$n2nLocale = $this->eiGui->getEiFrame()->getN2nContext()->getN2nLocale();
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
	 * @param GuiPropPath|string $eiPropPath
	 * @param bool $required
	 * @throws \InvalidArgumentException
	 * @throws GuiException
	 * @return \rocket\ei\manage\gui\GuiProp|null
	 */
	public function getGuiPropByGuiPropPath($eiPropPath, bool $required = false) {
		$eiPropPath = GuiPropPath::create($eiPropPath);
		
		try {
			return $this->eiGui->getEiGuiSiFactory()->getGuiDefinition()->getGuiPropByGuiPropPath($eiPropPath);
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
		
// 		$displayStructure = $this->eiGui->getEiGuiSiFactory()->getDisplayStructure();
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
		
// 		$this->eiGui->init(new CustomGuiViewFactory($viewFactory), $guiPropPaths);
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