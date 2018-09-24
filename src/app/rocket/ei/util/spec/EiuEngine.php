<?php
namespace rocket\ei\util\spec;

use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\ei\component\UnknownEiComponentException;
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
use rocket\ei\manage\gui\GuiIdPath;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\util\EiuAnalyst;

class EiuEngine {
	private $eiEngine;
	private $eiuMask;
	private $eiuAnalyst;
	
	public function __construct(EiEngine $eiEngine, EiuMask $eiuMask = null, EiuAnalyst $eiuAnalyst = null) {
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
	public function getEiuMask() {
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
	
	/**
	 * @param mixed $eiPropArg See {@see EiPropPath::create()}
	 * @return bool
	 */
	public function containsGenericEiProperty($eiPropArg) {
		return $this->eiEngine->getGenericEiDefinition()->getMap()->offsetExists($eiPropArg);
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
	
	
	public function containsEiProp($eiPropPath) {
		return $this->eiEngine->getEiMask()->getEiPropCollection()->containsId(EiPropPath::create($eiPropPath));
	}
	
	/**
	 * @param string|EiPropPath|\rocket\ei\component\prop\EiProp $eiPropArg
	 * @param bool $required
	 * @throws UnknownEiComponentException
	 * @return \rocket\ei\util\spec\EiuProp|null
	 */
	public function prop($eiPropArg, bool $required = true) {
		$eiPropPath = EiPropPath::create($eiPropArg);
		try {
			$this->eiEngine->getEiMask()->getEiPropCollection()->getById((string) $eiPropPath);
		} catch (UnknownEiComponentException $e) {
			if (!$required) return null;
			
			throw $e;
		}
		
		return new EiuProp($eiPropPath, $this);
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
	
	/**
	 * @param mixed $guiIdPath
	 * @throws \InvalidArgumentException
	 * @return boolean
	 */
	public function containsGuiProp($guiIdPath) {
		return $this->getGuiDefinition()->containsGuiProp(GuiIdPath::create($guiIdPath));
	}

	/**
	 * @param mixed $guiIdPath
	 * @throws \InvalidArgumentException
	 * @throws GuiException
	 * @return EiPropPath
	 */
	public function guiIdPathToEiPropPath($guiIdPath) {
		return $this->getGuiDefinition()->guiIdPathToEiPropPath(GuiIdPath::create($guiIdPath));
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
}
