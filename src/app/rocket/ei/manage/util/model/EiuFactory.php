<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\manage\util\model;

use rocket\ei\manage\EiFrame;
use n2n\core\container\N2nContext;
use rocket\ei\manage\ManageException;
use n2n\reflection\ArgUtils;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\mapping\EiEntry;
use rocket\ei\manage\EiEntityObj;
use rocket\ei\manage\LiveEiObject;
use rocket\ei\manage\draft\Draft;
use rocket\ei\manage\DraftEiObject;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\component\prop\EiProp;
use rocket\ei\EiPropPath;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;
use n2n\reflection\ReflectionUtils;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\EiEntryGuiAssembler;
use rocket\ei\EiEngine;
use rocket\spec\SpecManager;

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
	private $eiEntryGuiAssembler;
	private $eiPropPath;
	private $eiEngine;
	private $specManager;
	
	private $eiuEngine;
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
				$this->eiEngine = $eiArg->getContextEiEngine();
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
			
			if ($eiArg instanceof EiEngine) {
				$this->eiEngine = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof SpecManager) {
				$this->specManager = $eiArg;
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
			
			if ($eiArg instanceof EiEntryGuiAssembler) {
				$this->eiEntryGuiAssembler = $eiArg;
				$this->eiEntryGui = $eiArg->getEiEntryGui();
				$this->eiGui = $this->eiEntryGui->getEiGui();
				$this->assignEiFrameArg($this->eiGui->getEiFrame(), $key, $eiArg);
				$eiArg = $this->eiEntryGui->getEiEntry();
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
				$this->eiuGui = $eiArg;
				$this->eiGui = $this->eiuGui->getEiGui();
				$this->eiuFrame = $this->eiuGui->getEiuFrame();
				$this->assignEiFrameArg($this->eiuFrame->getEiFrame(), $key, $eiArg);
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
				$this->eiEngine = $eiArg->getEiMask()->getEiEngine();
				$this->assignEiFrameArg($eiArg->getEiFrame(), $key, $eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiuEngine) {
				$this->eiuEngine = $eiArg;
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
			$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
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
	
	/**
	 * @return NULL|\rocket\ei\manage\mapping\EiEntry
	 */
	public function getEiEntry() {
		return $this->eiEntry;
	}
	
	/**
	 * 
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\EiObject|NULL
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
	 * @return \rocket\ei\manage\gui\EiGui
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
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	public function getEiEntryGui(bool $required) {
		if (!$required || $this->eiEntryGui !== null) {
			return $this->eiEntryGui;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiEntryGui because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_ENTRY_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\gui\EiEntryGuiAssembler
	 */
	public function getEiEntryGuiAssembler(bool $required) {
		if (!$required || $this->eiEntryGuiAssembler !== null) {
			return $this->eiEntryGuiAssembler;
		}
		
		throw new EiuPerimeterException('Could not determine EiEntryGuiAssembler.');
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
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiEngine
	 */
	public function getEiEngine(bool $required) {
		if (!$required || $this->eiEngine !== null) {
			return $this->eiEngine;
		}
		
		throw new EiuPerimeterException('Could not determine EiEngine.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return SpecManager
	 */
	public function getSpecManager(bool $required) {
		if ($this->specManager !== null) {
			return $this->specManager;
		}
		
		if ($this->n2nContext !== null) {
			return $this->n2nContext->lookup(SpecManager::class);
		}
		
		throw new EiuPerimeterException('Could not determine SpecManager.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return N2nContext
	 */
	public function getN2nContext(bool $required) {
		if (!$required || $this->n2nContext !== null) {
			return $this->n2nContext;
		}
		
		throw new EiuPerimeterException('Could not determine N2nContext.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiuEngine
	 */
	public function getEiuEngine(bool $required) {
		if ($this->eiuEngine !== null) {
			return $this->eiuEngine;
		}
		
		if ($this->eiEngine !== null) {
			return $this->eiuEngine = new EiuEngine($this->eiEngine, $this->n2nContext);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuGui because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_TYPES));
	}
			
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\util\model\EiuFrame
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
				if (!$required) return null;
				
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
	 * @return \rocket\ei\manage\util\model\EiuEntryGui
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
	 * @return \rocket\ei\manage\util\model\EiuGui
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
	 * @param mixed $eiArg
	 * @param EiuFrame $eiuFrame
	 * @param string $argName
	 * @param bool $required
	 * @return \rocket\ei\manage\util\model\EiuEntry|NULL
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
	 * @return \rocket\ei\manage\EiObject|null
	 */
	public static function determineEiObject($eiObjectArg, &$eiEntry = null, &$eiEntryGui = null) {
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
			$eiEntry = $eiObjectArg->getEiEntry(false);
			return $eiObjectArg->getEiObject();
		}
		
		if ($eiObjectArg instanceof EiuEntryGui && null !== ($eiuEntry = $eiObjectArg->getEiuEntry(false))) {
			$eiEntry = $eiuEntry->getEiEntry(false);
			$eiEntryGui = $eiObjectArg->getEiEntryGui();
			return $eiuEntry->getEiObject();
		}
		
		if ($eiObjectArg instanceof Eiu && null !== ($eiuEntry = $eiObjectArg->entry(false))) {
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
	 * @param mixed $eiTypeObj
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\EiType|NULL
	 */
	public static function determineEiType($eiTypeArg, bool $required = false) {
		if (null !== ($eiObject = self::determineEiObject($eiTypeArg))) {
			return $eiObject->getEiEntityObj()->getEiType();
		}
		
		if ($eiTypeArg instanceof EiType) {
			return $eiTypeArg;
		}
		
		if ($eiTypeArg instanceof EiMask) {
			return $eiTypeArg->getEiEngine()->getEiMask()->getEiType();
		}
		
		if ($eiTypeArg instanceof EiFrame) {
			return $eiTypeArg->getEiEngine()->getEiMask()->getEiType();
		}
		
		if ($eiTypeArg instanceof Eiu && $eiuFrame = $eiTypeArg->frame(false)) {
			return $eiuFrame->getEiType();
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
		if ($eiTypeArg === null && !$required) {
			return null;
		}
		
		if (null !== ($eiType = self::determineEiType($eiTypeArg))) {
			return $eiType;
		}
		
		throw new EiuPerimeterException('Can not determine EiType of passed argument ' . $argName 
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_FRAME_TYPES, self::EI_ENTRY_TYPES)) . '; '
				. ReflectionUtils::getTypeInfo($eiTypeArg) . ' given.');
	}
	
	/**
	 * @param mixed $eiEntryArg
	 * @param string $argName
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\mapping\EiEntry
	 */
	public static function buildEiEntryFromEiArg($eiEntryArg, string $argName = null, bool $required = true) {
		if ($eiEntryArg instanceof EiEntry) {
			return $eiEntryArg;
		}
		
		if ($eiEntryArg instanceof EiuEntry) {
			return $eiEntryArg->getEiEntry();
		}
		
		throw new EiuPerimeterException('Can not determine EiEntry of passed argument ' . $argName
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_ENTRY_TYPES)) . '; '
				. ReflectionUtils::getTypeInfo($eiEntryArg) . ' given.');
	}
	
	/**
	 * 
	 * @param mixed $eiEntryGuiArg
	 * @param string $argName
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	public static function buildEiEntryGuiFromEiArg($eiEntryGuiArg, string $argName = null, bool $required = true) {
		if ($eiEntryGuiArg instanceof EiEntryGui) {
			return $eiEntryGuiArg;
		}
		
		if ($eiEntryGuiArg instanceof EiuEntryGui) {
			return $eiEntryGuiArg->getEiEntryGui();
		}
		
		if ($eiEntryGuiArg instanceof EiuGui) {
			$eiEntryGuiArg = $eiEntryGuiArg->getEiGui();
		}
		
		if ($eiEntryGuiArg instanceof EiGui) {
			$eiEntryGuis = $eiEntryGuiArg->getEiEntryGuis();
			if (1 == count($eiEntryGuiArg)) {
				return current($eiEntryGuis);
			}
			
			throw new EiuPerimeterException('Can not determine EiEntryGui of passed EiGui ' . $argName);
		}
		
		throw new EiuPerimeterException('Can not determine EiEntryGui of passed argument ' . $argName
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_ENTRY_GUI_TYPES)) . '; '
				. ReflectionUtils::getTypeInfo($eiEntryGuiArg) . ' given.');
	}
	
	public static function buildEiGuiFromEiArg($eiGuiArg, string $argName = null, bool $required = true) {
		if ($eiGuiArg instanceof EiGui) {
			return $eiGuiArg;
		}
	
		if ($eiGuiArg instanceof EiuGui) {
			return $eiGuiArg->getEiGui();
		}
		
		if ($eiGuiArg instanceof EiEntryGui) {
			return $eiGuiArg->getEiGui();
		}
	
		if ($eiGuiArg instanceof EiuEntryGui) {
			return $eiGuiArg->getEiGui();
		}
		
		if ($eiGuiArg instanceof Eiu && null !== ($eiuGui = $eiGuiArg->gui(false))) {
			return $eiuGui->getEiGui();
		}
	
		throw new EiuPerimeterException('Can not determine EiGui of passed argument ' . $argName
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_GUI_TYPES)) . '; '
				. ReflectionUtils::getTypeInfo($eiGuiArg) . ' given.');
	}
	
	public static function buildEiObjectFromEiArg($eiObjectObj, string $argName = null, EiType $eiType = null, 
			bool $required = true, &$eiEntry = null, &$eiGuiArg = null) {
		if (!$required && $eiObjectObj === null) {
			return null;
		}
		
		$eiEntryGui = null;
		if (null !== ($eiObject = self::determineEiObject($eiObjectObj, $eiEntry, $eiEntryGui))) {
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
