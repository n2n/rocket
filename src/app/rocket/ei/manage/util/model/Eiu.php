<?php
namespace rocket\ei\manage\util\model;

use n2n\context\Lookupable;
use n2n\l10n\DynamicTextCollection;
use rocket\core\model\Rocket;

class Eiu implements Lookupable {
	private $eiuFactory;
	private $eiuContext;
	private $eiuEngine;
	private $eiuMask;
	private $eiuFrame;
	private $eiuEntry;
	private $eiuGui;
	private $eiuEntryGui;
	private $eiuField;
	
	public function __construct(...$eiArgs) {
		$this->eiuFactory = new EiuFactory();
		$this->eiuFactory->applyEiArgs(...$eiArgs);
	}
	
	/**
	 * @return EiuContext|null
	 */
	public function context(bool $required = true) {
		if ($this->eiuContext !== null) {
			return $this->eiuContext;
		}
		
		return $this->eiuContext = $this->eiuFactory->getEiuContext($required);
		
	}
	
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuEngine
	 */
	public function engine(bool $required = true) {
		if ($this->eiuEngine !== null) {
			return $this->eiuEngine;
		}
		
		return $this->eiuEngine = $this->eiuFactory->getEiuEngine($required);
	}
	
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuMask
	 */
	public function mask(bool $required = true) {
		if ($this->eiuMask !== null) {
			return $this->eiuMask;
		}
		
		return $this->eiuMask = $this->eiuFactory->getEiuMask($required);
	}
	
	/**
	 * @return \rocket\ei\manage\util\model\EiuFrame
	 */
	public function frame(bool $required = true)  {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		return $this->eiuFrame = $this->eiuFactory->getEiuFrame($required);
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuEntry
	 */
	public function entry(bool $required = true) {
		if ($this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		return $this->eiuEntry = $this->eiuFactory->getEiuEntry($required);
	}
	
	/**
	 * 
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuGui
	 */
	public function gui(bool $required = true) {
		if ($this->eiuGui !== null) {
			return $this->eiuGui;
		}
		
		return $this->eiuGui = $this->eiuFactory->getEiuGui($required);
	}
	
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuEntryGui
	 */
	public function entryGui(bool $required = true) {
		if ($this->eiuEntryGui !== null) {
			return $this->eiuEntryGui;
		}
		
		return $this->eiuEntryGui = $this->eiuFactory->getEiuEntryGui($required);
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return NULL|\rocket\ei\manage\util\model\EiuField
	 */
	public function field(bool $required = true) {
		if ($this->eiuField !== null) {
			return $this->eiuField;
		}
		
		return $this->eiuField = $this->eiuFactory->getEiuField($required);
	}
	
	/**
	 * @param string|\ReflectionClass $lookupId
	 * @return mixed
	 */
	public function lookup($lookupId, bool $required = true) {
		return $this->eiuFactory->getN2nContext(true)->lookup($lookupId, $required);
	}
	
	/**
	 * @param string ...$moduleNamespaces
	 * @return \n2n\l10n\DynamicTextCollection
	 */
	public function dtc(string ...$moduleNamespaces) {
		return new DynamicTextCollection($moduleNamespaces, $this->eiuFactory->getN2nContext(true)->getN2nLocale());
	}
	
	/**
	 * @return \rocket\ei\manage\util\model\EiuFactory
	 */
	public function getEiuFactory() {
		return $this->eiuFactory;
	}
}