<?php
namespace rocket\ei\util\spec;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\ei\manage\generic\UnknownGenericEiPropertyException;
use rocket\ei\manage\generic\GenericEiProperty;
use rocket\ei\manage\generic\UnknownScalarEiPropertyException;
use rocket\ei\manage\generic\ScalarEiProperty;
use rocket\ei\EiEngine;
use rocket\ei\util\filter\EiuFilterForm;
use rocket\ei\util\filter\controller\ScrFilterPropController;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\util\filter\controller\FilterJhtmlHook;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\ei\util\privilege\EiuPrivilegeForm;
use rocket\ei\manage\security\privilege\data\PrivilegeSetting;
use rocket\ei\util\sort\EiuSortForm;
use rocket\ei\manage\critmod\sort\SortSettingGroup;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\GuiFieldPath;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\GuiDefinitionListener;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\EiGuiListener;

class EiuEngine {
	private $eiEngine;
	private $eiuMask;
	private $eiuAnalyst;
	
	public function __construct(EiEngine $eiEngine, EiuMask $eiuMask = null, EiuAnalyst $eiuAnalyst) {
		$this->eiEngine = $eiEngine;
		$this->eiuMask = $eiuMask;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\EiEngine
	 */
	public function getEiEngine() {
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
	 * @return \rocket\ei\EiType
	 */
	public function getEiType() {
		return $this->eiEngine->getEiMask()->getEiType();
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	public function supremeEngine() {
		if (!$this->eiEngine->hasSuperEiEngine()) {
			return $this;
		}
		
		return new EiuEngine($this->eiEngine->getSupremeEiEngine(), $this->n2nContext);
	}
	
	public function removeGuiProp($guiFieldPath) {
		$this->getGuiDefinition()->removeGuiPropByPath(GuiFieldPath::create($guiFieldPath));
	}
	
	/**
	 * @param mixed $eiPropArg See {@see EiPropPath::create()}
	 * @return bool
	 */
	public function containsDraftProperty($eiPropArg) {
		return $this->eiEngine->getDraftDefinition()->containsEiPropPath(EiPropPath::create($eiPropArg));
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
	
	/**
	 * @return ManageState
	 */
	private function getManageState() {
		return $this->eiuAnalyst->getN2nContext(true)->lookup(ManageState::class);
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\FilterDefinition
	 */
	public function getFilterDefinition() {
		return $this->getManageState()->getDef()->getFilterDefinition($this->eiEngine->getEiMask());
	}
	
	/**
	 * @return boolean
	 */
	public function hasFilterProps() {
		return !$this->getFilterDefinition()->isEmpty();
	}
	
	/**
	 * @return \rocket\ei\manage\security\filter\SecurityFilterDefinition
	 */
	public function getSecurityFilterDefinition() {
		return $this->getManageState()->getDef()->getSecurityFilterDefinition($this->eiEngine->getEiMask());
	}
	
	/**
	 * @return boolean
	 */
	public function hasSecurityFilterProps() {
		return !$this->getSecurityFilterDefinition()->isEmpty();
	}
	
	
	/**
	 * @return \rocket\ei\manage\critmod\sort\SortDefinition
	 */
	public function getSortDefinition() {
		return $this->getManageState()->getDef()->getSortDefinition($this->eiEngine->getEiMask());
	}
	
	/**
	 * @return boolean
	 */
	public function hasSortProps() {
		return !$this->getSortDefinition()->isEmpty();
	}
	
	/**
	 * @return \rocket\ei\manage\security\privilege\PrivilegeDefinition
	 */
	public function getPrivilegeDefinition() {
		return $this->getManageState()->getDef()->getPrivilegeDefinition($this->eiEngine->getEiMask());
	}
	
	/**
	 * @return \rocket\ei\manage\gui\GuiDefinition
	 */
	public function getGuiDefinition() {
		return $this->getManageState()->getDef()->getGuiDefinition($this->eiEngine->getEiMask());
	}
	
	public function onNewGui(\Closure $callback) {
		$this->getGuiDefinition()->registerGuiDefinitionListener(new ClosureGuiDefinitionListener($callback));
	}
	
	public function onNewEntryGui(\Closure $callback) {
		$this->getGuiDefinition()->registerGuiDefinitionListener(new ClosureEiGuiListener($callback));
	}
	
	/**
	 * @param mixed $eiPropPath
	 * @throws \InvalidArgumentException
	 * @return boolean
	 */
	public function containsGuiProp($eiPropPath) {
		return $this->getGuiDefinition()->containsGuiProp(GuiFieldPath::create($eiPropPath));
	}

	/**
	 * @param mixed $eiPropPath
	 * @throws \InvalidArgumentException
	 * @throws GuiException
	 * @return EiPropPath
	 */
	public function eiPropPathToEiPropPath($eiPropPath) {
		return $this->getGuiDefinition()->eiPropPathToEiPropPath(GuiFieldPath::create($eiPropPath));
	}
	
	
	/**
	 * @param FilterSettingGroup|null $rootGroup
	 * @return \rocket\ei\util\filter\EiuFilterForm
	 */
	public function newFilterForm(FilterSettingGroup $rootGroup = null) {
		return $this->createEiuFilterForm(
				$this->getFilterDefinition(),
				ScrFilterPropController::buildFilterJhtmlHook(
						$this->eiuAnalyst->getN2nContext(true)->lookup(ScrRegistry::class), 
						$this->eiEngine->getEiMask()->getEiTypePath()),
				$rootGroup);
	}
	
	
	/**
	 * @param FilterSettingGroup|null $rootGroup
	 * @return \rocket\ei\util\filter\EiuFilterForm
	 */
	public function newSecurityFilterForm(FilterSettingGroup $rootGroup = null) {
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
			FilterSettingGroup $rg = null) {
		return new EiuFilterForm($fd, $fjh, $rg, $this->eiuAnalyst);
	}
	
	
	/**
	 * @param SortSettingGroup|null $sortSetting
	 * @return \rocket\ei\util\sort\EiuSortForm
	 */
	public function newSortForm(SortSettingGroup $sortSetting = null) {
		return new EiuSortForm($this->getSortDefinition(), $sortSetting, $this->eiuAnalyst);
	}
	
	/**
	 * @return boolean
	 */
	public function hasPrivileges() {
		return !$this->getPrivilegeDefinition()->isEmpty();
	}
	
	/**
	 * @param PrivilegeSetting|null $privilegeSetting
	 * @return \rocket\ei\util\privilege\EiuPrivilegeForm
	 */
	public function newPrivilegeForm(PrivilegeSetting $privilegeSetting = null) {
		return new EiuPrivilegeForm($this->getPrivilegeDefinition(), $privilegeSetting, $this->eiuAnalyst);	
	}
	
	/**
	 * @param object $eiObjectArg
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(object $eiObjectArg, N2nLocale $n2nLocale = null): string {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', $this->eiuMask->getEiMask()->getEiType());
		return $this->getGuiDefinition()
				->createIdentityString($eiObject, $this->eiuAnalyst->getN2nContext(true),
						$n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
}

class ClosureGuiDefinitionListener implements GuiDefinitionListener {
	private $callback;
	
	public function __construct(\Closure $callback) {
		$this->callback = $callback;
	}
	
	public function onNewEiGui(EiGui $eiGui) {
		$c = $this->callback;
		$c(new Eiu($eiGui));
	}
	
	public function onEiGuiInitialized(EiGui $eiGui) {
	}

}

class ClosureEiGuiListener implements EiGuiListener, GuiDefinitionListener {
	private $eiEntryGuiCallback;
	
	public function __construct(\Closure $eiEntryGuiCallback) {
		$this->eiEntryGuiCallback = $eiEntryGuiCallback;
	}
	
	public function onNewEiEntryGui(EiEntryGui $eiEntryGui) {
		$c = $this->eiEntryGuiCallback;
		$c(new Eiu($eiEntryGui));
	}
	
	public function onInitialized(EiGui $eiGui) {
		
	}

	public function onNewView(HtmlView $view) {
	}

	public function onNewEiGui(EiGui $eiGui) {
		$eiGui->registerEiGuiListener($this);
	}


	
}
