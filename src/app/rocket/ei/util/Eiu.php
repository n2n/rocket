<?php
namespace rocket\ei\util;

use n2n\context\Lookupable;
use n2n\l10n\DynamicTextCollection;
use rocket\core\model\Rocket;
use n2n\core\container\N2nContext;
use rocket\ei\util\spec\EiuContext;
use rocket\ei\util\factory\EiuFactory;
use n2n\web\ui\ViewFactory;
use n2n\util\type\CastUtils;
use n2n\util\magic\MagicObjectUnavailableException;
use rocket\ei\util\spec\EiuProp;
use rocket\ei\util\spec\EiuCmd;

class Eiu implements Lookupable {
	private $eiuAnalyst;
	private $eiuContext;
	private $eiuEngine;
	private $eiuMask;
	private $eiuProp;
	private $eiuCmd;
	private $eiuFrame;
	private $eiuObject;
	private $eiuEntry;
	private $eiuField;
	private $eiuFieldMap;
	private $eiuGui;
	private $eiuGuiModel;
	private $eiuGuiFrame;
	private $eiuEntryGui;
	private $eiuGuiField;
	private $eiuFactory;
	
	public function __construct(...$eiArgs) {
		$this->eiuAnalyst = new EiuAnalyst();
		$this->eiuAnalyst->applyEiArgs(...$eiArgs);
	}
	
// 	static function fromAnalyst(EiuAnalyst $eiuAnalyst) {
		
// 	}
	
	private function _init(N2nContext $n2nContext) {
		$this->eiuAnalyst->applyEiArgs($n2nContext);
	}
	
	/**
	 * @return EiuContext|null
	 */
	public function context(bool $required = true): ?EiuContext {
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
	 * @return EiuProp
	 */
	public function prop(bool $required = true) {
		if ($this->eiuProp !== null) {
			return $this->eiuProp;
		}
		
		return $this->eiuProp = $this->eiuAnalyst->getEiuProp($required);
	}

	/**
	 * @param bool $required
	 * @return EiuCmd
	 */
	public function cmd(bool $required = true) {
		if ($this->eiuCmd !== null) {
			return $this->eiuCmd;
		}

		return $this->eiuCmd = $this->eiuAnalyst->getEiuCmd($required);
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
	 *
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	public function guiModel(bool $required = true) {
		if ($this->eiuGuiModel !== null) {
			return $this->eiuGuiModel;
		}
		
		return $this->eiuGuiModel = $this->eiuAnalyst->getEiuGuiModel($required);
	}
	
	/**
	 * 
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuGuiFrame
	 */
	public function guiFrame(bool $required = true) {
		if ($this->eiuGuiFrame !== null) {
			return $this->eiuGuiFrame;
		}
		
		return $this->eiuGuiFrame = $this->eiuAnalyst->getEiuGuiFrame($required);
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
	 * @param bool $required
	 * @return \rocket\ei\util\gui\EiuGuiField|null
	 */
	public function guiField(bool $required = true) {
		if ($this->eiuGuiField !== null) {
			return $this->eiuGuiField;
		}
		
		return $this->eiuGuiField = $this->eiuAnalyst->getEiuGuiField($required);
	}
	
	/**
	 * @return EiuFactory
	 */
	public function factory() {
		if ($this->eiuFactory === null) {
			$this->eiuFactory = new EiuFactory($this, $this->eiuAnalyst);
		}
		
		return $this->eiuFactory;
	}

	/**
	 * @return EiuFactory
	 */
	function f(): EiuFactory {
		return $this->factory();
	}
	
	/**
	 * @param string|\ReflectionClass $lookupId
	 * @return mixed
	 * @throws MagicObjectUnavailableException
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
	 * @param string $viewName
	 * @param array $args
	 * @return \n2n\web\ui\view\View
	 */
	function createView(string $viewName, array $args = []) {
		$viewFactory = $this->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		return $viewFactory->create($viewName, $args);
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