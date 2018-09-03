<?php
namespace rocket\ei\util\model;

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
use rocket\ei\manage\critmod\filter\data\FilterPropSettingGroup;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\util\filter\controller\FilterJhtmlHook;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\ei\util\privilege\EiuPrivilegeForm;
use rocket\ei\manage\security\privilege\data\PrivilegeSetting;
use rocket\ei\util\sort\EiuSortForm;
use rocket\ei\manage\critmod\sort\SortSetting;

class EiuEngine {
	private $eiEngine;
	private $eiuMask;
	private $eiuFactory;
	
	public function __construct(EiEngine $eiEngine, EiuMask $eiuMask = null, EiuFactory $eiuFactory = null) {
		$this->eiEngine = $eiEngine;
		$this->eiuMask = $eiuMask;
		$this->eiuFactory = $eiuFactory;
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
		
		return $this->eiuMask = new EiuMask($this->eiEngine->getEiMask(), $this, $this->eiuFactory);
	}
	
	/**
	 * @return \rocket\ei\EiType
	 */
	public function getEiType() {
		return $this->eiEngine->getEiMask()->getEiType();
	}
	
	/**
	 * @return \rocket\ei\util\model\EiuEngine
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
		return $this->eiEngine->getEiPropCollection()->containsId(EiPropPath::create($eiPropPath));
	}
	
	/**
	 * @param string|EiPropPath|\rocket\ei\component\prop\EiProp $eiPropArg
	 * @param bool $required
	 * @throws UnknownEiComponentException
	 * @return \rocket\ei\util\model\EiuProp|null
	 */
	public function prop($eiPropArg, bool $required = true) {
		$eiPropPath = EiPropPath::create($eiPropArg);
		try {
			$this->eiEngine->getEiPropCollection()->getById((string) $eiPropPath);
		} catch (UnknownEiComponentException $e) {
			if (!$required) return null;
			
			throw $e;
		}
		
		return new EiuProp($eiPropPath, $this);
	}
	
	/**
	 * @var \rocket\ei\manage\critmod\filter\FilterDefinition
	 */
	private $filterDefinition;
	/**
	 * @var \rocket\ei\manage\critmod\sort\SortDefinition
	 */
	private $sortDefinition;
	/**
	 * @var \rocket\ei\manage\security\filter\SecurityFilterDefinition
	 */
	private $securityFilterDefinition;
	/**
	 * @var \rocket\ei\manage\security\privilege\PrivilegeDefinition
	 */
	private $privilegeDefinition;
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\FilterDefinition
	 */
	public function getFilterDefinition() {
		if ($this->filterDefinition !== null) {
			return $this->filterDefinition;	
		}
		
		return $this->filterDefinition = $this->eiEngine->createFilterDefinition(
				$this->eiuFactory->getN2nContext(true));
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
		if ($this->securityFilterDefinition !== null) {
			return $this->securityFilterDefinition;
		}
		
		return $this->securityFilterDefinition = $this->eiEngine->createSecurityFilterDefinition(
				$this->eiuFactory->getN2nContext(true));
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
		if ($this->sortDefinition !== null) {
			return $this->sortDefinition;
		}
		
		return $this->sortDefinition = $this->eiEngine->createSortDefinition(
				$this->eiuFactory->getN2nContext(true));
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
		if ($this->privilegeDefinition !== null) {
			return $this->privilegeDefinition;
		}
		
		return $this->privilegeDefinition = $this->eiEngine->createPrivilegeDefinition(
				$this->eiuFactory->getN2nContext(true));
	}
	
	/**
	 * @param FilterPropSettingGroup|null $rootGroup
	 * @return \rocket\ei\util\filter\EiuFilterForm
	 */
	public function newFilterForm(FilterPropSettingGroup $rootGroup = null) {
		return $this->createEiuFilterForm(
				$this->getFilterDefinition(),
				ScrFilterPropController::buildFilterJhtmlHook(
						$this->eiuFactory->getN2nContext(true)->lookup(ScrRegistry::class), 
						$this->eiEngine->getEiMask()->getEiTypePath()),
				$rootGroup);
	}
	
	
	/**
	 * @param FilterPropSettingGroup|null $rootGroup
	 * @return \rocket\ei\util\filter\EiuFilterForm
	 */
	public function newSecurityFilterForm(FilterPropSettingGroup $rootGroup = null) {
		return $this->createEiuFilterForm(
				$this->getSecurityFilterDefinition()->toFilterDefinition(),
				ScrFilterPropController::buildSecurityFilterJhtmlHook(
						$this->eiuFactory->getN2nContext(true)->lookup(ScrRegistry::class),
						$this->eiEngine->getEiMask()->getEiTypePath()),
				$rootGroup);
				
	}
	
	/**
	 * @param FilterDefinition $fd
	 * @param FilterJhtmlHook $fjh
	 * @param FilterPropSettingGroup|null $rg
	 * @return EiuFilterForm
	 */
	private function createEiuFilterForm(FilterDefinition $fd, FilterJhtmlHook $fjh, 
			FilterPropSettingGroup $rg = null) {
		return new EiuFilterForm($fd, $fjh, $rg, $this->eiuFactory);
	}
	
	
	/**
	 * @param SortSetting|null $sortSetting
	 * @return \rocket\ei\util\sort\EiuSortForm
	 */
	public function newSortForm(SortSetting $sortSetting = null) {
		return new EiuSortForm($this->getSortDefinition(), $sortSetting, $this->eiuFactory);
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
		return new EiuPrivilegeForm($this->getPrivilegeDefinition(), $privilegeSetting, $this->eiuFactory);
		
	}
}