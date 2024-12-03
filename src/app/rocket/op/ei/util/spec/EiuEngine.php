<?php
namespace rocket\op\ei\util\spec;

use n2n\l10n\N2nLocale;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\generic\UnknownGenericEiPropertyException;
use rocket\op\ei\manage\generic\GenericEiProperty;
use rocket\op\ei\manage\generic\UnknownScalarEiPropertyException;
use rocket\op\ei\manage\generic\ScalarEiProperty;
use rocket\op\ei\EiEngine;
use rocket\op\ei\util\filter\EiuFilterForm;
use rocket\op\ei\util\filter\controller\ScrFilterPropController;
use rocket\op\ei\manage\critmod\filter\data\FilterSettingGroup;
use rocket\op\ei\manage\critmod\filter\FilterDefinition;
use rocket\op\ei\util\filter\controller\FilterJhtmlHook;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\op\ei\util\privilege\EiuPrivilegeForm;
use rocket\op\ei\manage\security\privilege\data\PrivilegeSetting;
use rocket\op\ei\manage\ManageState;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\manage\gui\EiGuiDefinitionListener;
use rocket\op\ei\util\Eiu;
use rocket\ui\gui\EiGuiListener;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\op\ei\manage\frame\EiForkLink;
use rocket\op\ei\manage\gui\EiGuiDefinition;
use rocket\op\ei\util\gui\EiuGuiMaskDeclaration;
use rocket\ui\gui\ViewMode;
use rocket\op\ei\util\gui\EiuGuiDeclaration;
use rocket\ui\gui\GuiEntry;
use rocket\ui\gui\EiGuiDeclarationFactory;
use n2n\l10n\Lstr;
use rocket\ui\gui\EiGuiDeclaration;
use InvalidArgumentException;

class EiuEngine {

	private ?EiuType $eiuType = null;

	public function __construct(private EiEngine $eiEngine, private ?EiuMask $eiuMask, private EiuAnalyst $eiuAnalyst) {
	}
	
	/**
	 * @return EiEngine
	 */
	public function getEiEngine(): EiEngine {
		return $this->eiEngine;
	}
	
	/**
	 * @return EiuMask 
	 */
	public function mask() {
		if ($this->eiuMask !== null) {
			return $this->eiuMask;
		}
		
		return $this->eiuMask = new EiuMask($this->eiEngine->getEiMask(), $this, $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\op\ei\util\spec\EiuType
	 */
	public function type() {
		if ($this->eiuType !== null) {
			return $this->eiuType;
		}
		
		return $this->eiuType = new EiuType($this->getEiType(), $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\op\ei\EiType
	 */
	private function getEiType() {
		return $this->eiEngine->getEiMask()->getEiType();
	}

	public function supremeEngine(): EiuEngine {
		if (!$this->eiEngine->getEiMask()->getEiType()->hasSuperEiType()) {
			return $this;
		}
		
		return new EiuEngine($this->eiEngine->getSupremeEiEngine(), null, $this->eiuAnalyst);
	}
	
	public function removeGuiProp($defPropPath): void {
		$this->eiEngine->getEiGuiDefinition()->removeGuiPropByPath(DefPropPath::create($defPropPath));
	}
	
//	/**
//	 * @param mixed $eiPropArg See {@see EiPropPath::create()}
//	 * @return bool
//	 */
//	public function containsDraftProperty($eiPropArg) {
//		return $this->eiEngine->getDraftDefinition()->containsEiPropPath(EiPropPath::create($eiPropArg));
//	}
	
	public function getGuiPropOptions(?N2nLocale $n2nLocale = null) {
		/**
		 * @var ManageState $ms
		 */
		$ms = $this->eiuAnalyst->getN2nContext(true)->lookup(ManageState::class);
		
		$n2nLocale = $n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale();
		
		return array_map(
				function (Lstr $labelLstr) use ($n2nLocale) { return $labelLstr->t($n2nLocale); },
				$this->eiEngine->getEiMask()->getEiEngine()->getEiGuiDefinition()->getLabelLstrs());
	}
	
	/**
	 * @param mixed $eiPropArg See {@see EiPropPath::create()}
	 * @return bool
	 */
	public function containsGenericEiProperty($eiPropArg) {
		return $this->eiEngine->getGenericEiDefinition()->containsEiPropPath(EiPropPath::create($eiPropArg));
	}
	
	/**
	 * @param mixed $eiPropArg See {@see EiPropPath::create()}
	 * @return GenericEiProperty
	 * @throws UnknownGenericEiPropertyException
	 */
	public function getGenericEiProperty($eiPropArg, bool $required = true) {
		try {
			return $this->eiEngine->getGenericEiDefinition()->getGenericEiPropertyByEiPropPath(
					EiPropPath::create($eiPropArg));
		} catch (UnknownGenericEiPropertyException $e) {
			if (!$required) return null;
			
			throw $e;
		}
	}
	
	/**
	 * @return string[]
	 */
	public function getGenericEiPropertyOptions() {
		$options = array();
		foreach ($this->eiEngine->getGenericEiDefinition()->getGenericEiProperties() as $genericEiProperty) {
			$str = (string) $genericEiProperty->getEiPropPath();
			$options[$str] = $str . ' (' . $genericEiProperty->getLabelLstr()->t(N2nLocale::getAdmin()) . ')';		
		}
		return $options;
	}
	
	/**
	 * @param EiPropPath|string $eiPropPath
	 * @return boolean
	 */
	function containsScalarEiProperty($eiPropPath) {
		return $this->eiEngine->getScalarEiDefinition()->containsEiPropPath(EiPropPath::create($eiPropPath));
	}
	
	/**
	 * @param mixed $eiPropArg See {@see EiPropPath::create()}
	 * @return ScalarEiProperty
	 * @throws UnknownScalarEiPropertyException
	 */
	public function getScalarEiProperty($eiPropArg, bool $required = true) {
		try {
			return $this->eiEngine->getScalarEiDefinition()->getScalarEiPropertyByEiPropPath(
					EiPropPath::create($eiPropArg));
		} catch (UnknownScalarEiPropertyException $e) {
			if (!$required) return null;
			
			throw $e;
		}
	}
	
	/**
	 * @return \rocket\op\ei\manage\generic\ScalarEiProperty[]
	 */
	function getScalarEiProperties() {
		return $this->eiEngine->getScalarEiDefinition()->getScalarEiProperties();
	}
	
	/**
	 * @return string[]
	 */
	public function getScalarEiPropertyOptions() {
		$options = array();
		foreach ($this->eiEngine->getScalarEiDefinition()->getScalarEiProperties() as $scalarEiProperty) {
			$str = (string) $scalarEiProperty->getEiPropPath();
			$options[$str] = $str . ' (' . $scalarEiProperty->getLabelLstr()->t(N2nLocale::getAdmin()) . ')';
		}
		return $options;
	}
	
//	/**
//	 * @return ManageState
//	 */
//	private function getEiLaunch() {
//		return $this->eiuAnalyst->getN2nContext(true)->lookup(ManageState::class);
//	}
	
//	/**
//	 * @return FilterDefinition
//	 */
//	public function getFilterDefinition() {
//		return $this->eiEngine->getFilterDefinition($this->eiEngine->getEiMask());
//	}
//	/**
//	 * @return boolean
//	 */
//	public function hasFilterProps() {
//		return !$this->getFilterDefinition()->isEmpty();
//	}
	
	/**
	 * @return \rocket\op\ei\manage\security\filter\SecurityFilterDefinition
	 */
	public function getSecurityFilterDefinition() {
		return $this->eiEngine->getSecurityFilterDefinition($this->eiEngine->getEiMask());
	}
	
	/**
	 * @return boolean
	 */
	public function hasSecurityFilterProps() {
		return !$this->getSecurityFilterDefinition()->isEmpty();
	}
	
	
//	/**
//	 * @return SortDefinition
//	 */
//	public function getSortDefinition(): SortDefinition {
//		return $this->eiEngine->getSortDefinition($this->eiEngine->getEiMask());
//	}
	
//	/**
//	 * @return boolean
//	 */
//	public function hasSortProps(): bool {
//		return !$this->getSortDefinition()->isEmpty();
//	}
	
//	/**
//	 * @return EiGuiDefinition
//	 */
//	public function getEiGuiDefinition() {
//		return $this->eiEngine->getEiGuiDefinition($this->eiEngine->getEiMask());
//	}
	
	/**
	 * @return \rocket\op\ei\manage\security\privilege\PrivilegeDefinition
	 */
	public function getPrivilegeDefinition() {
		return $this->eiEngine->getPrivilegeDefinition($this->eiEngine->getEiMask());
	}
	
// 	/**
// 	 * @return \rocket\op\ei\manage\gui\EiGuiDefinition
// 	 */
// 	public function getEiGuiDefinition() {
// 		return $this->eiEngine->getEiGuiDefinition($this->eiEngine->getEiMask());
// 	}
	
	/**
	 * @return \rocket\op\ei\manage\idname\IdNameDefinition
	 */
	public function getIdNameDefinition() {
		return $this->eiEngine->getIdNameDefinition($this->eiEngine->getEiMask());
	}
	
	
	/**
	 * @param mixed $eiObjectArg
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString($eiObjectArg, bool $determineEiMask = true,
			?N2nLocale $n2nLocale = null): string {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', $this->getEiType());
				
		$eiMask = $this->getEiEngine()->getEiMask();
		if ($determineEiMask) {
			$eiMask = $eiMask->determineEiMask($eiObject->getEiEntityObj()->getEiType());
		}
		
		$n2nContext = $this->eiuAnalyst->getN2nContext(true);
		return $eiMask->getEiEngine()->getIdNameDefinition()->createIdentityString($eiObject, $n2nContext,
				$n2nLocale ?? $n2nContext->getN2nLocale());
	}
	
	public function onNewGuiEntry(\Closure $callback) {
		$this->eiEngine->getEiGuiDefinition()->registerEiGuiDefinitionListener(new ClosureEiGuiListener($callback));
		return $this;
	}
	
	/**
	 * @param mixed $eiPropPath
	 * @throws InvalidArgumentException
	 * @return boolean
	 */
	public function containsGuiProp($eiPropPath): bool {
		return $this->eiEngine->getEiGuiDefinition()->containsGuiProp(DefPropPath::create($eiPropPath));
	}

//	/**
//	 * @param mixed $eiPropPath
//	 * @throws \InvalidArgumentException
//	 * @throws GuiException
//	 * @return EiPropPath
//	 */
//	public function eiPropPathToEiPropPath($eiPropPath) {
//		return $this->getEiGuiDefinition()->eiPropPathToEiPropPath(DefPropPath::create($eiPropPath));
//	}
	
	
//	/**
//	 * @param FilterSettingGroup|null $rootGroup
//	 * @return \rocket\op\ei\util\filter\EiuFilterForm
//	 */
//	public function newFilterForm(?FilterSettingGroup $rootGroup = null) {
//		return $this->createEiuFilterForm(
//				$this->getFilterDefinition(),
//				ScrFilterPropController::buildFilterJhtmlHook(
//						$this->eiuAnalyst->getN2nContext(true)->lookup(ScrRegistry::class),
//						$this->eiEngine->getEiMask()->getEiTypePath()),
//				$rootGroup);
//	}
		
	/**
	 * @param FilterSettingGroup|null $rootGroup
	 * @return \rocket\op\ei\util\filter\EiuFilterForm
	 */
	public function newSecurityFilterForm(?FilterSettingGroup $rootGroup = null) {
		return $this->createEiuFilterForm(
				$this->getSecurityFilterDefinition()->toFilterDefinition(),
				ScrFilterPropController::buildSecurityFilterJhtmlHook(
						$this->eiuAnalyst->getN2nContext(true)->lookup(ScrRegistry::class),
						$this->eiEngine->getEiMask()->getEiTypePath()),
				$rootGroup);
				
	}
	
	/**
	 * @param FilterDefinition $fd
	 * @param FilterJhtmlHook $fjh
	 * @param FilterSettingGroup|null $rg
	 * @return EiuFilterForm
	 */
	private function createEiuFilterForm(FilterDefinition $fd, FilterJhtmlHook $fjh, 
			?FilterSettingGroup $rg = null) {
		return new EiuFilterForm($fd, $fjh, $rg, $this->eiuAnalyst);
	}
	
	
//	/**
//	 * @param SortSettingGroup|null $sortSetting
//	 * @return \rocket\op\ei\util\sort\EiuSortForm
//	 */
//	public function newSortForm(?SortSettingGroup $sortSetting = null) {
//		return new EiuSortForm($this->getSortDefinition(), $sortSetting, $this->eiuAnalyst);
//	}
//
	/**
	 * @return boolean
	 */
	public function hasPrivileges() {
		return !$this->getPrivilegeDefinition()->isEmpty();
	}
	
	/**
	 * @param PrivilegeSetting|null $privilegeSetting
	 * @return \rocket\op\ei\util\privilege\EiuPrivilegeForm
	 */
	public function newPrivilegeForm(?PrivilegeSetting $privilegeSetting = null) {
		return new EiuPrivilegeForm($this->getPrivilegeDefinition(), $privilegeSetting, $this->eiuAnalyst);	
	}
	
// 	/**
// 	 * @param object $eiObjectArg
// 	 * @param N2nLocale $n2nLocale
// 	 * @return string
// 	 */
// 	public function createIdentityString(object $eiObjectArg, ?N2nLocale $n2nLocale = null): string {
// 		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', $this->eiuMask->getEiMask()->getEiType());
// 		return $this->getEiGuiDefinition()
// 				->createIdentityString($eiObject, $this->eiuAnalyst->getN2nContext(true),
// 						$n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
// 	}

	public function newFrame(EiForkLink $eiForkLink): EiuFrame {
		$newEiFrame = $this->eiEngine->createForkEiFrame($eiForkLink);
		
		$newEiuAnalyst = new EiuAnalyst();
		$newEiuAnalyst->applyEiArgs($newEiFrame);
		
		return new EiuFrame($newEiFrame, $newEiuAnalyst);
	}

	/**
	 * @param int $viewMode
	 * @param array|null $defPropPathsArg
	 * @return EiuGuiDeclaration
	 */
	function newGuiDeclaration(int $viewMode, ?array $defPropPathsArg = null): EiuGuiDeclaration {
		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);

		$eiGuiDeclaration = new EiGuiDeclaration($this->eiEngine->getEiMask(), $viewMode);
		$eiGuiDeclaration->putEiGuiMaskDeclaration(
				$this->eiEngine->obtainEiGuiMaskDeclaration($viewMode, $defPropPaths));
		
		return new EiuGuiDeclaration($eiGuiDeclaration, $this->eiuAnalyst);
	}

	/**
	 * @param int $viewMode
	 * @param array|null $defPropPaths
	 * @return EiuGuiMaskDeclaration
	 */
	function newGuiMaskDeclaration(int $viewMode, ?array $defPropPaths = null): EiuGuiMaskDeclaration {
		$defPropPaths = DefPropPath::buildArray($defPropPaths);
		return new EiuGuiMaskDeclaration($this->eiEngine->obtainEiGuiMaskDeclaration($viewMode, $defPropPaths),
				$this->eiuAnalyst);
	}

	function newMultiGuiDeclaration(bool $bulky = true, bool $readOnly = false, bool $nonAbstractsOnly = true,
			?array $allowedEiTypesArg = null, ?array $defPropPathsArg = null): EiuGuiDeclaration {
		$viewMode = ViewMode::determine($bulky, $readOnly, true);

		$allowedEiTypes = EiuAnalyst::buildEiTypesFromEiArg($allowedEiTypesArg);
		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);

		$eiGuiDeclarationFactory = new EiGuiDeclarationFactory($this->eiEngine->getEiMask(),
				$this->eiuAnalyst->getN2nContext(true));
		$eiGuiDeclaration =  $eiGuiDeclarationFactory->createMultiEiGuiDeclaration($viewMode, $nonAbstractsOnly,
				$allowedEiTypes, $defPropPaths);
		return new EiuGuiDeclaration($eiGuiDeclaration, $this->eiuAnalyst);
	}
}

class ClosureEiGuiDefinitionListener implements EiGuiDefinitionListener {
	private $callback;
	
	public function __construct(\Closure $callback) {
		$this->callback = $callback;
	}
	
	public function onNewEiGuiMaskDeclaration(EiGuiMaskDeclaration $eiGuiMaskDeclaration): void {
		$c = $this->callback;
		$c(new Eiu($eiGuiMaskDeclaration));
	}
	
	public function onEiGuiMaskDeclarationInitialized(EiGuiMaskDeclaration $eiGuiMaskDeclaration) {
	}

}

class ClosureEiGuiListener implements EiGuiListener, EiGuiDefinitionListener {

	public function __construct(private \Closure $eiGuiEntryCallback) {
	}
	
	public function onNewEiGuiEntry(GuiEntry $eiGuiEntry): void {
		$c = $this->eiGuiEntryCallback;
		$c(new Eiu($eiGuiEntry));
	}
	
	public function onInitialized(EiGuiMaskDeclaration $eiGuiMaskDeclaration) {
		
	}

	public function onNewEiGuiMaskDeclaration(EiGuiMaskDeclaration $eiGuiMaskDeclaration): void {
		$eiGuiMaskDeclaration->registerEiGuiListener($this);
	}
	
	public function onGiBuild(EiGuiMaskDeclaration $eiGuiMaskDeclaration) {
		
	}


	
}
