<?php
namespace rocket\ei\manage\util\model;

use n2n\context\Lookupable;
use n2n\l10n\DynamicTextCollection;
use rocket\core\model\Rocket;
use n2n\reflection\CastUtils;

class Eiu implements Lookupable {
	private $eiuFactory;
	private $eiuContext;
	private $eiuEngine;
	private $eiuFrame;
	private $eiuEntry;
	private $eiuGui;
	private $eiuEntryGui;
	private $eiuField;
	
	public function __construct(...$eiArgs) {
		$this->eiuFactory = new EiuFactory();
		$this->eiuFactory->applyEiArgs(...$eiArgs);
		$this->eiuEngine = $this->eiuFactory->getEiuEngine(false);
		$this->eiuFrame = $this->eiuFactory->getEiuFrame(false);
		$this->eiuEntry = $this->eiuFactory->getEiuEntry(false);
		$this->eiuGui = $this->eiuFactory->getEiuGui(false);
		$this->eiuEntryGui = $this->eiuFactory->getEiuEntryGui(false);
		$this->eiuField = $this->eiuFactory->getEiuField(false);
	}
	
	/**
	 * @return \rocket\ei\manage\util\model\EiuContext|null
	 */
	public function context(bool $required = true) {
		if ($this->eiuContext !== null) {
			return $this->eiuContext;
		}
		
		$n2nContext = $this->eiuFactory->getN2nContext($required);
		if ($n2nContext === null) return null;
		
		$rocket = $n2nContext->lookup(Rocket::class);
		CastUtils::assertTrue($rocket instanceof Rocket);
		
		return $this->eiuContext = new EiuContext($rocket->getSpec(), $n2nContext);
	}
	
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuEngine
	 */
	public function engine(bool $required = true) {
		if ($this->eiuEngine !== null || !$required) return $this->eiuEngine;
		
		throw new EiuPerimeterException('EiuEngine is unavailable.');
	}
	
	/**
	 * @return \rocket\ei\manage\util\model\EiuFrame
	 */
	public function frame(bool $required = true)  {
		if ($this->eiuFrame !== null || !$required) return $this->eiuFrame;
		
		throw new EiuPerimeterException('EiuFrame is unavailable.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuEntry
	 */
	public function entry(bool $required = true) {
		if ($this->eiuEntry !== null || !$required) return $this->eiuEntry;
	
		throw new EiuPerimeterException('EiuEntry is unavailable.');
	}
	
	/**
	 * 
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuGui
	 */
	public function gui(bool $required = true) {
		if ($this->eiuGui !== null || !$required) return $this->eiuGui;
	
		throw new EiuPerimeterException('EiuGui is unavailable.');
	}
	
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuEntryGui
	 */
	public function entryGui(bool $required = true) {
		if ($this->eiuEntryGui !== null) return $this->eiuEntryGui;
	
		if ($this->eiuGui !== null) {
			return $this->eiuGui->entryGui($required);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('EiuEntryGui is unavailable.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return NULL|\rocket\ei\manage\util\model\EiuField
	 */
	public function field(bool $required = true) {
		if ($this->eiuField !== null || !$required) return $this->eiuField;
		
		throw new EiuPerimeterException('EiuField is unavailable.');
	}
	
	/**
	 * @param string|\ReflectionClass $lookupId
	 * @return mixed
	 */
	public function lookup($lookupId, bool $required = true) {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame->getN2nContext()->lookup($lookupId, $required);
		}
		
		return $this->eiuFactory->getN2nContext(true)->lookup($lookupId, $required);
	}
	
	/**
	 * @param string ...$moduleNamespaces
	 * @return \n2n\l10n\DynamicTextCollection
	 */
	public function dtc(string ...$moduleNamespaces) {
		return new DynamicTextCollection($moduleNamespaces, $this->frame()->getN2nLocale());
	}
}