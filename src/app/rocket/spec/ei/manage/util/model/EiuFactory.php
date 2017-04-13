<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\EiFrame;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\ManageException;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiEntry;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\LiveEntry;
use rocket\spec\ei\manage\LiveEiEntry;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\DraftEiEntry;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\component\field\EiField;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\mask\EiMask;
use n2n\reflection\ReflectionUtils;

class EiuFactory {
	const EI_FRAME_TYPES = array(EiFrame::class, EiuFrame::class, N2nContext::class);
	const EI_ENTRY_TYPES = array(EiEntry::class, EiMapping::class, LiveEntry::class, Draft::class, 
			EiEntryGui::class, EiuEntry::class, EiuEntryGui::class);
	const EI_GUI_TYPES = array(EiEntryGui::class, EiuEntryGui::class);
	const EI_TYPES = array(EiFrame::class, N2nContext::class, EiEntry::class, EiMapping::class, LiveEntry::class, 
			Draft::class, EiEntryGui::class, EiEntryGui::class, EiField::class, EiFieldPath::class, EiuFrame::class, 
			EiuEntry::class, EiuEntryGui::class, EiuField::class, Eiu::class);
	const EI_FIELD_TYPES = array(EiField::class, EiFieldPath::class, EiuField::class);
	
	private $eiFrame;
	private $n2nContext;
	private $eiEntry;
	private $eiMapping;
	private $eiEntryGui;
	private $eiFieldPath;
	
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
			
			if ($eiArg instanceof EiField) {
				$this->eiFieldPath = EiFieldPath::from($eiArg);
				continue;
			}
				
			if ($eiArg instanceof EiFieldPath) {
				$this->eiFieldPath = $eiArg;
				continue;
			}

			if ($eiArg instanceof EiEntryGui) {
				$this->eiEntryGui = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiuField) {
				$this->eiuField = $eiArg;
				$eiArg = $eiArg->getEiuEntry(false);
			}
			
			if ($eiArg instanceof EiuEntryGui) {
				$this->eiuEntryGui = $eiArg;
				$this->eiEntryGui = $eiArg->getEiEntryGui();
				$eiArg = $eiArg->getEiuEntry(false);
			}
			
			if ($eiArg instanceof EiuEntry) {
				$this->eiEntry = $eiArg->getEiEntry();
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
			
			if (null !== ($eiEntry = self::determineEiEntry($eiArg, $this->eiMapping, $this->eiEntryGui))) {
				$this->eiEntry = $eiEntry;
				continue;
			}
			
			$remainingEiArgs[$key  + 1] = $eiArg;
		}
		
		if (empty($remainingEiArgs)) return;
		
		$eiSpec = null;
		$eiEntryTypes = self::EI_TYPES;
		if ($this->eiFrame !== null) {
			$eiSpec = $this->eiFrame->getContextEiMask()->getEiEngine()->getEiSpec();
			$eiEntryTypes[] = $eiSpec->getEntityModel()->getClass()->getName();
		}
		
		foreach ($remainingEiArgs as $argNo => $eiArg) {
			if ($eiSpec !== null) { 
				try {
					$this->eiEntry = LiveEiEntry::create($eiSpec, $eiArg);
					continue;
				} catch (\InvalidArgumentException $e) {
					return null;
				}
			}
			
			ArgUtils::valType($eiArg, $eiEntryTypes, true, 'eiArg#' . $argNo);
		}	
	}
	
// 	public function getEiFrame(bool $required) {
// 		if (!$required || $this->eiFrame !== null) {
// 			return $this->eiEntryGui;
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
	 * @return \rocket\spec\ei\manage\EiEntry|NULL
	 */
	public function getEiEntry(bool $required) {
		if (!$required || $this->eiEntry !== null) {
			return $this->eiEntry;
		}
	
		throw new EiuPerimeterException(
				'Could not determine EiEntry because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_ENTRY_TYPES));
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
						. implode(', ', self::EI_GUI_TYPES));
	}
	
	public function getEiFieldPath(bool $required) {
		if (!$required || $this->eiFieldPath !== null) {
			return $this->eiFieldPath;
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
			
			if ($this->eiEntry !== null) {
				return $this->eiuEntry = $eiuFrame->entry($this->eiEntry, true);
			}
		} else {
			if ($this->eiMapping !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiMapping);
			}
				
			if ($this->eiEntry !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiEntry);
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
		
		if ($this->eiEntryGui !== null) {
			return $this->eiuEntryGui = new EiuEntryGui($this->eiEntryGui);
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
			if ($this->eiFieldPath !== null) {
				return $this->eiuField = $eiuEntry->field($this->eiFieldPath);
			}
		} else {
			if ($this->eiFieldPath !== null) {
				return $this->eiuField = new EiuField($this->eiFieldPath);
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
	 * @param mixed $eiEntryObj
	 * @return \rocket\spec\ei\manage\EiEntry|null
	 */
	public static function determineEiEntry($eiEntryArg, &$eiMapping, &$eiEntryGui) {
		if ($eiEntryArg instanceof EiEntry) {
			return $eiEntryArg;
		} 
			
		if ($eiEntryArg instanceof EiMapping) {
			$eiMapping = $eiEntryArg;
			return $eiEntryArg->getEiEntry();
		}
		
		if ($eiEntryArg instanceof LiveEntry) {
			return new LiveEiEntry($eiEntryArg);
		}
		
		if ($eiEntryArg instanceof Draft) {
			return new DraftEiEntry($eiEntryArg);
		}
		
		if ($eiEntryArg instanceof EiuEntry) {
			return $eiEntryArg->getEiEntry();
		}
		
		if ($eiEntryArg instanceof EiuEntryGui && null !== ($eiuEntry = $eiEntryArg->getEiuEntry(false))) {
			$eiMapping = $eiuEntry->getEiMapping(false);
			$eiEntryGui = $eiEntryArg->getEiEntryGui();
			return $eiuEntry->getEiEntry();
		}
		
		return null;
// 		if (!$required) return null;
		
// 		throw new EiuPerimeterException('Can not determine EiEntry of passed argument type ' 
// 				. ReflectionUtils::getTypeInfo($eiEntryArg) . '. Following types are allowed: '
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
		if (null !== ($eiEntry = self::determineEiEntry($eiSpecArg, null, null))) {
			return $eiEntry->getLiveEntry()->getEiSpec();
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
	
	public static function buildEiEntryFromEiArg($eiEntryObj, string $argName = null, EiSpec $eiSpec = null, 
			bool $required = true, &$eiMapping = null, &$viewMode = null) {
		if (!$required && $eiEntryObj === null) {
			return null;
		}
		
		if (null !== ($eiEntry = self::determineEiEntry($eiEntryObj, $eiMapping, $viewMode))) {
			return $eiEntry;
		}
		
		$eiEntryTypes = self::EI_ENTRY_TYPES;
		
		if ($eiSpec !== null) {
			$eiEntryTypes[] = $eiSpec->getEntityModel()->getClass()->getName();
			try {
				return LiveEiEntry::create($eiSpec, $eiEntryObj);
			} catch (\InvalidArgumentException $e) {
				return null;
			}
		}
		
		ArgUtils::valType($eiEntryObj, $eiEntryTypes, !$required, $argName);
	}
}