<?php
namespace rocket\spec\ei\manage\util\model;

use n2n\context\Lookupable;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\l10n\DynamicTextCollection;

class Eiu implements Lookupable {
	private $eiuFactory;
	private $eiuFrame;
	private $eiuEntry;
	private $eiuGui;
	private $eiuEntryGui;
	private $eiuField;
	
	public function __construct(...$eiArgs) {
		$this->eiuFactory = new EiuFactory();
		$this->eiuFactory->applyEiArgs(...$eiArgs);
		$this->eiuFrame = $this->eiuFactory->getEiuFrame(false);
		$this->eiuEntry = $this->eiuFactory->getEiuEntry(false);
		$this->eiuGui = $this->eiuFactory->getEiuGui(false);
		$this->eiuEntryGui = $this->eiuFactory->getEiuEntryGui(false);
		$this->eiuField = $this->eiuFactory->getEiuField(false);
	}

	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function frame(bool $required = true)  {
		if ($this->eiuFrame !== null || !$required) return $this->eiuFrame;
		
		throw new EiuPerimeterException('EiuFrame is unavailable.');
	}
	
	/**
	 * @param unknown $eiObjectObj
	 * @param bool $assignToEiu
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function entry(bool $required = true) {
		if ($this->eiuEntry !== null || !$required) return $this->eiuEntry;
	
		throw new EiuPerimeterException('EiuEntry is unavailable.');
	}
	
	/**
	 * 
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuGui
	 */
	public function gui(bool $required = true) {
		if ($this->eiuGui !== null || !$required) return $this->eiuGui;
	
		throw new EiuPerimeterException('EiuGui is unavailable.');
	}
	
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuEntryGui
	 */
	public function entryGui(bool $required = true) {
		if ($this->eiuEntryGui !== null) return $this->eiuEntryGui;
	
		if ($this->eiuGui !== null) {
			return $this->eiuGui->entryGui($required);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('EiuEntryGui is unavailable.');
	}
	
	public function field(bool $required = true) {
		if ($this->eiuField !== null || !$required) return $this->eiuField;
		
		throw new EiuPerimeterException('EiuField is unavailable.');
	}
	
	/**
	 * @param string|\ReflectionClass $lookupId
	 * @return mixed
	 */
	public function lookup($lookupId, bool $required = true) {
		return $this->frame()->getN2nContext()->lookup($lookupId, $required);
	}
	
	public function dtc(string ...$moduleNamespaces) {
		return new DynamicTextCollection($moduleNamespaces, $this->frame()->getN2nLocale());
	}
}