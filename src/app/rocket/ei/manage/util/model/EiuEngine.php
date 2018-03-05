<?php
namespace rocket\ei\manage\util\model;

use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\ei\component\UnknownEiComponentException;
use rocket\ei\manage\generic\UnknownGenericEiPropertyException;
use rocket\ei\manage\generic\GenericEiProperty;
use rocket\ei\component\command\EiCommand;
use rocket\ei\component\prop\EiProp;
use rocket\ei\component\modificator\EiModificator;
use rocket\ei\manage\generic\UnknownScalarEiPropertyException;
use rocket\ei\manage\generic\ScalarEiProperty;

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
	 * @return \rocket\ei\EiEngine
	 */
	public function getEiEngine() {
		return $this->eiEngine;
	}
	
	/**
	 * @return \rocket\ei\EiType
	 */
	public function getEiType() {
		return $this->eiEngine->getEiMask()->getEiType();
	}
	
	/**
	 * @return \rocket\ei\manage\util\model\EiuEngine
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
	
	
	public function containsEiProp($eiPropPath) {
		return $this->eiEngine->getEiPropCollection()->containsId(EiPropPath::create($eiPropPath));
	}
	
	/**
	 * @param string|EiPropPath|\rocket\ei\component\prop\EiProp $eiPropArg
	 * @param bool $required
	 * @throws UnknownEiComponentException
	 * @return \rocket\ei\manage\util\model\EiuProp|null
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
	 * @return \rocket\ei\manage\util\model\EiuEngine
	 */
	public function addEiProp(EiProp $eiProp, bool $prepend = false) {
		$this->eiEngine->getEiMask()->getEiPropCollection()->add($eiProp, $prepend);
		return $this;
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @param bool $prepend
	 * @return \rocket\ei\manage\util\model\EiuEngine
	 */
	public function addEiCommand(EiCommand $eiCommand, bool $prepend = false) {
		$this->eiEngine->getEiMask()->getEiCommandCollection()->add($eiCommand, $prepend);
		return $this;
	}
	
	/**
	 * @param EiModificator $eiModificator
	 * @param bool $prepend
	 * @return \rocket\ei\manage\util\model\EiuEngine
	 */
	public function addEiModificator(EiModificator $eiModificator, bool $prepend = false) {
		$this->eiEngine->getEiMask()->getEiModificatorCollection()->add($eiModificator, $prepend);
		return $this;
	}
}