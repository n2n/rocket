<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\EiFrame;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\ManageException;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\EiEntityObj;
use rocket\spec\ei\manage\LiveEiObject;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\DraftEiObject;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\component\field\EiProp;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\mask\EiMask;
use n2n\reflection\ReflectionUtils;

class EiuFactory {
	const EI_FRAME_TYPES = array(EiFrame::class, EiuFrame::class, N2nContext::class);
	const EI_ENTRY_TYPES = array(EiObject::class, EiMapping::class, EiEntityObj::class, Draft::class, 
			EiEntryGui::class, EiuEntry::class, EiuEntryGui::class);
	const EI_GUI_TYPES = array(EiEntryGui::class, EiuEntryGui::class);
	const EI_TYPES = array(EiFrame::class, N2nContext::class, EiObject::class, EiMapping::class, EiEntityObj::class, 
			Draft::class, EiEntryGui::class, EiEntryGui::class, EiProp::class, EiPropPath::class, EiuFrame::class, 
			EiuEntry::class, EiuEntryGui::class, EiuField::class, Eiu::class);
	const EI_FIELD_TYPES = array(EiProp::class, EiPropPath::class, EiuField::class);
	
	private $eiFrame;
	private $n2nContext;
	private $eiObject;
	private $eiMapping;
	private $eiObjectGui;
	private $eiPropPath;
	
	private $eiuFrame;
	private $eiuEntry;
	private $eiuEntryGui;
	private $eiuField;
	
	public function applyEiArgs(...$eiArgs) {
		$remainingEiArgs = array();
		
		foreach ($eiArgs as $key => $eiArg) {
			if ($eiArg instanceof EiFrame) {
				$this->eiFrame = $eiArg;
				continue;
			}
	
			if ($eiArg instanceof N2nContext) {
				$this->n2nContext = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiProp) {
				$this->eiPropPath = EiPropPath::from($eiArg);
				continue;
			}
				
			if ($eiArg instanceof EiPropPath) {
				$this->eiPropPath = $eiArg;
				continue;
			}

			if ($eiArg instanceof EiEntryGui) {
				$this->eiObjectGui = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiuField) {
				$this->eiuField = $eiArg;
				$eiArg = $eiArg->getEiuEntry(false);
			}
			
			if ($eiArg instanceof EiuEntryGui) {
				$this->eiuEntryGui = $eiArg;
				$this->eiObjectGui = $eiArg->getEiEntryGui();
				$eiArg = $eiArg->getEiuEntry(false);
			}
			
			if ($eiArg instanceof EiuEntry) {
				$this->eiObject = $eiArg->getEiObject();
				$this->eiMapping = $eiArg->getEiMapping(false);
				$eiArg = $eiArg->getEiuFrame(false);
			}

			if ($eiArg instanceof EiuCtrl) {
				$eiArg = $eiArg->frame();
			}
			
			if ($eiArg instanceof EiuFrame) {
				$this->eiuFrame = $eiArg;
				$this->eiFrame = $eiArg->getEiFrame();
				continue;
			}
			
			if ($eiArg instanceof Eiu) {
				$this->eiuField = $this->eiuField ?? $eiArg->field(false);
				$this->eiuEntry = $this->eiuEntry ?? $eiArg->entry(false);
				$this->eiuFrame = $this->eiuFrame ?? $eiArg->frame(false);
				$this->eiuEntryGui = $this->eiuEntryGui ?? $eiArg->entryGui(false);
				continue;
			}
			
			if (null !== ($eiObject = self::determineEiObject($eiArg, $this->eiMapping, $this->eiObjectGui))) {
				$this->eiObject = $eiObject;
				continue;
			}
			
			$remainingEiArgs[$key  + 1] = $eiArg;
		}
		
		if (empty($remainingEiArgs)) return;
		
		$eiSpec = null;
		$eiObjectTypes = self::EI_TYPES;
		if ($this->eiFrame !== null) {
			$eiSpec = $this->eiFrame->getContextEiMask()->getEiEngine()->getEiSpec();
			$eiObjectTypes[] = $eiSpec->getEntityModel()->getClass()->getName();
		}
		
		foreach ($remainingEiArgs as $argNo => $eiArg) {
			if ($eiSpec !== null) { 
				try {
					$this->eiObject = LiveEiObject::create($eiSpec, $eiArg);
					continue;
				} catch (\InvalidArgumentException $e) {
					return null;
				}
			}
			
			ArgUtils::valType($eiArg, $eiObjectTypes, true, 'eiArg#' . $argNo);
		}	
	}
	
// 	public function getEiFrame(bool $required) {
// 		if (!$required || $this->eiFrame !== null) {
// 			return $this->eiObjectGui;
// 		}
	
// 		throw new EiuPerimeterException(
// 				'Could not determine EiuFrame because non of the following types were provided as eiArgs: '
// 						. implode(', ', self::EI_FRAME_TYPES));
// 	}
	
	public function getEiMapping() {
		return $this->eiMapping;
	}
	
	/**
	 * 
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\EiObject|NULL
	 */
	public function getEiObject(bool $required) {
		if (!$required || $this->eiObject !== null) {
			return $this->eiObject;
		}
	
		throw new EiuPerimeterException(
				'Could not determine EiObject because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_ENTRY_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\gui\EiEntryGui
	 */
	public function getEiEntryGui(bool $required) {
		if (!$required || $this->eiObjectGui !== null) {
			return $this->eiObjectGui;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiEntryGui because non of the following types were provided as eiArgs: ' 
						. implode(', ', self::EI_GUI_TYPES));
	}
	
	public function getEiPropPath(bool $required) {
		if (!$required || $this->eiPropPath !== null) {
			return $this->eiPropPath;
		}
	
		throw new EiuPerimeterException(
				'Could not create EiuField because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_FIELD_TYPES));
	}
			
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function getEiuFrame(bool $required) {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		if ($this->eiFrame !== null) {
			return $this->eiuFrame = new EiuFrame($this->eiFrame);
		} 
		
		if ($this->n2nContext !== null) {
			try {
				return new EiuFrame($this->n2nContext->lookup(ManageState::class)->peakEiFrame());
			} catch (ManageException $e) {
				throw new EiuPerimeterException('Can not create EiuFrame in invalid context.', 0, $e);
			}
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuFrame because non of the following types were provided as eiArgs: ' 
						. implode(', ', self::EI_FRAME_TYPES));
	}
	
	public function getEiuEntry(bool $required) {
		if ($this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		$eiuFrame = $this->getEiuFrame(false);
		
		if ($eiuFrame !== null) {
			if ($this->eiMapping !== null) {
				return $this->eiuEntry = $eiuFrame->entry($this->eiMapping, true);
			}
			
			if ($this->eiObject !== null) {
				return $this->eiuEntry = $eiuFrame->entry($this->eiObject, true);
			}
		} else {
			if ($this->eiMapping !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiMapping);
			}
				
			if ($this->eiObject !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiObject);
			}
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuFrame because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_ENTRY_TYPES));
	}
	

	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuEntryGui
	 */
	public function getEiuEntryGui(bool $required) {
		if ($this->eiuEntryGui !== null) {
			return $this->eiuEntryGui;
		}
		
		if ($this->eiObjectGui !== null) {
			return $this->eiuEntryGui = new EiuEntryGui($this->eiObjectGui);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuEntryGui because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_GUI_TYPES));
	}
	
	public function getEiuField(bool $required) {
		if ($this->eiuField !== null) {
			return $this->eiuField;
		}
	
		$eiuEntry = $this->getEiuEntry(false);
		if ($eiuEntry !== null) {
			if ($this->eiPropPath !== null) {
				return $this->eiuField = $eiuEntry->field($this->eiPropPath);
			}
		} else {
			if ($this->eiPropPath !== null) {
				return $this->eiuField = new EiuField($this->eiPropPath);
			}
		}
	
		if (!$required) return null;
	
		throw new EiuPerimeterException(
				'Can not create EiuField because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_FIELD_TYPES));
	}
	
	public static function buildEiuFrameFormEiArg($eiArg, string $argName = null, bool $required = false) {
		if ($eiArg instanceof EiuFrame) {
			return $eiArg;
		}
		
		if ($eiArg === null && !$required) {
			return null;
		}
		
		if ($eiArg instanceof EiFrame) {
			return new EiuFrame($eiArg);
		}
		
		if ($eiArg instanceof N2nContext) {
			try {
				return new EiuFrame($eiArg->lookup(ManageState::class)->preakEiFrame());
			} catch (ManageException $e) {
				throw new EiuPerimeterException('Can not create EiuFrame in invalid context.', 0, $e);
			}
		}
		
		if ($eiArg instanceof EiuCtrl) {
			return $eiArg->frame();
		}
		
		if ($eiArg instanceof EiuEntry) {
			return $eiArg->getEiuFrame($required);
		}
		
		if ($eiArg instanceof Eiu) {
			return $eiArg->frame();
		}
		
		ArgUtils::valType($eiArg, self::EI_FRAME_TYPES, !$required, $argName);
	}
	
	/**
	 * @param unknown $eiArg
	 * @param EiuFrame $eiuFrame
	 * @param string $argName
	 * @param bool $required
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry|NULL
	 */
	public static function buildEiuEntryFromEiArg($eiArg, EiuFrame $eiuFrame = null, string $argName = null, bool $required = false) {
		if ($eiArg instanceof EiuEntry) {
			return $eiArg;
		}
		
		if ($eiArg !== null) {
			return new EiuEntry($eiArg, $eiuFrame);
		}
			
		if (!$required) {
			return null;
		}
		
		ArgUtils::valType($eiArg, self::EI_ENTRY_TYPES);
	}
	
	/**
	 * @param mixed $eiObjectObj
	 * @return \rocket\spec\ei\manage\EiObject|null
	 */
	public static function determineEiObject($eiObjectArg, &$eiMapping, &$eiObjectGui) {
		if ($eiObjectArg instanceof EiObject) {
			return $eiObjectArg;
		} 
			
		if ($eiObjectArg instanceof EiMapping) {
			$eiMapping = $eiObjectArg;
			return $eiObjectArg->getEiObject();
		}
		
		if ($eiObjectArg instanceof EiEntityObj) {
			return new LiveEiObject($eiObjectArg);
		}
		
		if ($eiObjectArg instanceof Draft) {
			return new DraftEiObject($eiObjectArg);
		}
		
		if ($eiObjectArg instanceof EiuEntry) {
			return $eiObjectArg->getEiObject();
		}
		
		if ($eiObjectArg instanceof EiuEntryGui && null !== ($eiuEntry = $eiObjectArg->getEiuEntry(false))) {
			$eiMapping = $eiuEntry->getEiMapping(false);
			$eiObjectGui = $eiObjectArg->getEiEntryGui();
			return $eiuEntry->getEiObject();
		}
		
		return null;
// 		if (!$required) return null;
		
// 		throw new EiuPerimeterException('Can not determine EiObject of passed argument type ' 
// 				. ReflectionUtils::getTypeInfo($eiObjectArg) . '. Following types are allowed: '
// 				. implode(', ', array_merge(self::EI_FRAME_TYPES, self::EI_ENTRY_TYPES)));
	}
	
	/**
	 * 
	 * @param unknown $eiSpecObj
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\EiSpec|NULL
	 */
	public static function determineEiSpec($eiSpecArg, bool $required = false) {
		if (null !== ($eiObject = self::determineEiObject($eiSpecArg))) {
			return $eiObject->getEiEntityObj()->getEiSpec();
		}
		
		if ($eiSpecArg instanceof EiSpec) {
			return $eiSpecArg;
		}
		
		if ($eiSpecArg instanceof EiMask) {
			return $eiSpecArg->getEiEngine()->getEiSpec();
		}
			
		if ($eiSpecArg instanceof EiFrame) {
			return $eiSpecArg->getEiEngine()->getEiSpec();
		}
		
		if ($eiSpecArg instanceof EiuFrame) {
			return $eiSpecArg->getEiSpec();
		}
		
		if ($eiSpecArg instanceof EiuEntry && null !== ($eiuFrame = $eiSpecArg->getEiuFrame(false))) {
			return $eiuFrame->getEiSpec();
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('Can not determine EiSpec of passed argument type ' 
				. ReflectionUtils::getTypeInfo($eiSpecArg) . '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_FRAME_TYPES, EI_ENTRY_TYPES)));
	}
	
	public static function buildEiSpecFromEiArg($eiSpecArg, string $argName = null, bool $required = true) {
		if (null !== ($eiSpec = self::determineEiSpec($eiSpecArg))) {
			return $eiSpec;
		}
		
		throw new EiuPerimeterException('Can not determine EiSpec of passed argument ' . $argName 
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_FRAME_TYPES, EI_ENTRY_TYPES)) . '; '
				. ReflectionUtils::getTypeInfo($eiSpecArg) . ' given.');
	}
	
	public static function buildEiObjectFromEiArg($eiObjectObj, string $argName = null, EiSpec $eiSpec = null, 
			bool $required = true, &$eiMapping = null, &$viewMode = null) {
		if (!$required && $eiObjectObj === null) {
			return null;
		}
		
		if (null !== ($eiObject = self::determineEiObject($eiObjectObj, $eiMapping, $viewMode))) {
			return $eiObject;
		}
		
		$eiObjectTypes = self::EI_ENTRY_TYPES;
		
		if ($eiSpec !== null) {
			$eiObjectTypes[] = $eiSpec->getEntityModel()->getClass()->getName();
			try {
				return LiveEiObject::create($eiSpec, $eiObjectObj);
			} catch (\InvalidArgumentException $e) {
				return null;
			}
		}
		
		ArgUtils::valType($eiObjectObj, $eiObjectTypes, !$required, $argName);
	}
}