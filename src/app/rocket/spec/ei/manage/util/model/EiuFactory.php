<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\EiFrame;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\ManageException;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\mapping\EiEntry;
use rocket\spec\ei\manage\EiEntityObj;
use rocket\spec\ei\manage\LiveEiObject;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\DraftEiObject;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\component\field\EiProp;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\EiType;
use rocket\spec\ei\mask\EiMask;
use n2n\reflection\ReflectionUtils;
use rocket\spec\ei\manage\gui\EiGui;
use rocket\spec\ei\manage\util\model\EiuGui;

class EiuFactory {
	const EI_FRAME_TYPES = array(EiFrame::class, EiuFrame::class, N2nContext::class);
	const EI_ENTRY_TYPES = array(EiObject::class, EiEntry::class, EiEntityObj::class, Draft::class, 
			EiEntryGui::class, EiuEntry::class, EiuEntryGui::class);
	const EI_GUI_TYPES = array(EiGui::class, EiuGui::class, EiEntryGui::class, EiuEntryGui::class);
	const EI_ENTRY_GUI_TYPES = array(EiEntryGui::class, EiuEntryGui::class);
	const EI_TYPES = array(EiFrame::class, N2nContext::class, EiObject::class, EiEntry::class, EiEntityObj::class, 
			Draft::class, EiGui::class, EiuGui::class, EiEntryGui::class, EiEntryGui::class, EiProp::class, 
			EiPropPath::class, EiuFrame::class, EiuEntry::class, EiuEntryGui::class, EiuField::class, Eiu::class);
	const EI_FIELD_TYPES = array(EiProp::class, EiPropPath::class, EiuField::class);
	
	private $eiFrame;
	private $n2nContext;
	private $eiObject;
	private $eiEntry;
	private $eiGui;
	private $eiEntryGui;
	private $eiPropPath;
	
	private $eiuFrame;
	private $eiuEntry;
	private $eiuGui;
	private $eiuEntryGui;
	private $eiuField;
	
	public function applyEiArgs(...$eiArgs) {
		$remainingEiArgs = array();
		
		foreach ($eiArgs as $key => $eiArg) {
			if ($eiArg instanceof EiFrame) {
				$this->assignEiFrameArg($eiArg, $key, $eiArg);
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
			
			if ($eiArg instanceof EiGui) {
				$this->assignEiFrameArg($eiArg->getEiFrame(), $key, $eiArg);
				$this->eiGui = $eiArg;
				$eiEntryGuis = $eiArg->getEiEntryGuis();
				if ($this->eiEntryGui === null && 1 == count($eiEntryGuis)) {
					$this->eiEntryGui = current($eiEntryGuis);
				}
				continue;
			}

			if ($eiArg instanceof EiEntryGui) {
				$this->eiEntryGui = $eiArg;
				$this->eiGui = $eiArg->getEiGui();
				$this->assignEiFrameArg($this->eiGui->getEiFrame(), $key, $eiArg);
				$eiArg = $eiArg->getEiEntry();
			}
			
			if ($eiArg instanceof EiuField) {
				$this->eiuField = $eiArg;
				$eiArg = $eiArg->getEiuEntry(false);
			}
			
			if ($eiArg instanceof EiuEntryGui) {
				$this->eiuEntryGui = $eiArg;
				$this->eiEntryGui = $eiArg->getEiEntryGui();
				$this->eiuGui = $eiArg->getEiuGui();
				$this->eiGui = $this->eiuGui->getEiGui();
				$this->eiuFrame = $this->eiuGui->getEiuFrame();
				$this->assignEiFrameArg($this->eiuFrame->getEiFrame(), $key, $eiArg);
				
				$eiArg = $eiArg->getEiuEntry();
			}
			
			if ($eiArg instanceof EiuGui) {
				$this->eiuGui = $eiArg->getEiuGui();
				$this->eiGui = $this->eiuGui->getEiGui();
				$this->eiuFrame = $this->eiuGui->getEiuFrame();
				$this->assignEiFrameArg($this->eiuFrame->getEiFrame(), $key, $eiArg);
				if ($this->eiuEntryGui === null && $this->eiuGui->isSingle()) {
					$this->eiuEntryGui = $this->eiuGui->entryGui();
				}
				continue;
			}
			
			if ($eiArg instanceof EiuEntry) {
				$this->eiObject = $eiArg->getEiObject();
				$this->eiEntry = $eiArg->getEiEntry(false);
				$eiArg = $eiArg->getEiuFrame(false);
			}

			if ($eiArg instanceof EiuCtrl) {
				$eiArg = $eiArg->frame();
			}
			
			if ($eiArg instanceof EiuFrame) {
				$this->eiuFrame = $eiArg;
				$this->assignEiFrameArg($eiArg->getEiFrame(), $key, $eiArg);
				continue;
			}
			
			if ($eiArg instanceof Eiu) {
				$this->eiuField = $this->eiuField ?? $eiArg->field(false);
				$this->eiuEntry = $this->eiuEntry ?? $eiArg->entry(false);
				$this->eiuFrame = $this->eiuFrame ?? $eiArg->frame(false);
				$this->eiuEntryGui = $this->eiuEntryGui ?? $eiArg->entryGui(false);
				continue;
			}
			
			if (null !== ($eiObject = self::determineEiObject($eiArg, $this->eiEntry, $this->eiEntryGui))) {
				$this->eiObject = $eiObject;
				continue;
			}
			
			$remainingEiArgs[$key  + 1] = $eiArg;
		}
		
		if (empty($remainingEiArgs)) return;
		
		$eiType = null;
		$eiObjectTypes = self::EI_TYPES;
		if ($this->eiFrame !== null) {
			$eiType = $this->eiFrame->getContextEiMask()->getEiEngine()->getEiType();
			$eiObjectTypes[] = $eiType->getEntityModel()->getClass()->getName();
		}
		
		foreach ($remainingEiArgs as $argNo => $eiArg) {
			if ($eiType !== null) { 
				try {
					$this->eiObject = LiveEiObject::create($eiType, $eiArg);
					continue;
				} catch (\InvalidArgumentException $e) {
					return null;
				}
			}
			
			ArgUtils::valType($eiArg, $eiObjectTypes, true, 'eiArg#' . $argNo);
		}	
	}
	
	private function assignEiFrameArg($eiFrame, $eiArgKey, $eiArg) {
		if ($this->eiFrame === null || $this->eiFrame === $eiFrame) {
			$this->eiFrame = $eiFrame;
			return;
		}
			
		throw new \InvalidArgumentException('eiArg#' . $eiArgKey . ' provides EiFrame contradict EiFrame provided by previous eiArg.');
	}
	
// 	public function getEiFrame(bool $required) {
// 		if (!$required || $this->eiFrame !== null) {
// 			return $this->eiEntryGui;
// 		}
	
// 		throw new EiuPerimeterException(
// 				'Could not determine EiuFrame because non of the following types were provided as eiArgs: '
// 						. implode(', ', self::EI_FRAME_TYPES));
// 	}
	
	public function getEiEntry() {
		return $this->eiEntry;
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
	 * @return \rocket\spec\ei\manage\gui\EiGui
	 */
	public function getEiGui(bool $required) {
		if (!$required || $this->eiGui !== null) {
			return $this->eiGui;
		}
	
		throw new EiuPerimeterException(
				'Could not determine EiGui because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_GUI_TYPES));
	}
	
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\gui\EiEntryGui
	 */
	public function getEiEntryGui(bool $required) {
		if (!$required || $this->eiEntryGui !== null) {
			return $this->eiEntryGui;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiEntryGui because non of the following types were provided as eiArgs: ' 
						. implode(', ', self::EI_ENTRY_GUI_TYPES));
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
			if ($this->eiEntry !== null) {
				return $this->eiuEntry = $eiuFrame->entry($this->eiEntry, true);
			}
			
			if ($this->eiObject !== null) {
				return $this->eiuEntry = $eiuFrame->entry($this->eiObject, true);
			}
		} else {
			if ($this->eiEntry !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiEntry);
			}
				
			if ($this->eiObject !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiObject);
			}
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuEntry because non of the following types were provided as eiArgs: '
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
		
		if ($this->eiEntryGui !== null) {
			return $this->eiuEntryGui = new EiuEntryGui($this->eiEntryGui, $this->getEiuGui(true));
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuEntryGui because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_ENTRY_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuGui
	 */
	public function getEiuGui(bool $required) {
		if ($this->eiuGui !== null) {
			return $this->eiuGui;
		}
	
		if ($this->eiGui !== null) {
			return $this->eiuGui = new EiuGui($this->eiGui, $this->getEiuFrame(true));
		}
	
		if (!$required) return null;
	
		throw new EiuPerimeterException(
				'Can not create EiuGui because non of the following types were provided as eiArgs: '
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
	public static function determineEiObject($eiObjectArg, &$eiEntry, &$eiEntryGui) {
		if ($eiObjectArg instanceof EiObject) {
			return $eiObjectArg;
		} 
			
		if ($eiObjectArg instanceof EiEntry) {
			$eiEntry = $eiObjectArg;
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
			$eiEntry = $eiuEntry->getEiEntry(false);
			$eiEntryGui = $eiObjectArg->getEiEntryGui();
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
	 * @param unknown $eiTypeObj
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\EiType|NULL
	 */
	public static function determineEiType($eiTypeArg, bool $required = false) {
		if (null !== ($eiObject = self::determineEiObject($eiTypeArg))) {
			return $eiObject->getEiEntityObj()->getEiType();
		}
		
		if ($eiTypeArg instanceof EiType) {
			return $eiTypeArg;
		}
		
		if ($eiTypeArg instanceof EiMask) {
			return $eiTypeArg->getEiEngine()->getEiType();
		}
			
		if ($eiTypeArg instanceof EiFrame) {
			return $eiTypeArg->getEiEngine()->getEiType();
		}
		
		if ($eiTypeArg instanceof EiuFrame) {
			return $eiTypeArg->getEiType();
		}
		
		if ($eiTypeArg instanceof EiuEntry && null !== ($eiuFrame = $eiTypeArg->getEiuFrame(false))) {
			return $eiuFrame->getEiType();
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('Can not determine EiType of passed argument type ' 
				. ReflectionUtils::getTypeInfo($eiTypeArg) . '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_FRAME_TYPES, EI_ENTRY_TYPES)));
	}
	
	public static function buildEiTypeFromEiArg($eiTypeArg, string $argName = null, bool $required = true) {
		if (null !== ($eiType = self::determineEiType($eiTypeArg))) {
			return $eiType;
		}
		
		throw new EiuPerimeterException('Can not determine EiType of passed argument ' . $argName 
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_FRAME_TYPES, EI_ENTRY_TYPES)) . '; '
				. ReflectionUtils::getTypeInfo($eiTypeArg) . ' given.');
	}
	
	public static function buildEiObjectFromEiArg($eiObjectObj, string $argName = null, EiType $eiType = null, 
			bool $required = true, &$eiEntry = null, &$viewMode = null) {
		if (!$required && $eiObjectObj === null) {
			return null;
		}
		
		if (null !== ($eiObject = self::determineEiObject($eiObjectObj, $eiEntry, $viewMode))) {
			return $eiObject;
		}
		
		$eiObjectTypes = self::EI_ENTRY_TYPES;
		
		if ($eiType !== null) {
			$eiObjectTypes[] = $eiType->getEntityModel()->getClass()->getName();
			try {
				return LiveEiObject::create($eiType, $eiObjectObj);
			} catch (\InvalidArgumentException $e) {
				return null;
			}
		}
		
		ArgUtils::valType($eiObjectObj, $eiObjectTypes, !$required, $argName);
	}
}