<?php
namespace rocket\op\ei\util\gui;

use rocket\ui\gui\ViewMode;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\EiGuiDefinition;
use rocket\op\ei\util\EiuAnalyst;
use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\meta\SiMask;
use rocket\op\ei\EiPropPath;

class EiuGuiDefinition {
	

	public function __construct(private readonly EiGuiDefinition $eiGuiDefinition,
			private readonly EiuAnalyst $eiuAnalyst) {
	}
	
	/**
	 * @return EiGuiDefinition
	 */
	function getEiGuiDefinition(): EiGuiDefinition {
		return $this->eiGuiDefinition;
	}
	
//	/**
//	 * @return EiuGuiDeclaration
//	 */
//	function guiDeclaration(): EiuGuiDeclaration {
//		if ($this->eiuGuiDeclaration  === null) {
//			$this->eiuGuiDeclaration  = new EiuGuiDeclaration($this->eiGuiDefinition->getEiGuiDeclaration(), $this->eiuAnalyst);
//		}
//
//		return $this->eiuGuiDeclaration;
//	}/**
//	 * @return EiuGuiDeclaration
//	 */
//	function guiDeclaration(): EiuGuiDeclaration {
//		if ($this->eiuGuiDeclaration  === null) {
//			$this->eiuGuiDeclaration  = new EiuGuiDeclaration($this->eiGuiDefinition->getEiGuiDeclaration(), $this->eiuAnalyst);
//		}
//
//		return $this->eiuGuiDeclaration;
//	}/**
//	 * @return EiuGuiDeclaration
//	 */
//	function guiDeclaration(): EiuGuiDeclaration {
//		if ($this->eiuGuiDeclaration  === null) {
//			$this->eiuGuiDeclaration  = new EiuGuiDeclaration($this->eiGuiDefinition->getEiGuiDeclaration(), $this->eiuAnalyst);
//		}
//
//		return $this->eiuGuiDeclaration;
//	}
	
// 	/**
// 	 * @return \rocket\op\ei\util\frame\EiuFrame
// 	 */
// 	private function getEiuFrame() {
// 		if ($this->eiuFrame !== null) {
// 			return $this->eiuFrame;
// 		}
		
// 		if ($this->eiuAnalyst !== null) {
// 			$this->eiuFrame = $this->eiuAnalyst->getEiuFrame(false);
// 		}
		
// 		if ($this->eiuFrame === null) {
// 			$this->eiuFrame = new EiuFrame($this->eiGuiDefinition->getEiFrame(), $this->eiuAnalyst);
// 		}
		
// 		return $this->eiuFrame;
// 	}
	
	/**
	 * @return int
	 */
	public function getViewMode(): int {
		return $this->eiGuiDefinition->getViewMode();
	}
//
//	public function getPropLabel(DefPropPath|string $defPropPath, N2nLocale $n2nLocale = null, bool $required = false): ?string {
//		$defPropPath = DefPropPath::create($defPropPath);
//		if ($n2nLocale === null) {
//			$n2nLocale = $this->eiuAnalyst->getN2nContext()->getN2nLocale();
//		}
//
//// 		if (null !== ($displayItem = $this->getDisplayItemByDefPropPath($eiPropPath))) {
//// 			return $displayItem->translateLabel($n2nLocale);
//// 		}
//
//		if (null !== ($guiProp = $this->getGuiPropWrapperByDefPropPath($defPropPath, $required))) {
//			return $guiProp->getDisplayLabel();
//		}
//
//		return null;
//	}
	
//	/**
//	 * @param DefPropPath|string $defPropPath
//	 * @param bool $required
//	 * @return \rocket\op\ei\util\spec\EiuProp
//	 */
//	function getProp($defPropPath, bool $required = true) {
//		return new EiuProp($this->getGuiPropWrapperByDefPropPath($defPropPath, $required), null, $this->eiuAnalyst);
//	}
	
	/**
	 * @return EiPropPath[]
	 */
	function getEiPropPaths() {
		return $this->eiGuiDefinition->getEiPropPaths();
	}
	
	function getDefPropPaths() {
		return $this->eiGuiDefinition->getDefPropPaths();
	}
	
 	function newEntryGui($eiEntryArg, bool $entryGuiControlsIncluded = false): EiuGuiEntry {
 		$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg, 'eiEntryArg');
		
 		$eiGuiEntry = $this->eiGuiDefinition->createGuiEntry($this->eiuAnalyst->getEiFrame(true),
				$eiEntry, $entryGuiControlsIncluded);
		
 		return new EiuGuiEntry($eiGuiEntry, null, $this, $this->eiuAnalyst);
 	}
	
	/**
	 * @param DefPropPath|string $prefixDefPropPath
	 * @return DefPropPath[]
	 */
	function getForkedDefPropPaths($prefixDefPropPath) {
		$prefixDefPropPath = DefPropPath::create($prefixDefPropPath);
		$size = $prefixDefPropPath->size();
		
		$forkedDefPropPaths = [];
		foreach ($this->eiGuiDefinition->filterDefPropPaths($prefixDefPropPath) as $defPropPath) {
			$forkedDefPropPaths[] = $defPropPath->subDefPropPath($size);
		}
		return $forkedDefPropPaths;
	}
	
	/**
	 * @param DefPropPath|string $prefixDefPropPath
	 * @return EiPropPath[]
	 */
	function getForkedEiPropPaths($prefixDefPropPath) {
		$prefixDefPropPath = DefPropPath::create($prefixDefPropPath);
		
		$forkedEiPropPaths = [];
		foreach ($this->getForkedDefPropPaths($prefixDefPropPath) as $defPropPath) {
			$forkedEiPropPaths[] = $defPropPath->getFirstEiPropPath();
		}
		return $forkedEiPropPaths;
	}
	
//	/**
//	 * @param DefPropPath|string $eiPropPath
//	 * @param bool $required
//	 * @throws \InvalidArgumentException
//	 * @throws GuiException
//	 * @return \rocket\op\ei\manage\gui\GuiProp|null
//	 */
//	private function getGuiPropWrapperByDefPropPath($defPropPath, bool $required = false) {
//		$defPropPath = DefPropPath::create($defPropPath);
//
//		try {
//			return $this->eiGuiDefinition->getEiGuiDefinition()->getGuiPropWrapperByDefPropPath($defPropPath);
//		} catch (GuiException $e) {
//			if (!$required) return null;
//			throw $e;
//		}
//	}
	
	/**
	 * @param DefPropPath|string $defPropPath
	 * @return \rocket\op\ei\manage\gui\DisplayDefinition|null
	 */
	function getDisplayDefinition($defPropPath, bool $required = false) {
		$defPropPath = DefPropPath::create($defPropPath);
		
		if (!$required && !$this->eiGuiDefinition->containsDisplayDefinition($defPropPath)) {
			return null;
		}
		
		return $this->eiGuiDefinition->getDisplayDefintion($defPropPath);
	}
		
// 	/**
// 	 * @param DefPropPath|string $eiPropPath
// 	 * @param bool $required
// 	 * @throws \InvalidArgumentException
// 	 * @return \rocket\op\ei\mask\model\DisplayItem
// 	 */
// 	public function getDisplayItemByDefPropPath($eiPropPath) {
// 		$eiPropPath = DefPropPath::create($eiPropPath);
		
// 		$displayStructure = $this->eiGuiDefinition->getEiGuiSiFactory()->getDisplayStructure();
// 		if ($displayStructure !== null) {
// 			return $displayStructure->getDisplayItemByDefPropPath($eiPropPath);
// 		}
// 		return null;
// 	}
	
	/**
	 * @return bool
	 */
	public function isBulky(): bool {
		return (bool) ($this->getViewMode() & ViewMode::bulky());	
	}
	
	/**
	 * @return bool
	 */
	public function isCompact(): bool {
		return (bool) ($this->getViewMode() & ViewMode::compact());
	}
	
	/**
	 * @return boolean
	 */
	public function isReadOnly(): bool {
		return (bool) ($this->getViewMode() & ViewMode::read());
	}
	
// 	public function initWithUiCallback(\Closure $viewFactory, array $defPropPaths) {
// 		$defPropPaths = DefPropPath::createArray($defPropPaths);
		
// 		$this->eiGuiDefinition->init(new CustomGuiViewFactory($viewFactory), $defPropPaths);
// 	}

	/**
	 * @return \rocket\ui\si\meta\SiDeclaration
	 */
	function createSiDeclaration() {
		return new SiDeclaration(ViewMode::createSiStyle($this->getViewMode()), [new SiMask(
				$this->eiGuiDefinition->createGuiMask($this->eiuAnalyst->getN2nContext(true)->getN2nLocale()),
				null)]);
	}
}
//
//class CustomGuiViewFactory implements EiGuiSiFactory {
//	private $factory;
//
//	public function __construct(\Closure $factory) {
//		$this->factory = $factory;
//	}
//
//// 	public function createUiComponent(array $eiGuiValueBoundaries, ?HtmlView $contextView): UiComponent {
//// 		$uiComponent = $this->factory->call(null, $eiGuiValueBoundaries, $contextView);
//// 		ArgUtils::valTypeReturn($uiComponent, [UiComponent::class, 'scalar'], null, $this->factory);
//
//// 		if (is_scalar($uiComponent)) {
//// 			$uiComponent = new HtmlSnippet($uiComponent);
//// 		}
//
//// 		return $uiComponent;
//// 	}
//
//// 	public function createSiDeclaration(): SiDeclaration {
//// 		throw new NotYetImplementedException();
//// 	}
//
//	public function getSiStructureDeclarations(): array {
//		throw new NotYetImplementedException();
//	}
//
//	public function getSiProps(): array {
//		throw new NotYetImplementedException();
//	}
//
//
//}