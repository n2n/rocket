<?php
namespace rocket\spec\ei\manage\util\model;

use n2n\l10n\N2nLocale;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\component\UnknownEiComponentException;
use rocket\spec\ei\manage\generic\UnknownGenericEiPropertyException;
use rocket\spec\ei\manage\generic\GenericEiProperty;
use rocket\spec\ei\component\command\EiCommand;
use rocket\spec\ei\component\prop\EiProp;
use rocket\spec\ei\component\modificator\EiModificator;

class EiuEngine {
	private $eiEngine;
	private $n2nContext;
	
	/**
	 * @param mixed ...$eiArgs
	 */
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		
		$this->eiEngine = $eiuFactory->getEiEngine(true);
		$this->n2nContext = $eiuFactory->getN2nContext(false);
	}
	
	/**
	 * @return \rocket\spec\ei\EiEngine
	 */
	public function getEiEngine() {
		return $this->eiEngine;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuEngine
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
	
	public function containsEiProp($eiPropPath) {
		return $this->eiEngine->getEiPropCollection()->containsId(EiPropPath::create($eiPropPath));
	}
	
	/**
	 * @param string|EiPropPath|\rocket\spec\ei\component\prop\EiProp $eiPropArg
	 * @param bool $required
	 * @throws UnknownEiComponentException
	 * @return \rocket\spec\ei\manage\util\model\EiuProp|null
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
	 * @param EiProp $eiProp
	 * @param bool $prepend
	 * @return \rocket\spec\ei\manage\util\model\EiuEngine
	 */
	public function addEiProp(EiProp $eiProp, bool $prepend = false) {
		$this->eiEngine->getEiPropCollection()->add($eiProp, $prepend);
		return $this;
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @param bool $prepend
	 * @return \rocket\spec\ei\manage\util\model\EiuEngine
	 */
	public function addEiCommand(EiCommand $eiCommand, bool $prepend = false) {
		$this->eiEngine->getEiCommandCollection()->add($eiCommand, $prepend);
		return $this;
	}
	
	/**
	 * @param EiModificator $eiModificator
	 * @param bool $prepend
	 * @return \rocket\spec\ei\manage\util\model\EiuEngine
	 */
	public function addEiModificator(EiModificator $eiModificator, bool $prepend = false) {
		$this->eiEngine->getEiModificatorCollection()->add($eiModificator, $prepend);
		return $this;
	}
}