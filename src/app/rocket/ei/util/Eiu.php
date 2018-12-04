<?php
namespace rocket\ei\util;

use n2n\context\Lookupable;
use n2n\l10n\DynamicTextCollection;
use rocket\core\model\Rocket;
use n2n\core\container\N2nContext;
use rocket\ei\util\spec\EiuContext;

class Eiu implements Lookupable {
	private $eiuAnalyst;
	private $eiuContext;
	private $eiuEngine;
	private $eiuMask;
	private $eiuProp;
	private $eiuFrame;
	private $eiuObject;
	private $eiuEntry;
	private $eiuFieldMap;
	private $eiuGui;
	private $eiuEntryGui;
	private $eiuField;
	private $eiuFactory;
	
	public function __construct(...$eiArgs) {
		$this->eiuAnalyst = new EiuAnalyst();
		$this->eiuAnalyst->applyEiArgs(...$eiArgs);
	}
	
	private function _init(N2nContext $n2nContext) {
		$this->eiuAnalyst->applyEiArgs($n2nContext);
	}
	
	/**
	 * @return EiuContext|null
	 */
	public function context(bool $required = true) {
		if ($this->eiuContext !== null) {
			return $this->eiuContext;
		}
		
		return $this->eiuContext = $this->eiuAnalyst->getEiuContext($required);
		
	}
	
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	public function engine(bool $required = true) {
		if ($this->eiuEngine !== null) {
			return $this->eiuEngine;
		}
		
		return $this->eiuEngine = $this->eiuAnalyst->getEiuEngine($required);
	}
	
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\spec\EiuMask
	 */
	public function mask(bool $required = true) {
		if ($this->eiuMask !== null) {
			return $this->eiuMask;
		}
		
		return $this->eiuMask = $this->eiuAnalyst->getEiuMask($required);
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\ei\util\spec\EiuProp
	 */
	public function prop(bool $required = true) {
		if ($this->eiuProp !== null) {
			return $this->eiuProp;
		}
		
		return $this->eiuProp = $this->eiuAnalyst->getEiuProp($required);
	}
	
	/**
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	public function frame(bool $required = true)  {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		return $this->eiuFrame = $this->eiuAnalyst->getEiuFrame($required);
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\entry\EiuObject
	 */
	public function object(bool $required = true) {
		if ($this->eiuObject !== null) {
			return $this->eiuObject;
		}
		
		return $this->eiuObject = $this->eiuAnalyst->getEiuObject($required);
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	public function entry(bool $required = true) {
		if ($this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		return $this->eiuEntry = $this->eiuAnalyst->getEiuEntry($required);
	}
	
	public function fieldMap(bool $required = true) {
		if ($this->eiuFieldMap !== null) {
			return $this->eiuFieldMap;
		}
		
		return $this->eiuFieldMap = $this->eiuAnalyst->getEiuFieldMap($required);
	}
	
	/**
	 * 
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	public function gui(bool $required = true) {
		if ($this->eiuGui !== null) {
			return $this->eiuGui;
		}
		
		return $this->eiuGui = $this->eiuAnalyst->getEiuGui($required);
	}
	
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuEntryGui
	 */
	public function entryGui(bool $required = true) {
		if ($this->eiuEntryGui !== null) {
			return $this->eiuEntryGui;
		}
		
		return $this->eiuEntryGui = $this->eiuAnalyst->getEiuEntryGui($required);
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return NULL|\rocket\ei\util\entry\EiuField
	 */
	public function field(bool $required = true) {
		if ($this->eiuField !== null) {
			return $this->eiuField;
		}
		
		return $this->eiuField = $this->eiuAnalyst->getEiuField($required);
	}
	
	/**
	 * @return \rocket\ei\util\EiuFactory
	 */
	public function factory() {
		if ($this->eiuFactory === null) {
			$this->eiuFactory = new EiuFactory();
		}
		
		return $this->eiuFactory;
	}
	
	/**
	 * @param string|\ReflectionClass $lookupId
	 * @return mixed
	 */
	public function lookup($lookupId, bool $required = true) {
		return $this->eiuAnalyst->getN2nContext(true)->lookup($lookupId, $required);
	}
	
	/**
	 * @param string ...$moduleNamespaces
	 * @return \n2n\l10n\DynamicTextCollection
	 */
	public function dtc(string ...$moduleNamespaces) {
		return new DynamicTextCollection($moduleNamespaces, $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
	/**
	 * @return \n2n\core\container\N2nContext
	 */
	public function getN2nContext() {
		return $this->eiuAnalyst->getN2nContext(true);
	}
	
	/**
	 * @return \n2n\l10n\N2nLocale
	 */
	public function getN2nLocale() {
		return $this->getN2nContext()->getN2nLocale();
	}
	
	/**
	 * @return \rocket\ei\util\EiuAnalyst
	 */
	public function getEiuAnalyst() {
		return $this->eiuAnalyst;
	}
}